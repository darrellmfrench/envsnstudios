<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Industries grid — navy band with a grid of icon + label tiles.
 * Covers "Built For Businesses Of Every Type".
 */
class BAC_Industries_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_industries'; }
    public function get_title() { return 'BAC Industries Grid'; }
    public function get_icon() { return 'eicon-gallery-grid'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['industries', 'grid', 'bac']; }

    protected function register_controls() {

        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', ['label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Industries']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Built For Businesses Of Every Type']);
        $this->add_control('subtext', ['label' => 'Sub text (optional)', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('cta_label', ['label' => 'Button text (optional)', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('cta_url', ['label' => 'Button link', 'type' => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true], 'default' => ['url' => '']]);
        bac_add_theme_control($this, 'dark');
        $this->end_controls_section();

        $this->start_controls_section('items', ['label' => 'Industries']);
        $rep = new \Elementor\Repeater();
        $rep->add_control('icon', ['label' => 'Icon', 'type' => \Elementor\Controls_Manager::ICONS, 'default' => ['value' => 'fas fa-briefcase', 'library' => 'fa-solid']]);
        $rep->add_control('label', ['label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Corporate']);
        $this->add_control('inds', [
            'label' => 'Items', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rep->get_controls(), 'title_field' => '{{{ label }}}',
            'default' => [
                ['icon' => ['value' => 'fas fa-briefcase', 'library' => 'fa-solid'], 'label' => 'Corporate'],
                ['icon' => ['value' => 'fas fa-utensils', 'library' => 'fa-solid'], 'label' => 'Restaurants'],
                ['icon' => ['value' => 'fas fa-martini-glass', 'library' => 'fa-solid'], 'label' => 'Bars & Nightlife'],
                ['icon' => ['value' => 'fas fa-house', 'library' => 'fa-solid'], 'label' => 'Home Services'],
                ['icon' => ['value' => 'fas fa-building', 'library' => 'fa-solid'], 'label' => 'Real Estate'],
                ['icon' => ['value' => 'fas fa-helmet-safety', 'library' => 'fa-solid'], 'label' => 'Construction'],
                ['icon' => ['value' => 'fas fa-basketball', 'library' => 'fa-solid'], 'label' => 'Sports & Entertainment'],
                ['icon' => ['value' => 'fas fa-heart-pulse', 'library' => 'fa-solid'], 'label' => 'Fitness & Healthcare'],
                ['icon' => ['value' => 'fas fa-hands-holding-circle', 'library' => 'fa-solid'], 'label' => 'Nonprofits & Churches'],
                ['icon' => ['value' => 'fas fa-bullhorn', 'library' => 'fa-solid'], 'label' => 'Agencies'],
                ['icon' => ['value' => 'fas fa-store', 'library' => 'fa-solid'], 'label' => 'Small Business'],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        ?>
        <section class="bac-block bac-inds <?php echo esc_attr(bac_theme_class($s, 'dark')); ?>">
          <div class="bac-inds-inner">
            <div class="bac-inds-head">
              <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow light"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
              <?php if (! empty($s['heading'])) : ?><h2 class="bac-inds-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
              <?php if (! empty($s['subtext'])) : ?><p class="bac-inds-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
            </div>
            <div class="bac-inds-grid">
              <?php foreach ((array) $s['inds'] as $ind) : ?>
                <div class="bac-ind">
                  <span class="bac-ind-icon"><?php \Elementor\Icons_Manager::render_icon($ind['icon'], ['aria-hidden' => 'true']); ?></span>
                  <span class="bac-ind-label"><?php echo esc_html($ind['label']); ?></span>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if (! empty($s['cta_label'])) : ?>
              <div class="bac-inds-cta"><a class="bac-btn bac-btn-primary" href="<?php echo esc_url($s['cta_url']['url'] ?: '#'); ?>"><?php echo esc_html($s['cta_label']); ?></a></div>
            <?php endif; ?>
          </div>
        </section>
        <?php
    }
}
