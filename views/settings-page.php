<div class="wrap">
    <h1><?php esc_html_e('ST Chat Agent Settings', 'glint-ai-wc'); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('glint_ai_wc_settings_group');
        do_settings_sections('glint-ai-agent-settings');
        submit_button();
        ?>
    </form>
</div>