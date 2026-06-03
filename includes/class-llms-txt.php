<?php
/**
 * Handle dynamic generation of /.well-known/llms.txt
 */

if (!defined('ABSPATH')) {
    exit;
}

class Glint_AI_WC_LLMS_Txt
{

    public function __construct()
    {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_llms_txt_request'));
    }

    /**
     * Add rewrite rule to intercept requests to /.well-known/llms.txt
     * This needs to be static to be called on plugin activation.
     */
    public static function add_rewrite_rules()
    {
        add_rewrite_rule('^\.well-known/llms\.txt$', 'index.php?glint_ai_llms_txt=1', 'top');
    }

    /**
     * Add custom query var so WordPress recognizes our rewrite rule.
     */
    public function add_query_vars($vars)
    {
        $vars[] = 'glint_ai_llms_txt';
        return $vars;
    }

    /**
     * Check if the current request is for llms.txt, and output it if so.
     */
    public function handle_llms_txt_request()
    {
        if (get_query_var('glint_ai_llms_txt') == 1) {

            // Base content
            $content = "# ST Chat Agent endpoints\n";
            $content .= "- GET /wp-json/glint-ai/v1/products: Retrieve HTML product cards for given product_ids\n";
            $content .= "- POST /wp-json/glint-ai/v1/coupon: Generate a single-use coupon\n";
            $content .= "- GET /wp-json/glint-ai/v1/order-status: Check the status of a specific order_id\n";

            // Allow other plugins to modify the content
            $content = apply_filters('generate_llms_txt_content', $content);

            // Output headers
            header('Content-Type: text/plain; charset=utf-8');
            header('X-Robots-Tag: noindex, follow');

            // Output content and exit
            echo $content;
            exit;
        }
    }
}
