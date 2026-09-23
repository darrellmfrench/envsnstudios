<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Stats bar — navy band with a repeatable row of icon + number + label.
 * Client adds/removes/edits items in the repeater.
 */
class BAC_Stats_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_stats'; }
    public function get_title() { return 'BAC Stats Bar'; }
    public function get_icon() { return 'eicon-counter'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['stats', 'counter', 'bac']; }

    protected function register_controls() {

        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', [
            'label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Our Standard',
        ]);
        $this->add_control('heading', [
            'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Built Around Quality, Service & Trust',
        ]);
        bac_add_theme_control($this, 'dark');
        $this->end_controls_section();

        $this->start_controls_section('items', ['label' => 'Stats']);

        $rep = new \Elementor\Repeater();
        $rep->add_control('icon', [
            'label' => 'Icon',
            'type'  => \Elementor\Controls_Manager::ICONS,
            'default' => ['value' => 'fas fa-check', 'library' => 'fa-solid'],
        ]);
        $rep->add_control('number', [
            'label' => 'Number / stat', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => '10K+',
        ]);
        $rep->add_control('label', [
            'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Businesses Served',
        ]);

        $this->add_control('stats', [
            'label'       => 'Items',
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $rep->get_controls(),
            'title_field' => '{{{ number }}} — {{{ label }}}',
            'default'     => [
                ['icon' => ['value' => 'fas fa-users', 'library' => 'fa-solid'], 'number' => '10K+', 'label' => 'Businesses Served'],
                ['icon' => ['value' => 'fas fa-star', 'library' => 'fa-solid'], 'number' => 'Premium', 'label' => 'Apparel Brands'],
                ['icon' => ['value' => 'fas fa-bolt', 'library' => 'fa-solid'], 'number' => 'Fast', 'label' => 'Turnaround'],
                ['icon' => ['value' => 'fas fa-truck', 'library' => 'fa-solid'], 'number' => 'Nationwide', 'label' => 'Shipping'],
                ['icon' => ['value' => 'fas fa-user-shield', 'library' => 'fa-solid'], 'number' => 'Member-Focused', 'label' => 'Service'],
            ],
        ]);

        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        ?>
        <section class="bac-block bac-stats <?php echo esc_attr(bac_theme_class($s, 'dark')); ?>">
          <div class="bac-stats-inner">
            <?php if (! empty($s['eyebrow']) || ! empty($s['heading'])) : ?>
              <div class="bac-stats-head">
                <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow light"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
                <?php if (! empty($s['heading'])) : ?><h2 class="bac-stats-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="bac-stats-grid">
              <?php foreach ((array) $s['stats'] as $item) : ?>
                <div class="bac-stat">
                  <span class="bac-stat-icon"><?php \Elementor\Icons_Manager::render_icon($item['icon'], ['aria-hidden' => 'true']); ?></span>
                  <span class="bac-stat-num"><?php echo esc_html($item['number']); ?></span>
                  <span class="bac-stat-label"><?php echo esc_html($item['label']); ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php
    }
}
