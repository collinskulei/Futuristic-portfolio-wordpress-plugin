<?php
/*
Plugin Name: Futuristic Portfolio Display (All Features)
Description: Futuristic portfolio CPT with media upload, features, whatsapp, frontend ratings, views, likes, and full display color settings.
Version: 1.2
Author: Collins Kulei
*/

if (!defined('ABSPATH')) exit;


// 1) Register CPT: Add Project

add_action('init', function () {
    $labels = [
        'name' => 'Add Project',
        'singular_name' => 'Project',
        'menu_name' => 'Add Project'
    ];
    register_post_type('futuristic_project', [
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-portfolio',
        'supports' => ['title']
    ]);
});

//2) Enqueue admin media on our screens

add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'])) return;
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'futuristic_project') return;

    wp_enqueue_media();
    //  for upload buttons
    $inline = <<<JS
jQuery(function($){
    $(document).on('click', '.fp-upload-btn', function(e){
        e.preventDefault();
        var button = $(this);
        var targetName = button.data('target');
        var input = button.siblings('input[name="'+targetName+'"]');
        var preview = button.siblings('img.fp-img-preview').first();

        var frame = wp.media({
            title: 'Select or Upload Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            input.val(att.url).trigger('change');
            if (preview.length) preview.attr('src', att.url).show();
        });

        frame.open();
    });
});
JS;
    wp_add_inline_script('jquery', $inline);
});


//Meta box: Project Details
-
add_action('add_meta_boxes', function () {
    add_meta_box('fp_details', 'Project Details', 'fp_render_meta', 'futuristic_project', 'normal', 'high');
});

function fp_render_meta($post)
{
    wp_nonce_field('fp_save_meta', 'fp_meta_nonce');

    $title = get_post_meta($post->ID, 'fp_title', true) ?: get_the_title($post->ID);
    $problem = get_post_meta($post->ID, 'fp_problem', true);
    $link = get_post_meta($post->ID, 'fp_link', true);
    $img1 = get_post_meta($post->ID, 'fp_img1', true);
    $img2 = get_post_meta($post->ID, 'fp_img2', true);
    // Note: fp_rating and fp_reviews are managed by the frontend AJAX handler
    $rating = floatval(get_post_meta($post->ID, 'fp_rating', true));
    $reviews = intval(get_post_meta($post->ID, 'fp_reviews', true));
    $features = get_post_meta($post->ID, 'fp_features', true);
    $whatsapp = get_post_meta($post->ID, 'fp_whatsapp', true);
    $likes = intval(get_post_meta($post->ID, 'fp_likes', true));
    $views = intval(get_post_meta($post->ID, 'fp_views', true));

    ?>
    <style>
        .fp-row{margin-bottom:12px}
        .fp-media-input{width:70%;padding:6px}
        .fp-img-preview{display:block;max-width:120px;margin-top:8px;border-radius:6px}
        .fp-features{width:100%;min-height:80px;padding:8px}
    </style>

    <div class="fp-row">
        <label><strong>Display Title</strong></label><br>
        <input type="text" name="fp_title" value="<?php echo esc_attr($title); ?>" style="width:100%;padding:6px;">
    </div>

    <div class="fp-row">
        <label><strong>Problem it Solves</strong></label><br>
        <textarea name="fp_problem" style="width:100%;padding:6px;"><?php echo esc_textarea($problem); ?></textarea>
    </div>

    <div class="fp-row">
        <label><strong>Project Link (Visit Live)</strong></label><br>
        <input type="url" name="fp_link" value="<?php echo esc_attr($link); ?>" style="width:100%;padding:6px;">
    </div>

    <div class="fp-row">
        <label><strong>Image 1</strong></label><br>
        <input class="fp-media-input" type="text" name="fp_img1" value="<?php echo esc_attr($img1); ?>">
        <button class="button fp-upload-btn" data-target="fp_img1">Upload / Select</button><br>
        <img class="fp-img-preview" src="<?php echo esc_url($img1); ?>" style="<?php echo $img1 ? '' : 'display:none'; ?>">
    </div>

    <div class="fp-row">
        <label><strong>Image 2</strong></label><br>
        <input class="fp-media-input" type="text" name="fp_img2" value="<?php echo esc_attr($img2); ?>">
        <button class="button fp-upload-btn" data-target="fp_img2">Upload / Select</button><br>
        <img class="fp-img-preview" src="<?php echo esc_url($img2); ?>" style="<?php echo $img2 ? '' : 'display:none'; ?>">
    </div>

    <div class="fp-row">
        <label><strong>Features (one per line)</strong></label><br>
        <textarea name="fp_features" class="fp-features" placeholder="List features, one per line"><?php echo esc_textarea($features); ?></textarea>
        <small>Also accepted as comma-separated. Displayed on the modal.</small>
    </div>

    <div class="fp-row">
        <label><strong>WhatsApp Number (with country code)</strong></label><br>
        <input type="text" name="fp_whatsapp" value="<?php echo esc_attr($whatsapp); ?>" placeholder="+254712345678" style="width:100%;padding:6px;">
        <small>Used by "I Want Like This" button — include country code, e.g. +254712345678</small>
    </div>

    <div class="fp-row" style="display:flex;gap:12px;align-items:flex-start">
        <div>
            <label><strong>Avg Rating (Read-Only)</strong></label><br>
            <input type="text" value="<?php echo number_format($rating, 1); ?>" readonly style="width:100px;padding:6px; background-color:#f0f0f0;">
        </div>

        <div>
            <label><strong>Review Count (Read-Only)</strong></label><br>
            <input type="text" value="<?php echo intval($reviews); ?>" readonly style="width:120px;padding:6px; background-color:#f0f0f0;">
        </div>

        <div>
            <label><strong>Likes</strong></label><br>
            <input type="number" name="fp_likes" value="<?php echo intval($likes); ?>" min="0" style="width:120px;padding:6px;">
        </div>

        <div>
            <label><strong>Views</strong></label><br>
            <input type="number" name="fp_views" value="<?php echo intval($views); ?>" min="0" style="width:120px;padding:6px;">
        </div>
    </div>

    <?php
}


