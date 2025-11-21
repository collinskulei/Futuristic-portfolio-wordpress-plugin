<?php
/**
 * Plugin Name: Futuristic Portfolio Display
 * Description: A single-file WordPress plugin for displaying futuristic project cards with interactive ratings, likes, and WhatsApp integration.
 * Version: 1.0.2
 * Author: Collins Kulei 
 */

/**This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details./*
/* ---------------------------
| 1) Custom Post Type & Meta Setup: Preparing the Project Dungeon
----------------------------*/

// Register the 'futuristic_project' Custom Post Type (CPT)
add_action('init', 'fp_register_cpt');
function fp_register_cpt() {
    $labels = [
        'name' => 'Futuristic Projects',
        'singular_name' => 'Futuristic Project',
        'add_new_item' => 'Add New Futuristic Project',
        'edit_item' => 'Edit Futuristic Project',
        'new_item' => 'New Futuristic Project',
        'view_item' => 'View Futuristic Project',
        'search_items' => 'Search Projects',
    ];
    $args = [
        'labels' => $labels,
        'public' => false, // Keep it off the frontend archives, it's too cool for that.
        'show_ui' => true, // Show the administrative interface
        'menu_icon' => 'dashicons-hammer', // Because we're building things!
        'supports' => ['title'], // Only need the title, all other data is custom
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => true,
    ];
    register_post_type('futuristic_project', $args);
}

/* ---------------------------
| 2) Admin Media Integration: Giving the Uploader its Caffeine Jolt
----------------------------*/

// Enqueue WordPress media scripts only on our CPT screen
add_action('admin_enqueue_scripts', 'fp_enqueue_media_uploader');
function fp_enqueue_media_uploader($hook) {
    if ('post.php' == $hook || 'post-new.php' == $hook) {
        $screen = get_current_screen();
        if ('futuristic_project' === $screen->post_type) {
            wp_enqueue_media();
            // Load the inline script that handles the magic
            add_action('admin_head', 'fp_media_uploader_scripts');
        }
    }
}

// The inline JavaScript that runs the media selector
function fp_media_uploader_scripts() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // The glorious function for opening the media frame
            function fp_open_media_uploader(target_input, target_preview) {
                let media_frame;
                if (media_frame) {
                    media_frame.open();
                    return;
                }
                media_frame = wp.media({
                    title: 'Choose Project Image',
                    button: { text: 'Use this image' },
                    multiple: false
                });

                media_frame.on('select', function() {
                    const attachment = media_frame.state().get('selection').first().toJSON();
                    target_input.val(attachment.url);
                    target_preview.html('<img src="' + attachment.url + '" style="max-width:150px; height:auto; border-radius: 4px;">');
                });

                media_frame.open();
            }

            // Hook up the buttons to the media frame
            $('.fp-upload-btn').on('click', function(e) {
                e.preventDefault();
                const target_id = $(this).data('target');
                const target_input = $('#' + target_id);
                const target_preview = $('#' + target_id + '-preview');
                fp_open_media_uploader(target_input, target_preview);
            });
            
            // Initial preview load for existing images
            $('.fp-upload-btn').each(function() {
                const target_id = $(this).data('target');
                const target_input = $('#' + target_id);
                const target_preview = $('#' + target_id + '-preview');
                const url = target_input.val();
                if (url) {
                    target_preview.html('<img src="' + url + '" style="max-width:150px; height:auto; border-radius: 4px;">');
                }
            });
        });
    </script>
    <?php
}

/* ---------------------------
| 3) Meta Box Setup: The Project Data Input Form
----------------------------*/

// Add the custom meta box to the CPT screen
add_action('add_meta_boxes', 'fp_add_meta_box');
function fp_add_meta_box() {
    add_meta_box(
        'fp_project_meta',
        'Project Details (The Sacred Data)',
        'fp_render_meta',
        'futuristic_project',
        'normal',
        'high'
    );
}

