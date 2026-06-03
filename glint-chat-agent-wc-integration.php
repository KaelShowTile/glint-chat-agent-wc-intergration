<?php
/**
 * Plugin Name: ST Chat Agent WC Integration
 * Description: Exposes REST API endpoints for an AI Agent to interact with WooCommerce (products, coupons, orders) securely with IP whitelist.
 * Version: 1.0.0
 * Author: Kael
 * Text Domain: glint-ai-wc
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('GLINT_AI_WC_VERSION', '1.0.0');
define('GLINT_AI_WC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GLINT_AI_WC_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Glint_AI_WC_Integration
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required core files.
     */
    private function includes()
    {
        require_once GLINT_AI_WC_PLUGIN_DIR . 'includes/class-settings.php';
        require_once GLINT_AI_WC_PLUGIN_DIR . 'includes/class-api-endpoints.php';
        require_once GLINT_AI_WC_PLUGIN_DIR . 'includes/class-llms-txt.php';
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks()
    {
        // Instantiate classes
        new Glint_AI_WC_Settings();
        new Glint_AI_WC_API_Endpoints();
        new Glint_AI_WC_LLMS_Txt();
    }

    /**
     * Helper method to get the client IP, checking for proxy headers.
     */
    public static function get_client_ip()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can be a comma-separated list, first one is the original IP
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Permission callback to check if IP is in the whitelist.
     */
    public static function check_ip_whitelist()
    {
        $whitelist_string = get_option('glint_ai_wc_ip_whitelist', '');
        if (empty($whitelist_string)) {
            return new WP_Error('rest_forbidden', __('API is disabled. No IPs in whitelist.', 'glint-ai-wc'), array('status' => 403));
        }

        $allowed_ips = array_map('trim', explode(',', $whitelist_string));
        $client_ip = self::get_client_ip();
        error_log('Client IP is: ' . $client_ip);

        if (in_array($client_ip, $allowed_ips, true)) {
            return true;
        }

        return new WP_Error('rest_forbidden', __('Your IP is not authorized to access this API.', 'glint-ai-wc'), array('status' => 403));
    }
}

// Initialize the plugin
function glint_ai_wc_init()
{
    new Glint_AI_WC_Integration();
}
add_action('plugins_loaded', 'glint_ai_wc_init');

// On activation, rewrite rules need to be flushed for the llms.txt rewrite
register_activation_hook(__FILE__, 'glint_ai_wc_activation');
function glint_ai_wc_activation()
{
    require_once GLINT_AI_WC_PLUGIN_DIR . 'includes/class-llms-txt.php';
    Glint_AI_WC_LLMS_Txt::add_rewrite_rules();
    flush_rewrite_rules();
}

// On deactivation, flush rules to remove our custom rule
register_deactivation_hook(__FILE__, 'glint_ai_wc_deactivation');
function glint_ai_wc_deactivation()
{
    flush_rewrite_rules();
}