//Save meta fields

add_action('save_post', function ($post_id) {
    if (!isset($_POST['fp_meta_nonce']) || !wp_verify_nonce($_POST['fp_meta_nonce'], 'fp_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (get_post_type($post_id) !== 'futuristic_project') return;

    $fields = [
        'fp_title' => 'text',
        'fp_problem' => 'text',
        'fp_link' => 'url',
        'fp_img1' => 'text',
        'fp_img2' => 'text',
        'fp_features' => 'text',
        'fp_whatsapp' => 'text',
        'fp_likes' => 'int',
        'fp_views' => 'int'
        
        // reviews and rating are updated via the AJAX handler, not manually here
    ];

    foreach ($fields as $f => $type) {
        if (!isset($_POST[$f])) continue;
        $val = $_POST[$f];
        if ($type === 'url') update_post_meta($post_id, $f, esc_url_raw($val));
        elseif ($type === 'int') update_post_meta($post_id, $f, intval($val));
        else update_post_meta($post_id, $f, sanitize_text_field($val));
    }
});


//Settings page: Display Colors (front-end)

add_action('admin_menu', function () {
    add_options_page('Futuristic Portfolio Settings', 'Futuristic Portfolio', 'manage_options', 'fp_settings', 'fp_settings_page');
});

function fp_settings_page()
{
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['fp_save'])) {
        check_admin_referer('fp_settings_save', 'fp_settings_nonce');
        update_option('fp_color_card_bg', sanitize_hex_color($_POST['fp_color_card_bg']));
        update_option('fp_color_title', sanitize_hex_color($_POST['fp_color_title']));
        update_option('fp_color_popup_bg', sanitize_hex_color($_POST['fp_color_popup_bg']));
        update_option('fp_color_popup_border', sanitize_hex_color($_POST['fp_color_popup_border']));
        update_option('fp_color_button', sanitize_hex_color($_POST['fp_color_button']));
        update_option('fp_color_text', sanitize_hex_color($_POST['fp_color_text']));
        echo '<div class="updated"><p>Saved.</p></div>';
    }

    $c_card = get_option('fp_color_card_bg', '#0a0f24');
    $c_title = get_option('fp_color_title', '#00eaff');
    $c_popup = get_option('fp_color_popup_bg', '#0a0f24');
    $c_border = get_option('fp_color_popup_border', '#00eaff');
    $c_btn = get_option('fp_color_button', '#00eaff');
    $c_text = get_option('fp_color_text', '#eafcff');

    ?>
    <div class="wrap">
        <h1>Futuristic Portfolio — Display Settings</h1>
        <form method="post">
            <?php wp_nonce_field('fp_settings_save', 'fp_settings_nonce'); ?>
            <table class="form-table">
                <tr><th>Card Background</th><td><input type="color" name="fp_color_card_bg" value="<?php echo esc_attr($c_card); ?>"></td></tr>
                <tr><th>Card Title Color</th><td><input type="color" name="fp_color_title" value="<?php echo esc_attr($c_title); ?>"></td></tr>
                <tr><th>Popup Background</th><td><input type="color" name="fp_color_popup_bg" value="<?php echo esc_attr($c_popup); ?>"></td></tr>
                <tr><th>Popup Border/Glow</th><td><input type="color" name="fp_color_popup_border" value="<?php echo esc_attr($c_border); ?>"></td></tr>
                <tr><th>Button Color</th><td><input type="color" name="fp_color_button" value="<?php echo esc_attr($c_btn); ?>"></td></tr>
                <tr><th>Popup Text Color</th><td><input type="color" name="fp_color_text" value="<?php echo esc_attr($c_text); ?>"></td></tr>
            </table>
            <p><button class="button button-primary" name="fp_save">Save Settings</button></p>
        </form>
    </div>
    <?php
}


