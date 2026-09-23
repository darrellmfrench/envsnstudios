<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Latest Posts — pulls WordPress posts in the Card Grid look.
 * Columns 2–6, image aspect-ratio/fit, and a category filter that defaults to
 * ALL with a multi-select to limit to one or more categories.
 *
 * Keeps the widget name "bac_posts" so existing instances never break.
 */
class BAC_Posts_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_posts'; }
    public function get_title() { return 'BAC Latest Posts'; }
    public function get_icon() { return 'eicon-posts-grid'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['posts', 'blog', 'news', 'latest', 'cards', 'grid', 'bac']; }

    /** term_id => name list of post categories for the multi-select. */
    private function category_options() {
        $opts = [];
        $cats = get_categories(['hide_empty' => false]);
        if (! empty($cats)) {
            foreach ($cats as $c) { $opts[$c->term_id] = $c->name; }
        }
        return $opts;
    }

    protected function register_controls() {

        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', [
            'label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'From the Blog',
        ]);
        $this->add_control('heading', [
            'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => "What's New",
        ]);
        $this->add_control('subtext', [
            'label' => 'Sub text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => '',
        ]);
        $this->add_control('columns', [
            'label' => 'Columns', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'], 'default' => '3',
        ]);
        bac_add_ratio_control($this, '16 / 9');
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();

        $this->start_controls_section('query', ['label' => 'Posts']);
        $this->add_control('count', [
            'label' => 'Number of posts', 'type' => \Elementor\Controls_Manager::NUMBER,
            'min' => 1, 'max' => 24, 'default' => 3,
        ]);
        $this->add_control('categories', [
            'label'       => 'Categories',
            'type'        => \Elementor\Controls_Manager::SELECT2,
            'multiple'    => true,
            'label_block' => true,
            'options'     => $this->category_options(),
            'default'     => [],
            'description' => 'Leave empty to show ALL categories. Pick one or more to limit the posts.',
        ]);
        $this->add_control('orderby', [
            'label' => 'Order by', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['date' => 'Newest first', 'title' => 'Title (A–Z)', 'rand' => 'Random', 'menu_order' => 'Menu order', 'comment_count' => 'Most commented'],
            'default' => 'date',
        ]);
        $this->add_control('show_date', ['label' => 'Show date', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes']);
        $this->add_control('show_cat', ['label' => 'Show category tag', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes']);
        $this->add_control('show_excerpt', ['label' => 'Show excerpt', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes']);
        $this->add_control('excerpt_words', [
            'label' => 'Excerpt length (words)', 'type' => \Elementor\Controls_Manager::NUMBER,
            'min' => 5, 'max' => 60, 'default' => 22, 'condition' => ['show_excerpt' => 'yes'],
        ]);
        $this->add_control('show_more', ['label' => 'Show "Read more" link', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes']);
        $this->add_control('more_label', [
            'label' => '"Read more" text', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Read more', 'condition' => ['show_more' => 'yes'],
        ]);
        $this->add_control('empty_text', [
            'label' => 'Empty message', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'No posts found.',
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s     = $this->get_settings_for_display();
        $cols  = ! empty($s['columns']) ? (int) $s['columns'] : 3;
        $ratio = bac_ratio_value($s, '16 / 9');
        $fit   = bac_fit_value($s);
        $words = ! empty($s['excerpt_words']) ? (int) $s['excerpt_words'] : 22;

        $args = [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => ! empty($s['count']) ? (int) $s['count'] : 3,
            'ignore_sticky_posts' => true,
            'orderby'             => ! empty($s['orderby']) ? $s['orderby'] : 'date',
        ];
        if ($args['orderby'] === 'title' || $args['orderby'] === 'menu_order') {
            $args['order'] = 'ASC';
        }
        // New multi-select categories -> category__in. Empty = all.
        $cats = array_filter(array_map('intval', (array) ($s['categories'] ?? [])));
        // Back-compat: honor the old single "category" control if it was set.
        if (empty($cats) && ! empty($s['category'])) {
            $cats = [(int) $s['category']];
        }
        if (! empty($cats)) { $args['category__in'] = $cats; }

        $q = new WP_Query($args);
        ?>
        <section class="bac-block bac-cards bac-blog bac-posts <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-cards-inner">
            <?php if (! empty($s['eyebrow']) || ! empty($s['heading']) || ! empty($s['subtext'])) : ?>
              <div class="bac-cards-head">
                <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
                <?php if (! empty($s['heading'])) : ?><h2 class="bac-section-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
                <?php if (! empty($s['subtext'])) : ?><p class="bac-section-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if ($q->have_posts()) : ?>
              <div class="bac-cards-grid" style="--bac-cols: <?php echo esc_attr($cols); ?>; --bac-img-ratio: <?php echo esc_attr($ratio); ?>; --bac-img-fit: <?php echo esc_attr($fit); ?>;">
                <?php while ($q->have_posts()) : $q->the_post(); ?>
                  <article class="bac-card bac-blog-card">
                    <a class="bac-card-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                      <?php if (has_post_thumbnail()) : ?>
                        <?php echo bac_img_tag(get_the_post_thumbnail_url(get_the_ID(), 'medium_large'), get_the_title()); ?>
                      <?php else : ?>
                        <span class="bac-media-placeholder small"></span>
                      <?php endif; ?>
                    </a>
                    <div class="bac-blog-body">
                      <div class="bac-blog-meta">
                        <?php if (! empty($s['show_cat']) && $s['show_cat'] === 'yes') :
                            $cat_list = get_the_category();
                            if (! empty($cat_list)) : ?>
                              <span class="bac-blog-cat"><?php echo esc_html($cat_list[0]->name); ?></span>
                        <?php endif; endif; ?>
                        <?php if (! empty($s['show_date']) && $s['show_date'] === 'yes') : ?>
                          <time class="bac-blog-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                        <?php endif; ?>
                      </div>
                      <h3 class="bac-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                      <?php if (! empty($s['show_excerpt']) && $s['show_excerpt'] === 'yes') : ?>
                        <p class="bac-card-text"><?php echo esc_html(wp_trim_words(get_the_excerpt(), $words)); ?></p>
                      <?php endif; ?>
                      <?php if (! empty($s['show_more']) && $s['show_more'] === 'yes') : ?>
                        <a class="bac-blog-more" href="<?php the_permalink(); ?>"><?php echo esc_html($s['more_label'] ?: 'Read more'); ?> <span aria-hidden="true">&rarr;</span><span class="bac-sr-only"> — <?php echo esc_html(get_the_title()); ?></span></a>
                      <?php endif; ?>
                    </div>
                  </article>
                <?php endwhile; ?>
              </div>
            <?php else : ?>
              <p class="bac-cards-empty"><?php echo esc_html($s['empty_text'] ?: 'No posts found.'); ?></p>
            <?php endif; wp_reset_postdata(); ?>
          </div>
        </section>
        <?php
    }
}
