<?php

/* ------------------------------------------------------------------
 * Register settings for Magnify tab
 * ------------------------------------------------------------------ */
add_action('admin_init', function(){
    register_setting('agenix_extends_magnify_group', 'agenix_extends_magnify', [
        'type' => 'array',
        'sanitize_callback' => function($value){
            return [
                'enabled' => !empty($value['enabled']) ? 1 : 0,
                'zoom'    => isset($value['zoom']) ? intval($value['zoom']) : 2,
            ];
        }
    ]);

    add_settings_section(
        'agenix_extends_magnify_section',
        __('Magnify Extension', 'agenix-extends'),
        function(){ echo '<p>'.esc_html__('Enable or disable Magnify extension.', 'agenix-extends').'</p>'; },
        'agenix-extends-magnify'
    );

    add_settings_field('agenix_extends_magnify_enabled', __('Enable Magnify', 'agenix-extends'), function(){
        $opts = get_option('agenix_extends_magnify', []);
        $enabled = !empty($opts['enabled']);
        ?>
        <label class="agenix-toggle">
            <input type="checkbox" name="agenix_extends_magnify[enabled]" value="1" <?php checked($enabled); ?> />
            <span class="slider round"></span>
        </label>
        <?php
    }, 'agenix-extends-magnify', 'agenix_extends_magnify_section');



    add_settings_field('agenix_extends_zoom', __('Zoom Level', 'agenix-extends'), function(){
        $opts = get_option('agenix_extends_magnify', []);
        $zoom = !empty($opts['zoom']) ? intval($opts['zoom']) : 2;
        ?>
        <div class="agenix-zoom-wrap">
            <input type="range" min="1" max="5" step="1"
                name="agenix_extends_magnify[zoom]"
                value="<?php echo esc_attr($zoom); ?>" id="agenix-zoom-range" />
            <span id="agenix-zoom-value"><?php echo esc_html($zoom).'×'; ?></span>
        </div>
        <?php
    }, 'agenix-extends-magnify', 'agenix_extends_magnify_section');
});


/* ------------------------------------------------------------------
 * Frontend magnify effect (only if enabled)
 * ------------------------------------------------------------------ */
add_action('wp_enqueue_scripts', function(){
    $opts = get_option('agenix_extends_magnify', []);
    if ( !empty($opts['enabled']) ) {
        wp_enqueue_script(
            'agenix-magnify',
            AGENIX_EXTENDS_URL.'assets/js/magnify.js',
            ['jquery'],
            '1.0.0',
            true
        );
        wp_enqueue_style(
            'agenix-magnify',
            AGENIX_EXTENDS_URL.'assets/css/magnify.css',
            [],
            '1.0.0'
        );

        // Pass zoom level to JS
        wp_localize_script('agenix-magnify', 'agenixMagnify', [
            'zoom' => !empty($opts['zoom']) ? intval($opts['zoom']) : 2,
        ]);
    }
});
