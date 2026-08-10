<?php
/**************************************************************************
***************************************************************************/
/* DON'T REMOVE THIS LINE 👇🏻 */
include( trailingslashit( get_stylesheet_directory() ) . 'ChildInit.php' );
/* DON'T REMOVE THIS LINE 👆🏻 */
/**************************************************************************
***************************************************************************/

// Product reviews and Q&A (kept in the child theme so parent updates are safe).
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/product-community.php';

// Stable 12-hour product shuffle for product category archives.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/category-product-shuffle.php';

/**************************************************************************
✅ START EDIT FROM HERE 👇🏻
HAPPY CODING 😊
***************************************************************************/








// نمایش معرفی دسته و زیردسته‌ها - بالای لیست محصولات
add_action('woocommerce_before_shop_loop', 'display_category_intro_and_subcats', 15);
function display_category_intro_and_subcats() {
    if (!is_product_category()) return;
    
    $term = get_queried_object();
    $term_id = $term->term_id;
    
    // خواندن معرفی دسته
    $category_intro = get_field('category_intro', 'product_cat_' . $term_id);
    
    // خواندن زیردسته‌ها به صورت flat
    $subcats = [];
    for ($i = 0; $i < 50; $i++) {
        $image_id = get_term_meta($term_id, "sub_categories_{$i}_subcat_image", true);
        $title = get_term_meta($term_id, "sub_categories_{$i}_subcat_title", true);
        $link = get_term_meta($term_id, "sub_categories_{$i}_subcat_link", true);
        
        if (!$image_id && !$title && !$link) break;
        
        $subcats[] = [
            'image_id' => $image_id,
            'title' => $title,
            'link' => $link
        ];
    }
    
    // اگه هیچکدوم نبود، چیزی نمایش نده
    if (empty($category_intro) && empty($subcats)) return;
    
    ?>
    <style>
        /* فورس کردن برای شکستن float و اومدن زیر عنوان */
        .woocommerce-products-header::after,
        .page-title::after,
        header.woocommerce-products-header::after {
            content: "" !important;
            display: table !important;
            clear: both !important;
        }
        
        .cloz-category-intro-section {
            display: block !important;
            position: relative !important;
            clear: both !important;
            width: 100% !important;
            margin: 30px 0 40px 0 !important;
            padding: 0 !important;
            float: none !important;
        }
        
        /* فورس کردن برای اومدن زیر عنوان */
        .woocommerce-products-header + .cloz-category-intro-section,
        .page-title + .cloz-category-intro-section,
        h1 + .cloz-category-intro-section,
        header + .cloz-category-intro-section {
            display: block !important;
            clear: both !important;
            float: none !important;
            width: 100% !important;
        }
        
        /* اگه عنوان flex یا grid داره، این رو override کن */
        .woocommerce-products-header,
        header.woocommerce-products-header {
            display: block !important;
            width: 100% !important;
        }
        
        .cloz-category-intro-text {
            margin: 0 0 25px 0 !important;
            padding: 18px 24px !important;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
            border-right: 4px solid #0ea5e9 !important;
            border-radius: 8px !important;
            color: #475569 !important;
            font-size: 15px !important;
            line-height: 1.8 !important;
            direction: rtl !important;
            text-align: right !important;
            font-weight: 400 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        }
        
        .cloz-category-intro-text p {
            margin: 0 !important;
            padding: 0 !important;
            color: #475569 !important;
        }
        
        .cloz-category-intro-text p:not(:last-child) {
            margin-bottom: 10px !important;
        }
        
        .cloz-subcategories-grid {
            display: grid !important;
            grid-template-columns: repeat(6, 1fr) !important;
            gap: 16px !important;
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        
        .cloz-subcat-card {
            background: #fff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 20px !important;
            padding: 0 !important;
            margin: 0 !important;
            text-align: center !important;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
            text-decoration: none !important;
            display: flex !important;
            flex-direction: column !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
            overflow: hidden !important;
            position: relative !important;
        }
        
        .cloz-subcat-card:hover {
            transform: translateY(-6px) scale(1.02) !important;
            box-shadow: 0 12px 28px rgba(14,165,233,0.2) !important;
            border-color: #0ea5e9 !important;
        }
        
        .cloz-subcat-image-wrapper {
            width: 100% !important;
            height: 160px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
            overflow: hidden !important;
            border-radius: 20px 20px 0 0 !important;
        }
        
        .cloz-subcat-image {
            max-width: 100% !important;
            max-height: 100% !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            border-radius: 16px !important;
            margin: 0 !important;
            display: block !important;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .cloz-subcat-card:hover .cloz-subcat-image {
            transform: scale(1.08) !important;
        }
        
        .cloz-subcat-title {
            color: #1e293b !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            margin: 0 !important;
            padding: 14px 10px !important;
            direction: rtl !important;
            text-align: center !important;
            background: #fff !important;
            border-top: 1px solid #f1f5f9 !important;
            transition: color 0.3s ease !important;
        }
        
        .cloz-subcat-card:hover .cloz-subcat-title {
            color: #0ea5e9 !important;
        }
        
        @media (max-width: 768px) {
            .cloz-category-intro-section {
                margin: 20px 0 30px 0 !important;
            }
            
            .cloz-category-intro-text {
                font-size: 14px !important;
                line-height: 1.7 !important;
                padding: 14px 16px !important;
                margin: 0 0 20px 0 !important;
            }
            
            .cloz-subcategories-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 12px !important;
            }
            
            .cloz-subcat-image-wrapper {
                height: 100px !important;
                padding: 0 !important;
            }
            
            .cloz-subcat-image {
                border-radius: 10px !important;
            }
            
            .cloz-subcat-title {
                font-size: 12px !important;
                padding: 10px 6px !important;
            }
            
            .cloz-subcat-card {
                border-radius: 14px !important;
            }
            
            .cloz-subcat-image-wrapper {
                border-radius: 14px 14px 0 0 !important;
            }
        }
    </style>
    
    <div class="cloz-category-intro-section">
        <?php if (!empty($category_intro)): ?>
            <div class="cloz-category-intro-text">
				<?php echo wpautop( wp_kses_post( $category_intro ) ); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($subcats)): ?>
            <div class="cloz-subcategories-grid">
                <?php foreach ($subcats as $subcat): ?>
                    <?php 
                    $image_url = '';
                    if (!empty($subcat['image_id'])) {
                        $image_url = wp_get_attachment_image_url($subcat['image_id'], 'medium');
                    }
                    
                    $link = !empty($subcat['link']) ? esc_url($subcat['link']) : '#';
                    $title = !empty($subcat['title']) ? esc_html($subcat['title']) : 'بدون عنوان';
                    ?>
                    
                    <a href="<?php echo $link; ?>" class="cloz-subcat-card">
                        <div class="cloz-subcat-image-wrapper">
                            <?php if ($image_url): ?>
                                <img src="<?php echo esc_url($image_url); ?>" 
                                     alt="<?php echo $title; ?>" 
                                     class="cloz-subcat-image">
                            <?php endif; ?>
                        </div>
                        <div class="cloz-subcat-title"><?php echo $title; ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}























