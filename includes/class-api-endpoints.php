<?php
/**
 * API Endpoints Class
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Glint_AI_WC_API_Endpoints {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        // Products Endpoint
        register_rest_route( 'glint-ai/v1', '/products', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_products' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'product_ids' => array(
                    'required'          => true,
                    'sanitize_callback' => function( $param ) {
                        if ( is_string( $param ) ) {
                            $param = str_replace( array( '[', ']', '"', "'" ), '', $param );
                        }
                        return wp_parse_id_list( $param );
                    },
                ),
            ),
        ) );

        // Coupon Endpoint
        register_rest_route( 'glint-ai/v1', '/coupon', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'generate_coupon' ),
            'permission_callback' => '__return_true',
        ) );

        // Order Status Endpoint
        register_rest_route( 'glint-ai/v1', '/order-status', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_order_status' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'order_id' => array(
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ) );
    }

    /**
     * Get HTML cards for products.
     */
    public function get_products( WP_REST_Request $request ) {
        $product_ids = array_filter( $request->get_param( 'product_ids' ) );

        if ( empty( $product_ids ) ) {
            return new WP_Error( 'invalid_args', __( 'Invalid product IDs.', 'glint-ai-wc' ), array( 'status' => 400 ) );
        }

        $cards = array();

        foreach ( $product_ids as $id ) {
            $product = wc_get_product( $id );
            if ( ! $product ) {
                continue;
            }

            // Get Image
            $image_html = $product->get_image( 'woocommerce_thumbnail' );

            //get link
            $product_url = $product->get_permalink();

            // Get attribute
            $product_size = $product->get_attribute( 'pa_size' );
            $product_color = $product->get_attribute( 'pa_colour' );
            $product_finish = $product->get_attribute( 'pa_finish' );
            $product_design = $product->get_attribute( 'pa_design' );

            // Get Add to Cart URL and text
            $add_to_cart_url = site_url() . "/?add-to-cart=" . $id;
            $add_to_cart_text = $product->add_to_cart_text();

            // Get step and suffix
            $step_value = 0;
            $product_suffix = null;
            $price_string;
            
            if(function_exists( 'get_product_qty_data' ) ){
                $step_value = get_product_qty_data($id);
                $product_suffix = get_product_qty_suffix($id);

                if($product_suffix && $step_value > 0){
                    $regluar_price = round((($product->get_regular_price())/$step_value),2);

                    if($product->is_on_sale()){
                        $sale_price =  round((($product->get_sale_price())/$step_value),2);
                        $price_string = '<del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $regluar_price . '</bdi></span></del> <span class="screen-reader-text">Original price was: $40.89.</span><ins aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $sale_price . '/' . $product_suffix . '</bdi></span></ins><span class="screen-reader-text">Current price is: $35.25.</span>';
                    }else{
                        $price_string = '<ins aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $regluar_price . '/' . $product_suffix . '</bdi></span></ins>';
                    }
                }
            }

            // Construct HTML Card
            $card_html = '<div class="glint-ai-product-card" style="background: #efefef; border: 1px solid #232f3d;">';
            $card_html .= '<a href="' . $product_url . '"><div class="product-image">' . $image_html . '</div></a>';
            $card_html .= '<a href="' . $product_url . '"><h3 class="product-title" style="font-size: 0.8rem;margin: 10px 15px;">' . esc_html( $product->get_name() ) . '</h3></a>';
            
            if ( $product_size || $product_color || $product_finish || $product_design) {
                $card_html .= '<ul style="padding: 0; margin: 0 15px 10px;">';
                if($product_size){
                    $card_html .= '<li style="font-size: 0.75rem;">Size: ' . $product_size . '</li>';
                }
                if($product_color){
                    $card_html .= '<li style="font-size: 0.75rem;">Colour: ' . $product_color . '</li>';
                }
                if($product_finish){
                    $card_html .= '<li style="font-size: 0.75rem;">Finish: ' . $product_finish . '</li>';
                }
                if($product_design){
                    $card_html .= '<li style="font-size: 0.75rem;">Design: ' . $product_design . '</li>';
                }
                $card_html .= '</ul>';
            }

            if($product_suffix && $step_value > 0){
                $card_html .= '<div class="product-price" style="margin: 0 15px; padding-bottom: 10px; font-size: 1.2rem; font-weight: 700;">' . $price_string . '</div>';
            }else{
                $card_html .= '<div class="product-price" style="margin: 0 15px; padding-bottom: 10px; font-size: 1.2rem; font-weight: 700;">' . $product->get_price_html() . '</div>';
            } 
            $card_html .= '<a href="' . esc_url( $add_to_cart_url ) . '" class="button add_to_cart_button" style="display: inline-block; width: 100%; text-align: center; background: #232f3d; color: #fff; margin-top: 0; font-size: 0.8rem;">' . esc_html( $add_to_cart_text ) . '</a>';
            $card_html .= '</div>';

            $cards[] = array(
                'id'    => $product->get_id(),
                'title' => $product->get_name(),
                'html'  => $card_html,
            );
        }

        return rest_ensure_response( array( 'products' => $cards ) );
    }

    /**
     * Generate a single-use coupon.
     */
    public function generate_coupon( WP_REST_Request $request ) {
        // Generate a random coupon code
        $coupon_code = strtoupper( substr( md5( uniqid( rand(), true ) ), 0, 8 ) );

        $coupon = new WC_Coupon();
        $coupon->set_code( $coupon_code );

        // Get Settings
        $discount_type = get_option( 'glint_ai_wc_coupon_discount_type', 'percent' );
        $amount = get_option( 'glint_ai_wc_coupon_amount', '10' );
        $expiry_days = get_option( 'glint_ai_wc_coupon_expiry_days', '7' );
        $categories_str = get_option( 'glint_ai_wc_coupon_categories', '' );

        $coupon->set_discount_type( $discount_type );
        $coupon->set_amount( $amount );

        // Single use only
        $coupon->set_usage_limit( 1 );
        $coupon->set_individual_use( true );

        // Expiry
        if ( ! empty( $expiry_days ) ) {
            $expiry_date = strtotime( "+{$expiry_days} days" );
            $coupon->set_date_expires( $expiry_date );
        }

        // Categories
        if ( ! empty( $categories_str ) ) {
            $category_ids = array_map( 'intval', explode( ',', $categories_str ) );
            if ( ! empty( $category_ids ) ) {
                $coupon->set_product_categories( $category_ids );
            }
        }

        $coupon->save();

        return rest_ensure_response( array(
            'code'          => $coupon_code,
            'discount_type' => $discount_type,
            'amount'        => $amount,
            'expiry_date'   => isset( $expiry_date ) ? date( 'Y-m-d H:i:s', $expiry_date ) : null,
            'message'       => __( 'Coupon generated successfully.', 'glint-ai-wc' )
        ) );
    }

    /**
     * Check order status.
     */
    public function get_order_status( WP_REST_Request $request ) {
        $order_id = $request->get_param( 'order_id' );
        
        $order = wc_get_order( $order_id );
        
        if ( ! $order ) {
            return new WP_Error( 'not_found', __( 'Order not found.', 'glint-ai-wc' ), array( 'status' => 404 ) );
        }

        return rest_ensure_response( array(
            'order_id' => $order->get_id(),
            'status'   => $order->get_status(),
        ) );
    }
}
