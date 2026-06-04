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
