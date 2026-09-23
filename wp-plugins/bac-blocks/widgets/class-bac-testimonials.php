<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Testimonials — quote cards with star rating and author.
 */
class BAC_Testimonials_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_testimonials'; }
    public function get_title() { return 'BAC Testimonials'; }
    public function get_icon() { return 'eicon-testimonial'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['testimonials', 'reviews', 'quotes', 'bac']; }

    protected function register_controls() {
        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', ['label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Member Stories']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'What Members Are Saying']);
        $this->add_control('columns', ['label' => 'Columns', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4'], 'default' => '3']);
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();

        $this->start_controls_section('items_section', ['label' => 'Testimonials']);
        $rep = new \Elementor\Repeater();
        $rep->add_control('quote', ['label' => 'Quote', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => 'The pricing alone paid for my membership in the first order.']);
        $rep->add_control('name', ['label' => 'Name', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Marcus T.']);
        $rep->add_control('role', ['label' => 'Role / company', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => "Owner, Marcus's Auto Detail"]);
        $rep->add_control('rating', ['label' => 'Stars', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => ['5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1', '0' => 'None'], 'default' => '5']);
        $rep->add_control('avatar', ['label' => 'Avatar (optional)', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true]]);
        $this->add_control('items', [
            'label' => 'Testimonials', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rep->get_controls(), 'title_field' => '{{{ name }}}',
            'default' => [
                ['quote' => 'The pricing alone paid for my membership in the first order. Ordering for my crew keeps getting easier.', 'name' => 'Marcus T.', 'role' => "Owner, Marcus's Auto Detail", 'rating' => '5'],
                ['quote' => 'We needed uniforms fast before a big event. They turned around 30 polos in 48 hours. Incredible quality.', 'name' => 'Destiny R.', 'role' => 'Owner, Radiance Salon & Spa', 'rating' => '5'],
                ['quote' => 'I used to dread ordering merch. Now I log in, pick what I need, and it shows up. Five minutes.', 'name' => 'Jamal W.', 'role' => 'Co-Founder, Uplift Fitness', 'rating' => '5'],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $cols = ! empty($s['columns']) ? (int) $s['columns'] : 3;
        ?>
        <section class="bac-block bac-tst <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-tst-inner">
            <?php if (! empty($s['eyebrow']) || ! empty($s['heading'])) : ?>
              <div class="bac-tst-head">
                <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
                <?php if (! empty($s['heading'])) : ?><h2 class="bac-section-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
              </div>
            <?php endif; ?>
            <div class="bac-tst-grid" style="--bac-cols: <?php echo esc_attr($cols); ?>;">
              <?php foreach ((array) $s['items'] as $t) :
                  $stars = isset($t['rating']) ? (int) $t['rating'] : 5;
              ?>
                <figure class="bac-tst-card">
                  <?php if ($stars > 0) : ?>
                    <div class="bac-tst-stars" aria-label="<?php echo esc_attr($stars . ' out of 5 stars'); ?>">
                      <?php for ($i = 0; $i < $stars; $i++) : ?><svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/></svg><?php endfor; ?>
                    </div>
                  <?php endif; ?>
                  <blockquote class="bac-tst-quote"><?php echo esc_html($t['quote']); ?></blockquote>
                  <figcaption class="bac-tst-author">
                    <?php if (! empty($t['avatar']['url'])) : ?>
                      <span class="bac-tst-avatar"><?php echo bac_img_tag($t['avatar']['url'], $t['name']); ?></span>
                    <?php endif; ?>
                    <span class="bac-tst-meta">
                      <strong><?php echo esc_html($t['name']); ?></strong>
                      <?php if (! empty($t['role'])) : ?><span><?php echo esc_html($t['role']); ?></span><?php endif; ?>
                    </span>
                  </figcaption>
                </figure>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php
    }
}
