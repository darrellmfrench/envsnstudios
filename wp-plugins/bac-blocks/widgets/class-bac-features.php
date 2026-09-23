<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Icon feature grid — icon + title + text tiles (benefits/perks).
 */
class BAC_Features_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_features'; }
    public function get_title() { return 'BAC Icon Feature Grid'; }
    public function get_icon() { return 'eicon-icon-box'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['features', 'icons', 'benefits', 'perks', 'bac']; }

    protected function register_controls() {
        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', ['label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Why Members Choose Us']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Everything You Need, In One Membership']);
        $this->add_control('subtext', ['label' => 'Sub text (optional)', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('columns', ['label' => 'Columns', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'], 'default' => '4']);
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();

        $this->start_controls_section('items', ['label' => 'Features']);
        $rep = new \Elementor\Repeater();
        $rep->add_control('icon', ['label' => 'Icon', 'type' => \Elementor\Controls_Manager::ICONS, 'default' => ['value' => 'fas fa-star', 'library' => 'fa-solid']]);
        $rep->add_control('title', ['label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Member-Only Pricing']);
        $rep->add_control('text', ['label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => 'Up to 40% below retail on premium blanks and full custom orders.']);
        $this->add_control('features', [
            'label' => 'Features', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rep->get_controls(), 'title_field' => '{{{ title }}}',
            'default' => [
                ['icon' => ['value' => 'fas fa-percent', 'library' => 'fa-solid'], 'title' => 'Member-Only Pricing', 'text' => 'Up to 40% below retail on premium blanks and full custom orders.'],
                ['icon' => ['value' => 'fas fa-bolt', 'library' => 'fa-solid'], 'title' => 'Fast Turnaround', 'text' => 'Most orders ship within 48 to 72 hours. Rush options available.'],
                ['icon' => ['value' => 'fas fa-palette', 'library' => 'fa-solid'], 'title' => 'Design Support', 'text' => 'Our team prepares your logo for production-ready print files.'],
                ['icon' => ['value' => 'fas fa-shield-halved', 'library' => 'fa-solid'], 'title' => 'Quality Guarantee', 'text' => 'Every order inspected before it ships. Issues? We make it right.'],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $cols = ! empty($s['columns']) ? (int) $s['columns'] : 4;
        ?>
        <section class="bac-block bac-features <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-features-inner">
            <?php if (! empty($s['eyebrow']) || ! empty($s['heading']) || ! empty($s['subtext'])) : ?>
              <div class="bac-features-head">
                <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
                <?php if (! empty($s['heading'])) : ?><h2 class="bac-section-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
                <?php if (! empty($s['subtext'])) : ?><p class="bac-section-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
              </div>
            <?php endif; ?>
            <div class="bac-features-grid" style="--bac-cols: <?php echo esc_attr($cols); ?>;">
              <?php foreach ((array) $s['features'] as $f) : ?>
                <div class="bac-feature">
                  <span class="bac-feature-icon"><?php \Elementor\Icons_Manager::render_icon($f['icon'], ['aria-hidden' => 'true']); ?></span>
                  <h3 class="bac-feature-title"><?php echo esc_html($f['title']); ?></h3>
                  <p class="bac-feature-text"><?php echo esc_html($f['text']); ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php
    }
}