// توضیحات طولانی دسته‌بندی
add_action('woocommerce_after_shop_loop', 'cloz_category_long_description', 20);

function cloz_category_long_description() {
    if (!is_product_category()) return;

    $term = get_queried_object();
    $content = get_field('category_long_description', 'product_cat_' . $term->term_id);

    if (!$content) return;

    // بستن divهای احتمالی
    echo '</div></div></div>';

    echo '<section class="cloz-long-desc-section">';

    echo '<style>
    .cloz-long-desc-section {
        width: 100% !important;
        max-width: 100% !important;
        margin: 60px 0 !important;
        padding: 0 20px !important;
        clear: both !important;
        float: none !important;
        position: relative !important;
        right: auto !important;
        left: auto !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }
    
    .cloz-desc-container {
        max-width: 1200px !important;
        width: 100% !important;
        margin: 0 auto !important;
        background: #fff !important;
        padding: 50px 60px !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
        line-height: 1.8 !important;
        font-size: 17px !important;
        color: #444 !important;
        clear: both !important;
        float: none !important;
        position: relative !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }
    
    .cloz-desc-container * {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    
    .cloz-desc-container h2 {
        font-size: 32px !important;
        margin: 0 0 25px 0 !important;
        line-height: 1.3 !important;
        font-weight: 700 !important;
        color: #222 !important;
        border-bottom: 3px solid #7dd3fc !important;
        padding-bottom: 15px !important;
    }
    
    .cloz-desc-container h3 {
        font-size: 26px !important;
        margin: 40px 0 20px 0 !important;
        line-height: 1.4 !important;
        font-weight: 700 !important;
        color: #333 !important;
    }
    
    .cloz-desc-container h4 {
        font-size: 22px !important;
        margin: 30px 0 15px 0 !important;
        line-height: 1.4 !important;
        font-weight: 600 !important;
        color: #444 !important;
    }
    
    .cloz-desc-container p {
        margin-bottom: 20px !important;
        text-align: justify !important;
    }
    
    .cloz-desc-container ul,
    .cloz-desc-container ol {
        margin: 25px 0 !important;
        padding-right: 30px !important;
    }
    
    .cloz-desc-container li {
        margin-bottom: 12px !important;
        line-height: 1.7 !important;
    }
    
    .cloz-desc-container strong {
        color: #222 !important;
        font-weight: 600 !important;
    }
    
    .cloz-desc-container a {
        color: #0284c7 !important;
        text-decoration: none !important;
        border-bottom: 1px solid transparent !important;
        transition: all 0.3s ease !important;
    }
    
    .cloz-desc-container a:hover {
        border-bottom-color: #0284c7 !important;
    }
    
    @media (max-width: 1024px) {
        .cloz-desc-container {
            padding: 40px 40px !important;
        }
    }
    
    @media (max-width: 768px) {
        .cloz-long-desc-section {
            margin: 30px 0 !important;
            padding: 0 10px !important;
            overflow-x: hidden !important;
        }
        
        .cloz-desc-container {
            padding: 20px 15px !important;
            font-size: 15px !important;
            line-height: 1.6 !important;
            border-radius: 4px !important;
        }
        
        .cloz-desc-container h2 {
            font-size: 20px !important;
            margin-bottom: 15px !important;
            line-height: 1.2 !important;
            padding-bottom: 10px !important;
            border-bottom-width: 2px !important;
        }
        
        .cloz-desc-container h3 {
            font-size: 18px !important;
            margin: 20px 0 12px 0 !important;
            line-height: 1.2 !important;
        }
        
        .cloz-desc-container h4 {
            font-size: 16px !important;
            margin: 18px 0 10px 0 !important;
            line-height: 1.2 !important;
        }
        
        .cloz-desc-container p {
            margin-bottom: 12px !important;
        }
        
        .cloz-desc-container ul,
        .cloz-desc-container ol {
            margin: 15px 0 !important;
            padding-right: 20px !important;
        }
        
        .cloz-desc-container li {
            margin-bottom: 8px !important;
            line-height: 1.5 !important;
        }
    }
    
    @media (max-width: 480px) {
        .cloz-long-desc-section {
            padding: 0 5px !important;
        }
        
        .cloz-desc-container {
            padding: 15px 12px !important;
            font-size: 14px !important;
        }
        
        .cloz-desc-container h2 {
            font-size: 18px !important;
        }
        
        .cloz-desc-container h3 {
            font-size: 16px !important;
        }
        
        .cloz-desc-container h4 {
            font-size: 15px !important;
        }
    }
    </style>';

    echo '<div class="cloz-desc-container">';
    echo apply_filters('the_content', $content);
    echo '</div>';

    echo '</section>';

    // بازکردن divها
    echo '<div><div><div>';
}


// سوالات متداول (زیر توضیحات)
add_action('woocommerce_after_shop_loop', 'display_category_faq_manual', 25);

function display_category_faq_manual() {
    if (!is_product_category()) return;
    
    $term = get_queried_object();
    $term_id = $term->term_id;
    
    global $wpdb;
    $faq_count = get_term_meta($term_id, 'faq_list', true);
    
    if (!$faq_count || $faq_count < 1) return;
    
    $faqs = [];
    for ($i = 0; $i < $faq_count; $i++) {
        $question = get_term_meta($term_id, "faq_list_{$i}_faq_question", true);
        $answer = get_term_meta($term_id, "faq_list_{$i}_faq_answer", true);
        
        if ($question && $answer) {
            $faqs[] = [
                'question' => $question,
                'answer' => $answer
            ];
        }
    }
    
    if (empty($faqs)) return;
    
    // بستن divها
    echo '</div></div></div>';
    
    ?>
    <section class="category-faq-section">
        <div class="faq-container">
            <h2 class="faq-title">سوالات متداول</h2>
            <div class="faq-accordion">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <span><?php echo esc_html($faq['question']); ?></span>
                            <svg class="faq-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <?php echo wpautop(wp_kses_post($faq['answer'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <style>
        .category-faq-section {
            width: 100% !important;
            max-width: 100% !important;
            margin: 40px 0 60px 0 !important;
            padding: 0 20px !important;
            clear: both !important;
            float: none !important;
            position: relative !important;
            right: auto !important;
            left: auto !important;
            overflow-x: hidden !important;
            box-sizing: border-box !important;
        }
        
        .faq-container {
            max-width: 1200px !important;
            width: 100% !important;
            margin: 0 auto !important;
            box-sizing: border-box !important;
        }
        
        .faq-title {
            font-size: 32px !important;
            font-weight: 700 !important;
            color: #222 !important;
            margin-bottom: 30px !important;
            text-align: right !important;
            border-bottom: 3px solid #7dd3fc !important;
            padding-bottom: 15px !important;
        }
        
        .faq-accordion {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            box-sizing: border-box !important;
        }
        
        .faq-item {
            background: #ffffff !important;
            border: 1px solid #e0f2fe !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            transition: all 0.3s ease !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .faq-item:hover {
            border-color: #7dd3fc !important;
            box-shadow: 0 4px 12px rgba(125, 211, 252, 0.15) !important;
        }
        
        .faq-question {
            width: 100% !important;
            padding: 20px 24px !important;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
            border: none !important;
            text-align: right !important;
            cursor: pointer !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 16px !important;
            font-size: 18px !important;
            font-weight: 600 !important;
            color: #0c4a6e !important;
            transition: all 0.3s ease !important;
            box-sizing: border-box !important;
        }
        
        .faq-question:hover {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%) !important;
        }
        
        .faq-question span {
            flex: 1 !important;
            text-align: right !important;
        }
        
        .faq-icon {
            flex-shrink: 0 !important;
            transition: transform 0.3s ease !important;
            color: #0284c7 !important;
        }
        
        .faq-item.active .faq-icon {
            transform: rotate(180deg) !important;
        }
        
        .faq-answer {
            max-height: 0 !important;
            overflow: hidden !important;
            transition: max-height 0.4s ease !important;
        }
        
        .faq-answer-content {
            padding: 0 24px 20px 24px !important;
            color: #334155 !important;
            font-size: 16px !important;
            line-height: 1.7 !important;
        }
        
        .faq-item.active .faq-answer {
            max-height: 1000px !important;
        }
        
        @media (max-width: 768px) {
            .category-faq-section {
                padding: 0 10px !important;
                margin: 30px 0 40px 0 !important;
                overflow-x: hidden !important;
            }
            
            .faq-title {
                font-size: 20px !important;
                margin-bottom: 20px !important;
                padding-bottom: 10px !important;
                border-bottom-width: 2px !important;
            }
            
            .faq-accordion {
                gap: 10px !important;
            }
            
            .faq-question {
                padding: 16px 18px !important;
                font-size: 16px !important;
            }
            
            .faq-answer-content {
                padding: 0 18px 16px 18px !important;
                font-size: 15px !important;
            }
        }
        
        @media (max-width: 480px) {
            .category-faq-section {
                padding: 0 5px !important;
            }
            
            .faq-title {
                font-size: 18px !important;
            }
            
            .faq-question {
                padding: 14px 15px !important;
                font-size: 15px !important;
            }
            
            .faq-answer-content {
                padding: 0 15px 14px 15px !important;
                font-size: 14px !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', function() {
                    const isActive = item.classList.contains('active');
                    
                    faqItems.forEach(i => {
                        i.classList.remove('active');
                        i.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
                    });
                    
                    if (!isActive) {
                        item.classList.add('active');
                        question.setAttribute('aria-expanded', 'true');
                    }
                });
            });
        });
    </script>
    <?php
    
    // بازکردن divها
    echo '<div><div><div>';
}



























