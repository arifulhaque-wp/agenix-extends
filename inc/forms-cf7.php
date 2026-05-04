<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------------
 * Register CF7 toggle
 * ------------------------------------------------------------------ */
add_action('admin_init', function(){
    register_setting('agenix_extends_cf7_group', 'agenix_extends_cf7', [
        'type' => 'array',
        'sanitize_callback' => function($value){
            return ['enabled' => !empty($value['enabled']) ? 1 : 0];
        }
    ]);

    add_settings_section(
        'agenix_extends_cf7_section',
        __('Contact Form 7 Extension', 'agenix-extends'),
        function(){ echo '<p>'.esc_html__('Enable or disable CF7 extension.', 'agenix-extends').'</p>'; },
        'agenix-extends-cf7'
    );

    add_settings_field('agenix_extends_cf7_enabled', __('Enable CF7 Extension', 'agenix-extends'), function(){
        $opts = get_option('agenix_extends_cf7', []);
        $enabled = !empty($opts['enabled']);
        ?>
        <label class="agenix-toggle">
            <input type="checkbox" name="agenix_extends_cf7[enabled]" value="1" <?php checked($enabled); ?> />
            <span class="slider round"></span>
        </label>
        <?php
    }, 'agenix-extends-cf7', 'agenix_extends_cf7_section');
});

/* ------------------------------------------------------------------
 * Templates
 * ------------------------------------------------------------------ */
function agenix_extends_get_form_templates(){
    return [
        'simple' => [
            'title'   => 'Simple Contact Form',
            'content' => '[text* your-name placeholder "Your Name"] [email* your-email placeholder "Your Email"] [textarea your-message placeholder "Message"] [submit "Send"]',
            'image'   => AGENIX_EXTENDS_URL.'assets/images/form1.png'
        ],
        'feedback' => [
            'title'   => 'Feedback Form',
            'content' => '[text* first-name placeholder "First Name"] [text* last-name placeholder "Last Name"] [tel* your-tel placeholder "123-456-7890"] [email* your-email placeholder "Email"] [textarea feedback placeholder "Your Feedback"] [submit "Submit Feedback"]',
            'image'   => AGENIX_EXTENDS_URL.'assets/images/form2.png'
        ],
        'support' => [
            'title'   => 'Support Request',
            'content' => '[text* first-name placeholder "First Name"] [text* last-name placeholder "Last Name"] [email* your-email placeholder "Email"] [text* subject placeholder "Subject"] [textarea details placeholder "Describe your issue"] [submit "Request Support"]',
            'image'   => AGENIX_EXTENDS_URL.'assets/images/form3.webp'
        ],
        'newsletter' => [
            'title'   => 'Newsletter Signup',
            'content' => '[email* your-email placeholder "Email"] [submit "Subscribe"]',
            'image'   => AGENIX_EXTENDS_URL.'assets/images/form4.jpg'
        ],
    ];
}

/* ------------------------------------------------------------------
 * Handle form creation via admin_post
 * ------------------------------------------------------------------ */
add_action('admin_post_agenix_extends_create_form', function(){
    $form_templates = agenix_extends_get_form_templates();
    $opts = get_option('agenix_extends_cf7', []);
    if(empty($opts['enabled'])) return;

    if(!empty($_POST['agenix_selected_form']) && check_admin_referer('agenix_extends_create_form')){
        $key = sanitize_text_field($_POST['agenix_selected_form']);
        if(isset($form_templates[$key])){
            $tpl = $form_templates[$key];

            // Create CF7 form post
            $post_id = wp_insert_post([
                'post_title'   => $tpl['title'],
                'post_type'    => 'wpcf7_contact_form',
                'post_status'  => 'publish',
            ]);

            if($post_id){
                $shortcode = '';

                // Save form content using CF7 API
                if ( class_exists( 'WPCF7_ContactForm' ) ) {
                    $contact_form = WPCF7_ContactForm::get_instance( $post_id );
                    if ( $contact_form ) {
                        $contact_form->set_properties( [
                            'form' => $tpl['content'],
                        ] );
                        $contact_form->save();

                        // Re-fetch to ensure shortcode is populated
                        $contact_form = WPCF7_ContactForm::get_instance( $post_id );
                        $shortcode = $contact_form->shortcode();
                    }
                } else {
                    // Fallback
                    wp_update_post([
                        'ID'           => $post_id,
                        'post_content' => $tpl['content'],
                    ]);
                    $shortcode = '[contact-form-7 id="'.$post_id.'" title="'.$tpl['title'].'"]';
                }

                // Store shortcode in transient
                set_transient('agenix_cf7_created_shortcode', $shortcode, 60);


                wp_safe_redirect(add_query_arg([
                    'page'    => 'agenix-extends',
                    'tab'     => 'forms'
                ], admin_url('admin.php')));
                exit;
            }
        }
    }
});

/* ------------------------------------------------------------------
 * Render UI
 * ------------------------------------------------------------------ */
function agenix_extends_forms_ui(){
    $form_templates = agenix_extends_get_form_templates();
    $opts = get_option('agenix_extends_cf7', []);

    // Show shortcode notice once (from transient)
    $created_shortcode = get_transient('agenix_cf7_created_shortcode');
    if($created_shortcode){
        echo '<div class="notice notice-success"><p>Form created successfully! Shortcode: <code>'.$created_shortcode.'</code></p></div>';
        delete_transient('agenix_cf7_created_shortcode'); // clear after showing once
    }

    if(!empty($opts['enabled'])): ?>
        <h3><?php esc_html_e('Choose a Form Template','agenix-extends'); ?></h3>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('agenix_extends_create_form'); ?>
            <input type="hidden" name="action" value="agenix_extends_create_form">
            <div class="agenix-forms-grid">
                <?php foreach($form_templates as $key => $tpl): ?>
                    <label class="agenix-form-card" style="display:block;cursor:pointer;border:1px solid #ddd;padding:10px;margin:10px;transition:border-color 0.2s;">
                        <input type="radio" name="agenix_selected_form" value="<?php echo esc_attr($key); ?>" style="margin-right:10px;">
                        <strong><?php echo esc_html($tpl['title']); ?></strong>
                        <img src="<?php echo esc_url($tpl['image']); ?>" alt="<?php echo esc_attr($tpl['title']); ?>" style="max-width:300px; display:block; margin:10px 0;">
                    </label>
                <?php endforeach; ?>
            </div>
            <?php submit_button(__('Create Form','agenix-extends')); ?>
        </form>
    <?php else: ?>
        <p><?php esc_html_e('Contact Form 7 extension is disabled. Enable it in settings to use templates.','agenix-extends'); ?></p>
    <?php endif;
}