// Shortcode render 

function fp_render_portfolio($atts = [])
{
    $projects = get_posts(['post_type' => 'futuristic_project', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC']);

    // colors
    $card_bg = esc_attr(get_option('fp_color_card_bg', '#0a0f24'));
    $title_color = esc_attr(get_option('fp_color_title', '#00eaff'));
    $popup_bg = esc_attr(get_option('fp_color_popup_bg', '#0a0f24'));
    $popup_border = esc_attr(get_option('fp_color_popup_border', '#00eaff'));
    $btn_color = esc_attr(get_option('fp_color_button', '#00eaff'));
    $text_color = esc_attr(get_option('fp_color_text', '#eafcff'));

    // nonces
    $nonce_like = wp_create_nonce('fp_like');
    $nonce_view = wp_create_nonce('fp_view');
    $nonce_rate = wp_create_nonce('fp_rate');

    ob_start();
    ?>
    <style>
    /* Grid */
    .fp-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;padding:18px;box-sizing:border-box}
    @media(max-width:1200px){.fp-grid{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:900px){.fp-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:500px){.fp-grid{grid-template-columns:repeat(1,1fr)}}

    /* Card */
    .fp-card{background:<?php echo $card_bg ?>;padding:14px;border-radius:12px;cursor:pointer;border:1px solid rgba(255,255,255,0.04);color:<?php echo $text_color ?>;transition:transform .18s, box-shadow .18s}
    .fp-card:hover{transform:translateY(-6px);box-shadow:0 10px 20px rgba(0,0,0,0.2)}
    .fp-card img{width:100%;height:160px;object-fit:cover;border-radius:8px}
    .fp-title{color:<?php echo $title_color ?>;font-weight:700;margin-top:8px}
    .fp-stars{color:#ffd700;margin-top:6px}
    .fp-meta{margin-top:8px;color:rgba(255,255,255,0.75);font-size:13px}

    /* Modal - MODIFIED FOR SCROLLING AND CONTAINMENT */
    .fp-modal-bg{
        display:none;position:fixed;inset:0;
        background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);
        justify-content:center;align-items:center;
        z-index:99999;padding:18px;
        overflow-y: auto; /* Fallback for very short content */
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
        /* Key changes for responsiveness and scrolling */
        max-height: calc(100vh - 36px); /* Ensure modal fits within viewport with padding */
        overflow-y: auto; /* Make modal content scrollable */
    }
    
    /* Buttons */
    .fp-btn{display:inline-block;padding:10px 14px;border-radius:9px;background:<?php echo $btn_color ?>;color:#000;font-weight:700;text-decoration:none;margin-right:8px;border:none;cursor:pointer}
    .fp-like{
        background:transparent;color:<?php echo $btn_color ?>;
        border:1px solid <?php echo $btn_color ?>;
        padding:8px 12px;border-radius:8px;cursor:pointer;
        transition:background 0.2s;
    }
    .fp-like:hover{background:rgba(0, 234, 255, 0.1);}
    
    /* Exit Button Refinement */
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

    /* Misc */
    .fp-features-list{margin-top:12px;padding-left:18px}
    .fp-rating-stars{color:#ffd700;cursor:pointer;display:inline-block}
    .fp-rating-stars span{font-size:20px;margin-right:4px}
    .fp-review-form{margin-top:12px;border-top:1px dashed rgba(255,255,255,0.04);padding-top:12px}
    .fp-message{margin-left:8px;color:<?php echo $btn_color ?>;font-weight:bold;}
    </style>

    <div class="fp-grid" id="fp-grid">
        <?php foreach ($projects as $p):
            $id = $p->ID;
            $title = get_post_meta($id, 'fp_title', true) ?: get_the_title($id);
            $prob = get_post_meta($id, 'fp_problem', true);
            $link = get_post_meta($id, 'fp_link', true) ?: '#';
            $img1 = get_post_meta($id, 'fp_img1', true) ?: '';
            $img2 = get_post_meta($id, 'fp_img2', true) ?: $img1;
            $features = get_post_meta($id, 'fp_features', true);
            $whatsapp = get_post_meta($id, 'fp_whatsapp', true);
            $rating = floatval(get_post_meta($id, 'fp_rating', true));
            $reviews = intval(get_post_meta($id, 'fp_reviews', true));
            $likes = intval(get_post_meta($id, 'fp_likes', true));
            $views = intval(get_post_meta($id, 'fp_views', true));
            $features_array = [];
            if ($features) {
                // support newline or comma separated
                if (strpos($features, "\n") !== false) $features_array = array_filter(array_map('trim', explode("\n", $features)));
                else $features_array = array_filter(array_map('trim', explode(',', $features)));
            }
            ?>
            <div class="fp-card" data-id="<?php echo $id ?>" data-img2="<?php echo esc_url($img2) ?>" data-link="<?php echo esc_url($link) ?>" data-title="<?php echo esc_attr($title) ?>" data-prob="<?php echo esc_attr($prob) ?>" onclick="fp_open_modal(<?php echo $id ?>)">
                <?php if ($img1): ?><img src="<?php echo esc_url($img1) ?>" alt="Project Image"><?php endif; ?>
                <div class="fp-title"><?php echo esc_html($title) ?></div>
                <div class="fp-stars"><?php echo str_repeat('⭐', floor($rating)) ?> <small style="color:rgba(255,255,255,0.65)">(<?php echo $reviews ?>)</small></div>
                <div class="fp-meta">Likes: <span class="fp-like-count-<?php echo $id ?>"><?php echo $likes ?></span> · Views: <span class="fp-view-count-<?php echo $id ?>"><?php echo $views ?></span></div>
            </div>

            <!-- modal -->
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
                        <?php
                        // WhatsApp button target (if provided) otherwise fallback to link
                        $wa_link = '#';
                        if ($whatsapp) {
                            $clean = preg_replace('/[^0-9+]/', '', $whatsapp);
                            $msg = rawurlencode("Hi — I want a project like: {$title} ({$link})");
                            $wa_link = "https://api.whatsapp.com/send?phone={$clean}&text={$msg}";
                        }
                        ?>
                        <a class="fp-btn" href="<?php echo esc_url($link) ?>" target="_blank" rel="noopener">Visit Live</a>
                        <a class="fp-btn" href="<?php echo esc_url($wa_link) ?>" target="_blank" rel="noopener">I Want Like This</a>
                        <button class="fp-like" onclick="fp_like(<?php echo $id ?>, this)">
                            ❤ Like <span id="fp-like-count-<?php echo $id ?>"><?php echo $likes ?></span>
                            <span class="fp-message" style="display:none;" id="fp-like-msg-<?php echo $id ?>"></span>
                        </button>
                    </div>

                    <div style="margin-top:12px;color:rgba(255,255,255,0.9)">Reviews: <strong id="fp-rev-count-<?php echo $id ?>"><?php echo $reviews ?></strong> — Avg: <strong id="fp-avg-<?php echo $id ?>"><?php echo number_format($rating, 1) ?></strong> / 5</div>

                    <!-- Front-end rating -->
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
    (function(){
        var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var nonceLike = '<?php echo $nonce_like; ?>';
        var nonceView = '<?php echo $nonce_view; ?>';
        var nonceRate = '<?php echo $nonce_rate; ?>';

        function fp_show_message(id, type, message, duration = 2000) {
            var msgEl = document.getElementById('fp-' + type + '-msg-' + id);
            if (!msgEl) return;
            var originalContent = msgEl.textContent;
            msgEl.textContent = message;
            msgEl.style.display = 'inline';
            setTimeout(function() {
                msgEl.style.display = 'none';
                // Reset content if it was for rating form
                if(type === 'rate') msgEl.textContent = originalContent;
            }, duration);
        }

        // open modal, increment views
        window.fp_open_modal = function(id){
            var el = document.getElementById('fp-modal-'+id);
            if(!el) return;
            el.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            // increment views (AJAX)
            fetch(ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'action=fp_view&post_id='+encodeURIComponent(id)+'&nonce='+encodeURIComponent(nonceView)
            }).then(res=>res.json()).then(function(r){
                if (r.success) {
                    var elCount = document.querySelector('.fp-view-count-'+id) || document.getElementById('fp-view-count-'+id);
                    if (elCount) elCount.textContent = r.data.views;
                }
            }).catch(function(){});
        };

        window.fp_close_modal = function(id){
            var el = document.getElementById('fp-modal-'+id);
            if(!el) return;
            el.style.display = 'none';
            document.body.style.overflow = '';
        };

        // likes: prevent multiple likes by storing in localStorage
        window.fp_like = function(id, btn){
            var key = 'fp_liked_'+id;
            if (localStorage.getItem(key)) {
                return fp_show_message(id, 'like', 'Already liked!', 1500);
            }
            
            fp_show_message(id, 'like', 'Liking...');

            fetch(ajaxUrl, {
                method:'POST',
                credentials:'same-origin',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'action=fp_like&post_id='+encodeURIComponent(id)+'&nonce='+encodeURIComponent(nonceLike)
            }).then(r=>r.json()).then(function(res){
                if (res.success) {
                    localStorage.setItem(key, '1');
                    var cntEl = document.getElementById('fp-like-count-'+id) || document.querySelector('.fp-like-count-'+id);
                    if (cntEl) cntEl.textContent = res.data.likes;
                    fp_show_message(id, 'like', 'Thanks!', 1000);
                } else {
                    fp_show_message(id, 'like', res.data || 'Failed to like', 1500);
                }
            }).catch(function(){ fp_show_message(id, 'like', 'Error liking.', 1500); });
        };

        // Rating stars UI
        document.addEventListener('click', function(e){
            if (!e.target) return;
            var star = e.target.closest && e.target.closest('[data-star]');
            if (!star) return;
            var container = star.parentNode;
            // Only proceed if it is a rating container
            if (!container.id.startsWith('fp-stars-')) return;

            var selected = parseInt(star.getAttribute('data-star'));
            
            // fill stars
            var spans = container.querySelectorAll('span[data-star]');
            spans.forEach(function(s){
                var sVal = parseInt(s.getAttribute('data-star'));
                s.textContent = sVal <= selected ? '★' : '☆';
            });
            container.setAttribute('data-selected', selected);
        });

        // submit rating
        window.fp_submit_rating = function(id){
            var starsContainer = document.querySelector('#fp-stars-'+id);
            var stars = starsContainer.getAttribute('data-selected') || 0;
            stars = parseInt(stars);
            
            if (!stars || stars < 1 || stars > 5) {
                fp_show_message(id, 'rate', 'Select 1–5 stars first', 1500);
                return;
            }

            // --- RATING DEDUPLICATION LOGIC ADDED HERE ---
            var key = 'fp_rated_' + id;
            if (localStorage.getItem(key)) {
                fp_show_message(id, 'rate', 'You already rated this!', 2000);
                return;
            }
            // ---------------------------------------------
            
            var name = encodeURIComponent(document.getElementById('fp-review-name-'+id).value || 'Guest');

            fp_show_message(id, 'rate', 'Submitting...');

            fetch(ajaxUrl, {
                method:'POST',
                credentials:'same-origin',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'action=fp_rate&post_id='+encodeURIComponent(id)+'&stars='+encodeURIComponent(stars)+'&name='+name+'&nonce='+encodeURIComponent(nonceRate)
            }).then(r=>r.json()).then(function(res){
                if (res.success) {
                    // Set flag in localStorage on successful submission
                    localStorage.setItem(key, '1');

                    fp_show_message(id, 'rate', 'Thanks — rating saved', 2000);
                    // update counts and avg
                    var revCount = document.getElementById('fp-rev-count-'+id);
                    var avg = document.getElementById('fp-avg-'+id);
                    if (revCount) revCount.textContent = res.data.reviews;
                    if (avg) avg.textContent = parseFloat(res.data.avg).toFixed(1); // Ensure one decimal place for display
                    
                    // reset star selection
                    var spans = starsContainer.querySelectorAll('span');
                    spans.forEach(function(s){ s.textContent = '☆'; });
                    starsContainer.removeAttribute('data-selected');
                } else {
                    fp_show_message(id, 'rate', res.data || 'Failed to save rating', 1500);
                }
            }).catch(function(){ fp_show_message(id, 'rate', 'Error saving rating.', 1500); });
        };

    })();
    </script>

    <?php
    return ob_get_clean();
}

add_shortcode('future_portfolio', 'fp_render_portfolio');
add_shortcode('futuristic_portfolio', 'fp_render_portfolio');




//7) AJAX: like, view, rate


// Like
add_action('wp_ajax_fp_like', 'fp_handle_like');
add_action('wp_ajax_nopriv_fp_like', 'fp_handle_like');
function fp_handle_like(){
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fp_like')) wp_send_json_error('Invalid nonce');
    $post_id = intval($_POST['post_id'] ?? 0);
    if (!$post_id) wp_send_json_error('Invalid post');

    $likes = intval(get_post_meta($post_id, 'fp_likes', true));
    $likes++;
    update_post_meta($post_id, 'fp_likes', $likes);

    wp_send_json_success(['likes' => $likes]);
}

// View
add_action('wp_ajax_fp_view', 'fp_handle_view');
add_action('wp_ajax_nopriv_fp_view', 'fp_handle_view');
function fp_handle_view(){
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fp_view')) wp_send_json_error('Invalid nonce');
    $post_id = intval($_POST['post_id'] ?? 0);
    if (!$post_id) wp_send_json_error('Invalid post');

    $views = intval(get_post_meta($post_id, 'fp_views', true));
    $views++;
    update_post_meta($post_id, 'fp_views', $views);

    wp_send_json_success(['views' => $views]);
}

// Rating
add_action('wp_ajax_fp_rate', 'fp_handle_rate');
add_action('wp_ajax_nopriv_fp_rate', 'fp_handle_rate');
function fp_handle_rate(){
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fp_rate')) wp_send_json_error('Invalid nonce');
    $post_id = intval($_POST['post_id'] ?? 0);
    $stars = intval($_POST['stars'] ?? 0);
    $name = sanitize_text_field($_POST['name'] ?? 'Guest');

    if (!$post_id || $stars < 1 || $stars > 5) wp_send_json_error('Invalid data');

    // Fetch all existing ratings
    $ratings = get_post_meta($post_id, 'fp_ratings', true);
    if (!is_array($ratings)) $ratings = [];

    // Add the new rating
    $r = [
        'id' => uniqid('r', true),
        'stars' => $stars,
        'name' => $name,
        'date' => current_time('mysql')
    ];
    $ratings[] = $r;
    
    // Save the updated list of ratings
    update_post_meta($post_id, 'fp_ratings', $ratings);

    // Update aggregate: count & average
    $count = count($ratings);
    $sum = 0;
    foreach ($ratings as $rr) $sum += intval($rr['stars'] ?? 0);
    $avg = round($sum / max(1, $count), 2);
    
    // Update display meta keys
    update_post_meta($post_id, 'fp_reviews', $count);
    update_post_meta($post_id, 'fp_rating', $avg);

    wp_send_json_success(['reviews' => $count, 'avg' => $avg]);
}




 //Admin notice helper to create sample project (optional)


add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) return;
    global $pagenow;
    if (!in_array($pagenow, ['post.php', 'post-new.php', 'edit.php'])) return;
    $found = get_posts(['post_type' => 'futuristic_project', 'posts_per_page' => 1]);
    if (empty($found)) {
        echo '<div class="notice notice-info is-dismissible"><p><strong>Futuristic Portfolio:</strong> No projects yet. Add one under <em>Add Project</em> in the WP admin. Use the media uploader (Upload/Select). Use shortcode <code>[future_portfolio]</code>.</p></div>';
    }
});


// If you are reading this you are free to customize it as you wish. HAVE FUN!