add_action('woocommerce_after_shop_loop', 'display_category_related_posts', 30);
function display_category_related_posts() {
    if (!is_product_category()) return;
    
    $term = get_queried_object();
    $term_id = $term->term_id;
    
    $related_post_ids = get_field('related_posts', 'product_cat_' . $term_id);
    
    if (empty($related_post_ids) || !is_array($related_post_ids)) return;
    
    echo '<div class="cloz-related-posts-wrapper">';
    echo '<h2 class="cloz-related-posts-title">مطالب مرتبط</h2>';
    echo '<div class="cloz-related-posts-grid">';
    
    foreach ($related_post_ids as $post_id) {
        $post = get_post($post_id);
        if (!$post) continue;
        
        $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
        
        $excerpt = get_post_meta($post_id, 'rank_math_description', true);
        if (empty($excerpt)) {
            $excerpt = wp_trim_words($post->post_content, 20, '...');
        }
        
        $date = get_the_date('j F Y', $post_id);
        $permalink = get_permalink($post_id);
        
        echo '<article class="cloz-related-post-card">';
        if ($thumbnail) {
            echo '<a href="' . esc_url($permalink) . '" class="cloz-post-thumbnail">';
            echo '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($post->post_title) . '">';
            echo '</a>';
        }
        echo '<div class="cloz-post-content">';
        echo '<h3 class="cloz-post-title"><a href="' . esc_url($permalink) . '">' . esc_html($post->post_title) . '</a></h3>';
        echo '<p class="cloz-post-excerpt">' . esc_html($excerpt) . '</p>';
        echo '<div class="cloz-post-meta">';
        echo '<span class="cloz-post-date">📅 ' . esc_html($date) . '</span>';
        echo '<a href="' . esc_url($permalink) . '" class="cloz-post-readmore">مشاهده مطلب</a>';
        echo '</div>';
        echo '</div>';
        echo '</article>';
    }
    
    echo '</div>';
    echo '</div>';
}

