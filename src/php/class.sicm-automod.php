<?php

class Sicm_Automod {
    const API_HOST = 'api.getspectrum.io';
    const API_VERSION = '1.0';
    const API_PORT = 443;

    private static $initiated = false;

    private static $api;

    private static $last_comment;

    public static function init() {
        if (!self::$initiated) {
            self::init_hooks();
        }
    }

    public static function init_hooks() {
        self::$initiated = true;
        self::$api = new Sicm_Spectrum_Api(get_option(SICM_AUTOMOD__API_KEY_OPTION_NAME, null));

        add_filter('preprocess_comment', array('Sicm_Automod', 'check_comment'), 1);
        add_action('wp_insert_comment', array('Sicm_Automod', 'check_comment_post_insert'), 10, 2);
        add_action('transition_comment_status', array('Sicm_Automod', 'transition_comment_status'), 10, 3);

    }

    public static function check_comment($comment_data) {
        // automatically return early for comments from registered users
        if ($comment_data['user_id']) {
            return $comment_data;
        }

        self::$last_comment = null;

        // handle akismet on/off
        if (isset($comment_data['comment_as_submitted']) &&
            isset($comment_data['comment_as_submitted']['comment_content'])) {
            $text = $comment_data['comment_as_submitted']['comment_content'];
        } else {
            $text = $comment_data['comment_content'];
        }

        try {
            $result = self::$api->classify_text($text);
            $comment_data['automod_result'] = $result['body']['result']['toxic'];
        } catch (Sicm_NetworkException $e) {
            $comment_data['automod_result'] = 'error';
        }

        self::$last_comment = $comment_data;
        return $comment_data;
    }

    public static function check_comment_post_insert($id, $comment) {
        if (!self::matches_last_comment($comment)) {
            return;
        }

        $last_comment = self::$last_comment;
        $result = $last_comment['automod_result'];

        if ($result === true || $result == 'error') {
            if ($result === true) {
                self::update_comment_history($comment->comment_ID, 'check-hold');
            } else {
                self::update_comment_history($comment->comment_ID, 'check-error');
            }

            wp_set_comment_status($id, 'hold');
        } else {
            self::update_comment_history($comment->comment_ID, 'check-allow');
        }
    }

    public static function transition_comment_status($new_status, $old_status, $comment) {

        $new_status =  self::to_toxicity($new_status);
        $old_status =  self::to_toxicity($old_status);

        if ($new_status == $old_status) {
            return;
        }

        if (!current_user_can('edit_post', $comment->comment_post_ID) && !current_user_can('moderate_comments')) {
            return;
        }

        if (defined('WP_IMPORTING') && WP_IMPORTING == true) {
            return;
        }

        try {
            $result = self::$api->record_user_classification($comment->comment_content, $new_status);
        } catch (Sicm_NetworkException $e) {
            $result = 'error';
        }

        return $result;
    }

    public static function to_toxicity($status) {
        if ($status == 'approved') {
            $status = false;
        } else {
            $status = true;
        }

        return $status;
    }

    public static function cleanup() {
        delete_option(SICM_AUTOMOD__API_KEY_OPTION_NAME);
    }

    public static function get_comment_history($comment_id) {
        // failsafe for old WP versions
        if (!function_exists('add_comment_meta')) {
            return false;
        }

        $history = get_comment_meta($comment_id, 'automod_history', false);
        usort($history, array('Sicm_Automod', '_cmp_time'));
        return $history;
    }

    public static function view($name, $args = array()) {
        foreach ($args AS $key => $val) {
            $$key = $val;
        }

        load_plugin_textdomain('automod');

        $file = SICM_AUTOMOD__PLUGIN_DIR . 'views/' . $name . '.php';

        include($file);
    }

    public static function update_comment_history($comment_id, $event = null, $meta = null) {
        global $current_user;

        // failsafe for old WP versions
        if (!function_exists('add_comment_meta')) {
            return false;
        }

        $event = array(
            'time' => self::get_microtime(),
            'event' => $event,
        );

        if (is_object($current_user) && isset($current_user->user_login)) {
            $event['user'] = $current_user->user_login;
        }

        if (!empty($meta)) {
            $event['meta'] = $meta;
        }

        // $unique = false so as to allow multiple values per comment
        add_comment_meta($comment_id, 'automod_history', $event, false);
    }

    private static function comments_match($comment1, $comment2) {
        $comment1 = (array)$comment1;
        $comment2 = (array)$comment2;

        // Set default values for these strings that we check in order to simplify
        // the checks and avoid PHP warnings.
        if (!isset($comment1['comment_author'])) {
            $comment1['comment_author'] = '';
        }

        if (!isset($comment2['comment_author'])) {
            $comment2['comment_author'] = '';
        }

        if (!isset($comment1['comment_author_email'])) {
            $comment1['comment_author_email'] = '';
        }

        if (!isset($comment2['comment_author_email'])) {
            $comment2['comment_author_email'] = '';
        }

        $comments_match = (
            isset($comment1['comment_post_ID'], $comment2['comment_post_ID'])
            && intval($comment1['comment_post_ID']) == intval($comment2['comment_post_ID'])
            && (
                // The comment author length max is 255 characters, limited by the TINYTEXT column type.
                // If the comment author includes multibyte characters right around the 255-byte mark, they
                // may be stripped when the author is saved in the DB, so a 300+ char author may turn into
                // a 253-char author when it's saved, not 255 exactly.  The longest possible character is
                // theoretically 6 bytes, so we'll only look at the first 248 bytes to be safe.
                substr($comment1['comment_author'], 0, 248) == substr($comment2['comment_author'], 0, 248)
                || substr(stripslashes($comment1['comment_author']), 0, 248) == substr($comment2['comment_author'], 0, 248)
                || substr($comment1['comment_author'], 0, 248) == substr(stripslashes($comment2['comment_author']), 0, 248)
                // Certain long comment author names will be truncated to nothing, depending on their encoding.
                || (!$comment1['comment_author'] && strlen($comment2['comment_author']) > 248)
                || (!$comment2['comment_author'] && strlen($comment1['comment_author']) > 248)
            )
            && (
                // The email max length is 100 characters, limited by the VARCHAR(100) column type.
                // Same argument as above for only looking at the first 93 characters.
                substr($comment1['comment_author_email'], 0, 93) == substr($comment2['comment_author_email'], 0, 93)
                || substr(stripslashes($comment1['comment_author_email']), 0, 93) == substr($comment2['comment_author_email'], 0, 93)
                || substr($comment1['comment_author_email'], 0, 93) == substr(stripslashes($comment2['comment_author_email']), 0, 93)
                // Very long emails can be truncated and then stripped if the [0:100] substring isn't a valid address.
                || (!$comment1['comment_author_email'] && strlen($comment2['comment_author_email']) > 100)
                || (!$comment2['comment_author_email'] && strlen($comment1['comment_author_email']) > 100)
            )
        );

        return $comments_match;
    }

    // Does the supplied comment match the details of the one most recently stored in self::$last_comment?
    public static function matches_last_comment($comment) {
        return self::comments_match(self::$last_comment, $comment);
    }

    public static function get_microtime() {
        $mtime = explode(' ', microtime());
        return $mtime[1] + $mtime[0];
    }

    public static function _cmp_time($a, $b) {
        return $a['time'] > $b['time'] ? -1 : 1;
    }
}
