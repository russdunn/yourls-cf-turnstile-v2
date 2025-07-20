<?php
/*
Plugin Name: Cloudflare Turnstile for YOURLS Admin v2 Improved
Plugin URI: https://github.com/russdunn/yourls-cf-turnstile-v2
Description: Adds Cloudflare Turnstile to the YOURLS Admin login page.
Version: 2.0
Author: Russ Dunn
Author URI: https://russdunn.com.au
*/

/*
Ideally, we want to farm all required bot / spam checks elsewhere, meaning this plugin aims to catch
entities prior to their interaction and have Cloudflare vet them before our server does any work at all.
If they (or them) are able to spam a submit buttom, regardless of Cloudflare, they can cause DDOS issues.
- Russ Dunn / July 2025
*/

if (!defined('YOURLS_ABSPATH')) die();

// Define Cloudflare (CF) Keys & Endpoints
// - Note: Testing keys are here...
//         https://developers.cloudflare.com/turnstile/troubleshooting/testing/
define('CF_TS_SCRIPT',          'https://challenges.cloudflare.com/turnstile/v0/api.js');
define('CF_TS_CHALLENGE',       'https://challenges.cloudflare.com/turnstile/v0/siteverify');
define('CF_TS_SITE_KEY',        '2x00000000000000000000BB');            // Your Site Key
define('CF_TS_SECRET_KEY',      '1x0000000000000000000000000000000AA'); // Your Secret Key

// Add CF Turnstile script to the head section of the HTML file
// - Note: This automatically initialises per Turnstile docs.
yourls_add_action('html_head', 'cf_ts_html_head');
function cf_ts_html_head() {
    echo '<script src="'.CF_TS_SCRIPT.'" async defer></script>';
}

// Add Cloudflare Turnstile widget to the YOURLS admin login form
yourls_add_action('login_form_bottom', 'cf_ts_login_form');
function cf_ts_login_form() {
    echo '<div class="cf-turnstile" data-sitekey="'.CF_TS_SITE_KEY.'" data-callback="cf_ts_cb"></div>';
}

// Configure login button protecton logic & css tweak
yourls_add_action('html_footer', 'cf_ts_html_footer');
function cf_ts_html_footer() {
    echo '
        <script>
            $(function(){
                $(\'#login input[type="submit"]\')
                    .prop(\'disabled\',true)
                    .css(\'cursor\', \'not-allowed\');
            });
            window.cf_ts_cb = function() {
                $(\'#login input[type="submit"]\')
                    .prop(\'disabled\',false)
                    .css(\'cursor\', \'\');

                console.debug(\'Russ Dunn / Cloudflare Turnstile: Open sesame\');
            };
        </script>
    ';
}

// Do serverside checks / kill the login if Turnstile fails to approve.
// - Note: This is only for username / password logins.......
yourls_add_action('pre_login_username_password', 'cf_ts_go');
function cf_ts_go() { 
    if ( isset( $_REQUEST['cf-turnstile-response'] ) && !empty( $_REQUEST['cf-turnstile-response'] ) ) {
        $ch      = curl_init(CF_TS_CHALLENGE);
        $payload = [
            'response' => $_REQUEST['cf-turnstile-response'],
            'secret'   => CF_TS_SECRET_KEY,
        ];

        curl_setopt_array($ch, [
            CURLOPT_POST            => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_POSTFIELDS      => http_build_query($payload)
        ]);

        $init = curl_exec($ch);
        if ($init === false) {
            curl_close($ch);
            yourls_die('Error: Cloudflare service unreachable');
        };

        $response = json_decode($init, true);
        if (!isset($response['success']) || $response['success'] !== true) {
            // Die here because there is no reason a legitimate user should fail after getting to this point.
            // - Bots should not even have a token but might have faked it to get here.
            // - Any real user can have another go after manually refreshing.
            // - In my opinion, to die is safer than yourls_login_screen()
            yourls_die('Something has gone wrong or you are a bot...');
        };
    } 

    else {
        // Die here because if reached, something malicious has probably happened.
        yourls_die('Something has gone wrong or you are a bot...');
    }
}
