<div class="wrap">
    <h1><?php esc_html_e('API Endpoints', 'glint-ai-wc'); ?></h1>

    <p><?php esc_html_e('These are the available REST API endpoints for the ST Chat Agent. Ensure the IP of the agent is whitelisted in the Settings page.', 'glint-ai-wc'); ?>
    </p>

    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Method', 'glint-ai-wc'); ?></th>
                <th><?php esc_html_e('Endpoint', 'glint-ai-wc'); ?></th>
                <th><?php esc_html_e('Description', 'glint-ai-wc'); ?></th>
                <th><?php esc_html_e('Parameters', 'glint-ai-wc'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>GET</strong></td>
                <td><code>/wp-json/glint-ai/v1/products</code></td>
                <td><?php esc_html_e('Retrieve HTML product cards for given product IDs.', 'glint-ai-wc'); ?></td>
                <td><code>product_ids</code> (string, comma-separated IDs or array)</td>
            </tr>
            <tr>
                <td><strong>POST</strong></td>
                <td><code>/wp-json/glint-ai/v1/coupon</code></td>
                <td><?php esc_html_e('Generate a single-use coupon based on settings.', 'glint-ai-wc'); ?></td>
                <td><?php esc_html_e('None', 'glint-ai-wc'); ?></td>
            </tr>
            <tr>
                <td><strong>GET</strong></td>
                <td><code>/wp-json/glint-ai/v1/order-status</code></td>
                <td><?php esc_html_e('Check the status of a specific order.', 'glint-ai-wc'); ?></td>
                <td><code>order_id</code> (integer)</td>
            </tr>
        </tbody>
    </table>

    <h2 style="margin-top: 30px;"><?php esc_html_e('llms.txt', 'glint-ai-wc'); ?></h2>
    <p><?php esc_html_e('The LLM text definition file is available at:', 'glint-ai-wc'); ?></p>
    <p><code><a href="<?php echo esc_url(home_url('/.well-known/llms.txt')); ?>" target="_blank"><?php echo esc_url(home_url('/.well-known/llms.txt')); ?></a></code>
    </p>
</div>