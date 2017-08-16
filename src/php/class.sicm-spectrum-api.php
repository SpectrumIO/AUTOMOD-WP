<?php


class Sicm_Spectrum_Api {
    private $cookies = array();

    private $api_key;

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function call_rpc_method($service, $method, $params = array(), $throw_error = true) {
        $options = array(
            'headers' => array(
                'content-type' => 'application/json',
            ),
            'body' => json_encode(array(
                'jsonrpc' => '2.0',
                'method' => $method,
                'params' => $params,
                'id' => 1
            ))
        );

        if ($this->api_key != null) {
            $options['headers']['authorization'] = 'apikey ' . $this->api_key;
        } else {
            $options['cookies'] = $this->cookies;
        }

        $response = wp_safe_remote_post(SICM_AUTOMOD__API_BASE_URL . $service, $options);

        if (is_wp_error($response)){
            error_log('Received error from Spectrum backend!');
            throw new Sicm_NetworkException(array(
                'body' => array(
                    'error' => array(
                        'code' => -32601,
                        'message' => 'Backend error.'
                    )
                )
            ));
        }

        try {
            $response['body'] = json_decode($response['body'], true);
        } catch (Exception $e) {
            $response['body'] = array(
                'error' => array(
                    'code' => -32601,
                    'message' => 'Failed to decode JSON!'
                )
            );
        }

        if ($throw_error && !self::is_ok($response)) {
            throw new Sicm_NetworkException($response);
        }

        return $response;
    }

    public function login($email, $password) {
        $result = $this->call_rpc_method('users', 'login', array(
            'email' => $email,
            'password' => $password
        ), false);

        if (self::is_ok($result)) {
            $this->cookies = $result['cookies'];
            return true;
        }

        return false;
    }

    public function create_integration() {
        return $this->call_rpc_method('integrations', 'createWordPressIntegration', array(
            'siteName' => get_bloginfo('name'),
            'siteUrl' => get_bloginfo('url'),
            'wordPressVersion' => get_bloginfo('version')
        ));
    }

    public function classify_text($text) {
        return $this->call_rpc_method('classification', 'classifyText', array(
            'text' => $text
        ));
    }

    public function record_user_classification($content, $should_block) {
        return $this->call_rpc_method('classification', 'recordUserClassification', array(
            'content' => $content,
            'shouldBlock' => $should_block
        ));
    }

    public function fetch_analytics() {
        return $this->call_rpc_method('integrationAnalytics', 'getDailyRollupsMtd');
    }

    public static function is_ok($response) {
        return $response['response']['code'] === 200 &&
            !array_key_exists('error', $response['body']);
    }
}

class Sicm_NetworkException extends Exception {
    private $response = array();

    public function __construct(array $response) {
        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }
}