add_action('wp_head', 'cloz_related_posts_styles');
function cloz_related_posts_styles() {
    if (!is_product_category()) return;
    ?>
    <style>
    .cloz-related-posts-wrapper {
        max-width: 1200px !important;
        margin: 50px auto !important;
        padding: 0 20px !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }
    .cloz-related-posts-title {
        font-size: 32px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        margin-bottom: 35px !important;
        text-align: right !important;
        padding-bottom: 20px !important;
        border-bottom: 4px solid transparent !important;
        background: linear-gradient(to left, #0ea5e9, #06b6d4) !important;
        background-clip: text !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        position: relative !important;
    }
    .cloz-related-posts-title::after {
        content: '' !important;
        position: absolute !important;
        bottom: 0 !important;
        right: 0 !important;
        width: 120px !important;
        height: 4px !important;
        background: linear-gradient(to left, #0ea5e9, #06b6d4) !important;
        border-radius: 2px !important;
    }
    .cloz-related-posts-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
        gap: 30px !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .cloz-related-post-card {
        background: #ffffff !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: flex !important;
        flex-direction: column !important;
        border: 1px solid #f1f5f9 !important;
    }
    .cloz-related-post-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 20px 40px rgba(14,165,233,0.2) !important;
        border-color: #e0f2fe !important;
    }
    .cloz-post-thumbnail {
        display: block !important;
        width: 100% !important;
        height: 220px !important;
        overflow: hidden !important;
        position: relative !important;
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%) !important;
    }
    .cloz-post-thumbnail::after {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.1) 100%) !important;
        opacity: 0 !important;
        transition: opacity 0.3s ease !important;
    }
    .cloz-related-post-card:hover .cloz-post-thumbnail::after {
        opacity: 1 !important;
    }
    .cloz-post-thumbnail img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .cloz-related-post-card:hover .cloz-post-thumbnail img {
        transform: scale(1.08) !important;
    }
    .cloz-post-content {
        padding: 25px !important;
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        background: #ffffff !important;
    }
    .cloz-post-title {
        font-size: 20px !important;
        font-weight: 700 !important;
        margin: 0 0 15px 0 !important;
        line-height: 1.5 !important;
        text-align: right !important;
    }
    .cloz-post-title a {
        color: #0f172a !important;
        text-decoration: none !important;
        transition: color 0.3s ease !important;
        display: block !important;
    }
    .cloz-post-title a:hover {
        color: #0ea5e9 !important;
    }
    .cloz-post-excerpt {
        color: #64748b !important;
        font-size: 15px !important;
        line-height: 1.7 !important;
        margin: 0 0 20px 0 !important;
        flex: 1 !important;
        text-align: right !important;
    }
    .cloz-post-meta {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding-top: 20px !important;
        border-top: 1px solid #f1f5f9 !important;
        direction: rtl !important;
    }
    .cloz-post-date {
        color: #94a3b8 !important;
        font-size: 14px !important;
        font-weight: 500 !important;
    }
    .cloz-post-readmore {
        color: #ffffff !important;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%) !important;
        padding: 8px 20px !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 8px rgba(14,165,233,0.3) !important;
    }
    .cloz-post-readmore:hover {
        background: linear-gradient(135deg, #0284c7 0%, #0891b2 100%) !important;
        box-shadow: 0 4px 12px rgba(14,165,233,0.4) !important;
        transform: translateY(-2px) !important;
    }
    @media (max-width: 768px) {
        .cloz-related-posts-wrapper {
            margin: 30px auto !important;
        }
        .cloz-related-posts-title {
            font-size: 26px !important;
        }
        .cloz-related-posts-grid {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }
        .cloz-post-thumbnail {
            height: 200px !important;
        }
    }
    </style>
    <?php
}
