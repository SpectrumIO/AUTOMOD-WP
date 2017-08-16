<?php

class Sicm_Automod_Admin {
    private static $initiated = false;

    private static $notices = array();

    private static $api;

    private static $notices_index = array(
        'login-failed' => array(
            'message' => 'Invalid username or password.',
            'type' => 'error'
        ),
        'create-integration-failed' => array(
            'message' => 'Failed to create the link between your blog and our server. Please try again.',
            'type' => 'error'
        ),
        'create-integration-succeeded' => array(
            'message' => 'Successfully linked your blog to our servers!',
            'type' => 'success'
        )
    );

    public static function init() {
        if (!self::$initiated) {
            self::init_hooks();
        }

        self::$api = new Sicm_Spectrum_Api(get_option(SICM_AUTOMOD__API_KEY_OPTION_NAME));

        if (isset($_POST['action']) && $_POST['action'] == 'create-integration') {
            self::create_integration();
        } elseif (isset($_POST['action']) && $_POST['action'] == 'disconnect') {
            self::disconnect_integration();
        }
    }

    public static function init_hooks() {
        self::$initiated = true;

        load_plugin_textdomain('automod');
        add_action('admin_init', array('Sicm_Automod_Admin', 'admin_init'));
        add_action('admin_menu', array('Sicm_Automod_Admin', 'admin_menu'));
        add_action('admin_enqueue_scripts', array('Sicm_Automod_Admin', 'load_resources'));
    }

    public static function admin_init() {
        add_meta_box('automod-status', __('AutoMod History', 'automod'), array('Sicm_Automod_Admin', 'comment_status_meta_box'),
            'comment', 'normal');
    }

    public static function admin_menu() {
        add_options_page(__('Spectrum', 'automod'), __('Spectrum', 'automod'),
            'manage_options', 'automod-config', array('Sicm_Automod_Admin', 'display_page'));
    }

    public static function load_resources() {
        wp_register_style('automod.css', plugin_dir_url(__FILE__) . '_inc/automod.css', array(), SICM_AUTOMOD_VERSION);
        wp_register_style('chartist.css', plugin_dir_url(__FILE__) . '_inc/chartist.css', array(), SICM_AUTOMOD_VERSION);
        wp_enqueue_style('automod.css');
        wp_enqueue_style('chartist.css');

        wp_register_script('chartist.js', plugin_dir_url(__FILE__) . '_inc/vendor/chartist.min.js', array(), SICM_AUTOMOD_VERSION);
        wp_register_script('home.js', plugin_dir_url(__FILE__) . '_inc/home.js', array('jquery'), SICM_AUTOMOD_VERSION);
        wp_enqueue_script('chartist.js');
        wp_enqueue_script('home.js');
    }

    public static function display_page() {
        if (!get_option(SICM_AUTOMOD__API_KEY_OPTION_NAME)) {
            Sicm_Automod::view('onboarding', array());
            return;
        }

        try {
            $analytics_data = self::$api->fetch_analytics();
        } catch (Sicm_NetworkException $e) {
            $analytics_data = null;
        }

        Sicm_Automod::view('home', array(
            'api_key' => get_option(SICM_AUTOMOD__API_KEY_OPTION_NAME),
            'analytics_data' => $analytics_data['body']['result']
        ));
    }

    public static function get_page_url($page = 'config') {
        $args = array('page' => 'automod-config');

        return add_query_arg($args, admin_url('options-general.php'));
    }

    public static function render_notices() {
        if (empty(self::$notices)) {
            return;
        }

        print '<div class="automod--notices">';
        foreach (self::$notices as $notice) {
            Sicm_Automod::view('notice', self::$notices_index[$notice]);
        }
        print '</div>';
    }

    public static function comment_status_meta_box($comment) {
        $history = Sicm_Automod::get_comment_history($comment->comment_ID);

        if ($history) {
            print '<div class="akismet-history" style="margin: 13px;">';

            foreach ($history as $row) {
                $time = date('D d M Y @ h:i:m a', $row['time']) . ' GMT';

                $message = '';

                switch ($row['event']) {
                    case 'check-hold':
                        $message = __('AutoMod held this comment for moderation.', 'automod');
                        break;
                    case 'check-allow':
                        $message = __('AutoMod allowed this comment.', 'automod');
                        break;
                    case 'check-error':
                        $message = __('AutoMod encountered an error and held this comment for moderation based on your settings.', 'automod');
                        break;
                }

                print '<div style="margin-bottom: 13px;">';
                print '<span style="color: #999;" alt="' . $time . '" title="' . $time . '">' . sprintf(esc_html__('%s ago', 'automod'), human_time_diff($row['time'])) . '</span>';
                print ' - ';
                print esc_html($message);
                print '</div>';
            }

            print '</div>';
        }
    }

    private static function create_integration() {
        $login_response = self::$api->login($_POST['email'], $_POST['password']);

        if (!$login_response) {
            self::$notices[] = 'login-failed';
            return;
        }

        try {
            $create_integration_response = self::$api->create_integration();
        } catch (Sicm_NetworkException $e) {
            self::$notices[] = 'create-integration-failed';
            return;
        }

        self::$notices[] = 'create-integration-succeeded';

        update_option(SICM_AUTOMOD__API_KEY_OPTION_NAME, $create_integration_response['body']['result']['apiKey']);
    }

    private static function disconnect_integration() {
        Sicm_Automod::cleanup();
    }
}