// Render the content of the meta box
function fp_render_meta($post) {
    // A security token (nonce) to protect our precious data
    wp_nonce_field('fp_save_meta_box_data', 'fp_meta_nonce');

    // Fetch existing values or default to empty
    $title_override = get_post_meta($post->ID, 'fp_title', true);
    $problem = get_post_meta($post->ID, 'fp_problem', true);
    $link = get_post_meta($post->ID, 'fp_link', true);
    $img1 = get_post_meta($post->ID, 'fp_img1', true);
    $img2 = get_post_meta($post->ID, 'fp_img2', true);
    $features = get_post_meta($post->ID, 'fp_features', true);
    $likes = get_post_meta($post->ID, 'fp_likes', true) ?: 0;
    $views = get_post_meta($post->ID, 'fp_views', true) ?: 0;
    $rating = get_post_meta($post->ID, 'fp_rating', true) ?: 0.0;
    $reviews = get_post_meta($post->ID, 'fp_reviews', true) ?: 0;

    ?>
    <style>
        .fp-meta-table td { padding: 8px 10px; }
        .fp-meta-table label { font-weight: bold; }
    </style>
    <table class="form-table fp-meta-table">
        <tr>
            <td colspan="2">
                <p>The title above is the *technical* title. Use the field below for the *display* title if you want it different.</p>
                <label for="fp_title">Display Title Override</label><br>
                <input type="text" id="fp_title" name="fp_title" value="<?php echo esc_attr($title_override); ?>" style="width: 100%;">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <label for="fp_problem">Problem/Goal Statement (The Project's Origin Story)</label><br>
                <textarea id="fp_problem" name="fp_problem" style="width: 100%; height: 100px;"><?php echo esc_textarea($problem); ?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <label for="fp_link">Live Project Link (Where the Magic Happens)</label><br>
                <input type="url" id="fp_link" name="fp_link" value="<?php echo esc_url($link); ?>" style="width: 100%;">
            </td>
            <td>
                <label for="fp_features">Key Features (One per line or comma-separated)</label><br>
                <textarea id="fp_features" name="fp_features" style="width: 100%; height: 100px;"><?php echo esc_textarea($features); ?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <label for="fp_img1">Card Image URL (The Teaser)</label><br>
                <input type="url" id="fp_img1" name="fp_img1" value="<?php echo esc_url($img1); ?>" style="width: 75%;">
                <button class="button fp-upload-btn" data-target="fp_img1">Upload</button>
                <div id="fp_img1-preview" style="margin-top: 5px;"></div>
            </td>
            <td>
                <label for="fp_img2">Modal Image URL (The Grand Reveal)</label><br>
                <input type="url" id="fp_img2" name="fp_img2" value="<?php echo esc_url($img2); ?>" style="width: 75%;">
                <button class="button fp-upload-btn" data-target="fp_img2">Upload</button>
                <div id="fp_img2-preview" style="margin-top: 5px;"></div>
            </td>
        </tr>
        <tr>
            <td><strong>Likes (Public Digital Affection)</strong>: <?php echo intval($likes); ?></td>
            <td><strong>Views (Curious Onlookers)</strong>: <?php echo intval($views); ?></td>
        </tr>
        <tr>
            <td><strong>Current Rating (The Consensus)</strong>: <?php echo number_format($rating, 1); ?> / 5</td>
            <td><strong>Total Reviews (Voices Heard)</strong>: <?php echo intval($reviews); ?></td>
        </tr>
    </table>
    <?php
}

/* ---------------------------
| 4) Data Saving: Locking the Vault
----------------------------*/

// Save the meta box content
add_action('save_post', 'fp_save_meta_box_data');
function fp_save_meta_box_data($post_id) {
    // Security checks first! Don't let the hackers in.
    if (!isset($_POST['fp_meta_nonce']) || !wp_verify_nonce($_POST['fp_meta_nonce'], 'fp_save_meta_box_data')) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    if ('futuristic_project' != $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // List of fields to save and their sanitization functions
    $fields_to_save = [
        'fp_title' => 'sanitize_text_field',
        'fp_problem' => 'wp_kses_post',
        'fp_link' => 'esc_url_raw',
        'fp_img1' => 'esc_url_raw',
        'fp_img2' => 'esc_url_raw',
        'fp_features' => 'wp_kses_post',
    ];

    foreach ($fields_to_save as $meta_key => $sanitizer) {
        if (isset($_POST[$meta_key])) {
            $data = call_user_func($sanitizer, $_POST[$meta_key]);
            update_post_meta($post_id, $meta_key, $data);
        }
    }
}


/* ---------------------------
| 5) Global Settings: The Control Room
----------------------------*/

// Add the settings page under 'Settings'
add_action('admin_menu', 'fp_add_settings_page');
function fp_add_settings_page() {
    add_options_page(
        'Futuristic Portfolio Settings',
        'Futuristic Portfolio',
        'manage_options',
        'fp_settings',
        'fp_render_settings_page'
    );
}

// Render the content of the settings page
function fp_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Futuristic Portfolio Global Settings (The Master Control Panel)</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('fp_options_group');
            do_settings_sections('fp_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register all the global settings
add_action('admin_init', 'fp_register_settings');
function fp_register_settings() {
    register_setting('fp_options_group', 'fp_global_whatsapp', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('fp_options_group', 'fp_predefined_message', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('fp_options_group', 'fp_color_card_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('fp_options_group', 'fp_color_title', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('fp_options_group', 'fp_color_popup_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('fp_options_group', 'fp_color_popup_border', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('fp_options_group', 'fp_color_button', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('fp_options_group', 'fp_color_text', ['sanitize_callback' => 'sanitize_hex_color']);

    // General Settings Section
    add_settings_section('fp_general_section', 'Global WhatsApp Integration (The Gossip Line)', null, 'fp_settings');
    add_settings_field('fp_global_whatsapp', 'WhatsApp Number (e.g., +2547XXXXXXXX)', 'fp_whatsapp_field', 'fp_settings', 'fp_general_section');
    add_settings_field('fp_predefined_message', 'Predefined Message (Use {link} placeholder)', 'fp_message_field', 'fp_settings', 'fp_general_section');

    // Color Settings Section
    add_settings_section('fp_color_section', 'Aesthetic Controls (The Glamour Shots)', null, 'fp_settings');
    add_settings_field('fp_color_card_bg', 'Card Background Color', 'fp_color_field', 'fp_settings', 'fp_color_section', ['option_name' => 'fp_color_card_bg', 'default' => '#0a0f24']);
    add_settings_field('fp_color_title', 'Title Color', 'fp_color_field', 'fp_settings', 'fp_color_section', ['option_name' => 'fp_color_title', 'default' => '#00eaff']);
    add_settings_field('fp_color_popup_bg', 'Modal Background Color', 'fp_color_field', 'fp_settings', 'fp_color_section', ['option_name' => 'fp_color_popup_bg', 'default' => '#0a0f24']);
    add_settings_field('fp_color_popup_border', 'Modal Border Color', 'fp_color_field', 'fp_settings', 'fp_color_section', ['option_name' => 'fp_color_popup_border', 'default' => '#00eaff']);
    add_settings_field('fp_color_button', 'Button Color', 'fp_color_field', 'fp_settings', 'fp_color_section', ['option_name' => 'fp_color_button', 'default' => '#00eaff']);
    add_settings_field('fp_color_text', 'Text Color', 'fp_color_field', 'fp_settings', 'fp_color_section', ['option_name' => 'fp_color_text', 'default' => '#eafcff']);
}

// Helper function for the WhatsApp number field
function fp_whatsapp_field() {
    $option = get_option('fp_global_whatsapp', '');
    echo '<input type="text" name="fp_global_whatsapp" value="' . esc_attr($option) . '" style="width: 300px;">';
}

// Helper function for the predefined message field
function fp_message_field() {
    $option = get_option('fp_predefined_message', 'Hi, I want a project like {link} on your website.');
    echo '<input type="text" name="fp_predefined_message" value="' . esc_attr($option) . '" style="width: 500px;">';
    echo '<p class="description">Use {link} to dynamically insert the project\'s live URL.</p>';
}

// Helper function for color fields
function fp_color_field($args) {
    $option_name = $args['option_name'];
    $default = $args['default'];
    $option = get_option($option_name, $default);
    echo '<input type="color" name="' . esc_attr($option_name) . '" value="' . esc_attr($option) . '">';
    // WordPress doesn't have a native color picker field, so this is the standard HTML way.
}


/* ---------------------------
| 6) The Frontend Grand Entrance Shortcode
----------------------------*/
function fp_render_portfolio($atts = [])
{
    // Snatch all the shiny projects
    $projects = get_posts(['post_type' => 'futuristic_project', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC']);

    // Acquire the sacred color palette
    $card_bg = esc_attr(get_option('fp_color_card_bg', '#0a0f24'));
    $title_color = esc_attr(get_option('fp_color_title', '#00eaff'));
    $popup_bg = esc_attr(get_option('fp_color_popup_bg', '#0a0f24'));
    $popup_border = esc_attr(get_option('fp_color_popup_border', '#00eaff'));
    $btn_color = esc_attr(get_option('fp_color_button', '#00eaff'));
    $text_color = esc_attr(get_option('fp_color_text', '#eafcff'));

    // Retrieve the Global Gossip Line (WhatsApp)
    $global_whatsapp = get_option('fp_global_whatsapp', '');
    // Unlock the Pre-Written Seduction Message
    $predefined_message_template = get_option('fp_predefined_message', 'Hi, I want a project like {link} on your website.');

    // Gather all the magical security tokens (Nonces)
    $nonce_like = wp_create_nonce('fp_like');
    $nonce_view = wp_create_nonce('fp_view');
    $nonce_rate = wp_create_nonce('fp_rate');

    // Engage the Output Buffer, commence scrolling!
    ob_start();
    ?>
    <style>
    /* The Grid That Bends But Doesn't Break */
    .fp-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;padding:18px;box-sizing:border-box}
    @media(max-width:1200px){.fp-grid{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:900px){.fp-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:500px){.fp-grid{grid-template-columns:repeat(1,1fr)}}

    /* Giving the Cards a Glow-Up */
    .fp-card{background:<?php echo $card_bg ?>;padding:14px;border-radius:12px;cursor:pointer;border:1px solid rgba(255,255,255,0.04);color:<?php echo $text_color ?>;transition:transform .18s, box-shadow .18s}
    .fp-card:hover{transform:translateY(-6px);box-shadow:0 10px 20px rgba(0,0,0,0.2)}
    .fp-card img{width:100%;height:160px;object-fit:cover;border-radius:8px}
    .fp-title{color:<?php echo $title_color ?>;font-weight:700;margin-top:8px}
    .fp-stars{color:#ffd700;margin-top:6px}
    .fp-meta{margin-top:8px;color:rgba(255,255,255,0.75);font-size:13px}

    /* The Modal, Ready to Scroll into Oblivion */
    .fp-modal-bg{
        display:none;position:fixed;inset:0;
        background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);
        justify-content:center;align-items:center;
        z-index:99999;padding:18px;
        overflow-y: auto; 
    }
    .fp-modal{
        background:<?php echo $popup_bg ?>;
        padding:20px;
        border-radius:14px;
        max-width:720px;
        width:100%;
        border:2px solid <?php echo $popup_border ?>;
        box-shadow:0 0 36px <?php echo $popup_border ?>33;
        color:<?php echo $text_color ?>;
        position:relative;
        /* Scrollable modal body */
        max-height: calc(100vh - 36px); 
        overflow-y: auto; 
    }
    
    /* Pimp My Buttons */
    .fp-btn{display:inline-block;padding:10px 14px;border-radius:9px;background:<?php echo $btn_color ?>;color:#000;font-weight:700;text-decoration:none;margin-right:8px;border:none;cursor:pointer}
    .fp-like{
        background:transparent;color:<?php echo $btn_color ?>;
        border:1px solid <?php echo $btn_color ?>;
        padding:8px 12px;border-radius:8px;cursor:pointer;
        transition:background 0.2s;
    }
    .fp-like:hover{background:rgba(0, 234, 255, 0.1);}
    
    /* The Escape Hatch Button */
    .fp-exit{
        position:absolute;top:10px;right:10px;
        padding:8px 12px;
        background:rgba(255,255,255,0.1);
        border:none;color:<?php echo $text_color ?>;
        cursor:pointer;border-radius:6px;
        font-weight:bold;
        transition:background 0.2s, color 0.2s;
        line-height:1;
        font-size:14px;
    }
    .fp-exit:hover{background:rgba(255,0,0,0.8); color:#fff;}

    /* Random Visual Enhancements */
    .fp-features-list{margin-top:12px;padding-left:18px}
    .fp-rating-stars{color:#ffd700;cursor:pointer;display:inline-block}
    .fp-rating-stars span{font-size:20px;margin-right:4px}
    .fp-review-form{margin-top:12px;border-top:1px dashed rgba(255,255,255,0.04);padding-top:12px}
    .fp-message{margin-left:8px;color:<?php echo $btn_color ?>;font-weight:bold;}
    </style>

    <div class="fp-grid" id="fp-grid">
        <?php foreach ($projects as $p):
            // Drill for Project Gold (Get project data)
            $id = $p->ID;
            $title = get_post_meta($id, 'fp_title', true) ?: get_the_title($id);
            $prob = get_post_meta($id, 'fp_problem', true);
            $link = get_post_meta($id, 'fp_link', true) ?: '#';
            $img1 = get_post_meta($id, 'fp_img1', true) ?: '';
            $img2 = get_post_meta($id, 'fp_img2', true) ?: $img1;
            $features = get_post_meta($id, 'fp_features', true);
            $rating = floatval(get_post_meta($id, 'fp_rating', true));
            $reviews = intval(get_post_meta($id, 'fp_reviews', true));
            $likes = intval(get_post_meta($id, 'fp_likes', true));
            $views = intval(get_post_meta($id, 'fp_views', true));
            $features_array = [];
            if ($features) {
                // Split features data
                if (strpos($features, "\n") !== false) $features_array = array_filter(array_map('trim', explode("\n", $features)));
                else $features_array = array_filter(array_map('trim', explode(',', $features)));
            }
            
            // --- WHATSAPP LINK FIX: Safely determine the 'I Want Like This' link ---
            $wa_link = '#'; // Default fallback (the haunted link)
            $wa_target = ''; // Default target attribute
            $wa_rel = ''; // Default rel attribute

            if (!empty($global_whatsapp)) {
                // 1. Clean the number for wa.me link
                $clean_num = preg_replace('/[^0-9+]/', '', $global_whatsapp);
                
                // 2. Prepare the message
                $raw_msg_template = $predefined_message_template;
                $final_msg_raw = str_replace('{link}', esc_url_raw($link), $raw_msg_template);
                $msg = rawurlencode($final_msg_raw);
                
                // 3. Construct the final link
                $wa_link = "https://wa.me/{$clean_num}?text={$msg}";
                $wa_target = '_blank';
                $wa_rel = 'noopener';
            } 
            // NOTE: If $global_whatsapp is empty, $wa_link remains '#', and $wa_target/rel remain empty.
            // --- END OF WHATSAPP LINK FIX ---
            ?>
            <div class="fp-card" data-id="<?php echo $id ?>" data-img2="<?php echo esc_url($img2) ?>" data-link="<?php echo esc_url($link) ?>" data-title="<?php echo esc_attr($title) ?>" data-prob="<?php echo esc_attr($prob) ?>" onclick="fp_open_modal(<?php echo $id ?>)">
                <?php if ($img1): ?><img src="<?php echo esc_url($img1) ?>" alt="Project Image"><?php endif; ?>
                <div class="fp-title"><?php echo esc_html($title) ?></div>
                <div class="fp-stars"><?php echo str_repeat('⭐', floor($rating)) ?> <small style="color:rgba(255,255,255,0.65)">(<?php echo $reviews ?>)</small></div>
                <div class="fp-meta">Likes: <span class="fp-like-count-<?php echo $id ?>"><?php echo $likes ?></span> · Views: <span class="fp-view-count-<?php echo $id ?>"><?php echo $views ?></span></div>
            </div>

            <!-- Project's Very Own Private Disco (Modal) -->
            <div class="fp-modal-bg" id="fp-modal-<?php echo $id ?>" onclick="if(event.target === this) fp_close_modal(<?php echo $id ?>)">
                <div class="fp-modal" role="dialog" aria-modal="true" aria-labelledby="fp-title-<?php echo $id ?>">
                    <button class="fp-exit" onclick="fp_close_modal(<?php echo $id ?>)">X</button>
                    <h2 id="fp-title-<?php echo $id ?>"><?php echo esc_html($title) ?></h2>
                    <p id="fp-prob-<?php echo $id ?>"><?php echo esc_html($prob) ?></p>

                    <?php if ($img2): ?><img src="<?php echo esc_url($img2) ?>" style="width:100%;border-radius:8px;margin:12px 0;height:auto;max-height:400px;object-fit:contain;"><?php endif; ?>

                    <?php if ($features_array): ?>
                        <div><strong>Features</strong>
                            <ul class="fp-features-list">
                                <?php foreach ($features_array as $feat): ?><li><?php echo esc_html($feat) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top:12px;">
                        <a class="fp-btn" href="<?php echo esc_url($link) ?>" target="_blank" rel="noopener">Visit Live</a>
                        <a class="fp-btn" href="<?php echo esc_url($wa_link) ?>" target="<?php echo $wa_target; ?>" rel="<?php echo $wa_rel; ?>">I Want Like This</a>
                        <button class="fp-like" onclick="fp_like(<?php echo $id ?>, this)">
                            ❤ Like <span id="fp-like-count-<?php echo $id ?>"><?php echo $likes ?></span>
                            <span class="fp-message" style="display:none;" id="fp-like-msg-<?php echo $id ?>"></span>
                        </button>
                    </div>

                    <div style="margin-top:12px;color:rgba(255,255,255,0.9)">Reviews: <strong id="fp-rev-count-<?php echo $id ?>"><?php echo $reviews ?></strong> — Avg: <strong id="fp-avg-<?php echo $id ?>"><?php echo number_format($rating, 1) ?></strong> / 5</div>

                    <!-- The People's Court of Ratings (Frontend Rating) -->
                    <div class="fp-review-form" id="fp-review-form-<?php echo $id ?>">
                        <h4>Add a rating</h4>
                        <div class="fp-rating-stars" id="fp-stars-<?php echo $id ?>">
                            <span data-star="1">☆</span><span data-star="2">☆</span><span data-star="3">☆</span><span data-star="4">☆</span><span data-star="5">☆</span>
                        </div>
                        <div style="margin-top:8px;">
                            <input type="text" id="fp-review-name-<?php echo $id ?>" placeholder="Your name (optional)" style="padding:8px;width:60%">
                            <button class="fp-btn" onclick="fp_submit_rating(<?php echo $id ?>)">Submit Rating</button>
                            <span id="fp-rate-msg-<?php echo $id ?>" class="fp-message"></span>
                        </div>
                    </div>

                </div>
            </div>

        <?php endforeach; ?>
    </div>

    <script>
        // Store nonces and AJAX URL in a global object (The Secret Scrolls)
        const fp_ajax = {
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            nonce_like: "<?php echo $nonce_like; ?>",
            nonce_view: "<?php echo $nonce_view; ?>",
            nonce_rate: "<?php echo $nonce_rate; ?>"
        };

        // Function to open the project modal
        function fp_open_modal(id) {
            const modal = document.getElementById('fp-modal-' + id);
            if (modal) {
                modal.style.display = 'flex';
                // The View Counter Must Go Up! (Fire off the AJAX for a view)
                if (!localStorage.getItem('fp_viewed_' + id)) {
                    fp_handle_view_ajax(id);
                }
            }
        }

        // Function to close the project modal
        function fp_close_modal(id) {
            const modal = document.getElementById('fp-modal-' + id);
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // AJAX function to Acknowledge the Gaze (Handle view count)
        function fp_handle_view_ajax(id) {
            const viewCountElement = document.querySelector('.fp-view-count-' + id);
            
            fetch(fp_ajax.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'fp_handle_view',
                    project_id: id,
                    nonce: fp_ajax.nonce_view
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && viewCountElement) {
                    viewCountElement.textContent = data.data.views;
                    localStorage.setItem('fp_viewed_' + id, 'true'); // One view per browser, per project.
                }
            })
            .catch(error => console.error('Error acknowledging the gaze:', error));
        }


        // Function to Register the Digital Heart Throb (Handle like)
        function fp_like(id, btn) {
            // The Like button has been deployed! Prevent repeated digital affection.
            if (localStorage.getItem('fp_liked_' + id)) {
                document.getElementById('fp-like-msg-' + id).textContent = 'You already love this one!';
                document.getElementById('fp-like-msg-' + id).style.display = 'inline';
                setTimeout(() => { document.getElementById('fp-like-msg-' + id).style.display = 'none'; }, 2000);
                return;
            }

            const likeCountElement = document.getElementById('fp-like-count-' + id);
            const cardLikeCountElement = document.querySelector('.fp-like-count-' + id);

            fetch(fp_ajax.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'fp_handle_like',
                    project_id: id,
                    nonce: fp_ajax.nonce_like
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    likeCountElement.textContent = data.data.likes;
                    if (cardLikeCountElement) cardLikeCountElement.textContent = data.data.likes;
                    localStorage.setItem('fp_liked_' + id, 'true');
                    document.getElementById('fp-like-msg-' + id).textContent = 'Digital affection received!';
                    document.getElementById('fp-like-msg-' + id).style.display = 'inline';
                    setTimeout(() => { document.getElementById('fp-like-msg-' + id).style.display = 'none'; }, 2000);
                }
            })
            .catch(error => console.error('Error registering heart throb:', error));
        }
        
        // Function to Process the Public Scrutiny (Rating)
        function fp_submit_rating(id) {
            // The People's Court of Ratings is in session. Let the stars fly!
            if (localStorage.getItem('fp_rated_' + id)) {
                document.getElementById('fp-rate-msg-' + id).textContent = 'You have already cast your vote, citizen!';
                return;
            }

            const starElements = document.querySelectorAll('#fp-stars-' + id + ' span');
            let rating = 0;
            starElements.forEach(span => {
                if (span.textContent === '★') {
                    rating = parseInt(span.dataset.star);
                }
            });

            if (rating === 0) {
                document.getElementById('fp-rate-msg-' + id).textContent = 'Please select a star rating first!';
                return;
            }
            
            const name = document.getElementById('fp-review-name-' + id).value;
            const rateMsg = document.getElementById('fp-rate-msg-' + id);

            rateMsg.textContent = 'Submitting...';

            fetch(fp_ajax.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'fp_handle_rate',
                    project_id: id,
                    nonce: fp_ajax.nonce_rate,
                    rating: rating,
                    name: name
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('fp-avg-' + id).textContent = parseFloat(data.data.new_avg).toFixed(1);
                    document.getElementById('fp-rev-count-' + id).textContent = data.data.new_reviews;
                    
                    // Update star rating in the main card (though it's only floor)
                    const cardStars = document.querySelector('.fp-card[data-id="' + id + '"] .fp-stars');
                    if(cardStars) {
                         const floorRating = '⭐'.repeat(Math.floor(data.data.new_avg));
                         const reviewCountText = cardStars.querySelector('small').outerHTML;
                         cardStars.innerHTML = floorRating + ' ' + reviewCountText;
                    }


                    localStorage.setItem('fp_rated_' + id, 'true');
                    rateMsg.textContent = 'Rating saved! Thank you, citizen.';
                } else {
                    rateMsg.textContent = data.data || 'Failed to submit rating (Check Console).';
                }
            })
            .catch(error => {
                console.error('Error processing public scrutiny:', error);
                rateMsg.textContent = 'A server error occurred. Try again later.';
            });
        }

        // Initialize star hover functionality for rating
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.fp-rating-stars').forEach(starContainer => {
                // The Stars Must Shine!
                starContainer.querySelectorAll('span').forEach(star => {
                    star.addEventListener('mouseover', function() {
                        const hoverRating = parseInt(this.dataset.star);
                        starContainer.querySelectorAll('span').forEach(s => {
                            const currentStar = parseInt(s.dataset.star);
                            s.textContent = currentStar <= hoverRating ? '★' : '☆';
                        });
                    });

                    star.addEventListener('mouseout', function() {
                        starContainer.querySelectorAll('span').forEach(s => s.textContent = '☆');
                    });

                    star.addEventListener('click', function() {
                        // Lock in the chosen rating (prevents mouseout from clearing it)
                        const finalRating = parseInt(this.dataset.star);
                        starContainer.querySelectorAll('span').forEach(s => {
                            const currentStar = parseInt(s.dataset.star);
                            s.textContent = currentStar <= finalRating ? '★' : '☆';
                        });
                        // Prevent accidental rating submission by using a local storage flag check in the submit function
                        // We don't submit here, we just select. Submission is on the button click.
                    });
                });
            });
        });

    </script>
    <?php

    // End buffering and return the HTML output
    return ob_get_clean();
}
add_shortcode('future_portfolio', 'fp_render_portfolio');
add_shortcode('futuristic_portfolio', 'fp_render_portfolio');


/* ---------------------------
| 7) AJAX Endpoints: The Back-End Wizards
----------------------------*/

// Acknowledge the Gaze: Handles a view event when the modal opens.
add_action('wp_ajax_fp_handle_view', 'fp_handle_view');
add_action('wp_ajax_nopriv_fp_handle_view', 'fp_handle_view');

function fp_handle_view() {
    // Security check on the magical token
    check_ajax_referer('fp_view', 'nonce');

    $project_id = intval($_POST['project_id']);
    if (!$project_id) {
        wp_send_json_error('Invalid project ID.');
    }

    // Increment views
    $current_views = intval(get_post_meta($project_id, 'fp_views', true));
    $new_views = $current_views + 1;
    update_post_meta($project_id, 'fp_views', $new_views);

    // Send back the new count for glory
    wp_send_json_success(['views' => $new_views]);
}

// Register the Digital Heart Throb: Handles a like event.
add_action('wp_ajax_fp_handle_like', 'fp_handle_like');
add_action('wp_ajax_nopriv_fp_handle_like', 'fp_handle_like');

function fp_handle_like() {
    // Security check on the magical token
    check_ajax_referer('fp_like', 'nonce');

    $project_id = intval($_POST['project_id']);
    if (!$project_id) {
        wp_send_json_error('Invalid project ID.');
    }

    // Increment likes
    $current_likes = intval(get_post_meta($project_id, 'fp_likes', true));
    $new_likes = $current_likes + 1;
    update_post_meta($project_id, 'fp_likes', $new_likes);

    // Send back the new count for digital affection
    wp_send_json_success(['likes' => $new_likes]);
}

// Process the Public Scrutiny (Rating): Handles submission of a new rating.
add_action('wp_ajax_fp_handle_rate', 'fp_handle_rate');
add_action('wp_ajax_nopriv_fp_handle_rate', 'fp_handle_rate');

function fp_handle_rate() {
    // Security check on the magical token
    check_ajax_referer('fp_rate', 'nonce');

    $project_id = intval($_POST['project_id']);
    $rating = intval($_POST['rating']);
    $name = sanitize_text_field($_POST['name'] ?? 'Anonymous Voyager');

    if (!$project_id || $rating < 1 || $rating > 5) {
        wp_send_json_error('Invalid project ID or rating value. (The stars are confusing us.)');
    }

    // Get the array of all ratings, or start a new one
    $all_ratings = get_post_meta($project_id, 'fp_ratings', true) ?: [];
    if (!is_array($all_ratings)) $all_ratings = [];

    // Add the new rating to the list
    $all_ratings[] = ['rating' => $rating, 'name' => $name, 'timestamp' => time()];
    update_post_meta($project_id, 'fp_ratings', $all_ratings);

    // Calculate the new average and count
    $total_ratings = count($all_ratings);
    $sum_ratings = array_sum(array_column($all_ratings, 'rating'));
    $new_avg = ($total_ratings > 0) ? ($sum_ratings / $total_ratings) : 0.0;
    
    // Update the simplified meta fields for display
    update_post_meta($project_id, 'fp_rating', $new_avg);
    update_post_meta($project_id, 'fp_reviews', $total_ratings);

    // Send back the updated consensus
    wp_send_json_success([
        'new_avg' => number_format($new_avg, 1), 
        'new_reviews' => $total_ratings
    ]);
}

/* ---------------------------
| 8) Admin Notice: Don't forget to enable!
----------------------------*/

// We should probably add a notice if the shortcode isn't used, but we'll leave that for a future quest.

