<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Trust / guarantee bar — a row of icon + label reassurances.
 */
class BAC_Trust_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_trust'; }
    public function get_title() { return 'BAC Trust Bar'; }
    public function get_icon() { return 'eicon-check-circle'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['trust', 'guarantee', 'badges', 'bac']; }

    protected function register_controls() {
        $this->start_controls_section('items_section', ['label' => 'Trust items']);
        $rep = new \Elementor\Repeater();
        $rep->add_control('icon', ['label' => 'Icon', 'type' => \Elementor\Controls_Manager::ICONS, 'default' => ['value' => 'fas fa-shield-halved', 'library' => 'fa-solid']]);
        $rep->add_control('label', ['label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Quality Guarantee']);
        $rep->add_control('sub', ['label' => 'Sub label (optional)', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('items', [
            'label' => 'Items', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rep->get_controls(), 'title_field' => '{{{ label }}}',
            'default' => [
                ['icon' => ['value' => 'fas fa-shield-halved', 'library' => 'fa-solid'], 'label' => 'Quality Guarantee', 'sub' => 'Every order inspected'],
                ['icon' => ['value' => 'fas fa-truck-fast', 'library' => 'fa-solid'], 'label' => 'Fast Turnaround', 'sub' => '48 to 72 hours'],
                ['icon' => ['value' => 'fas fa-lock', 'library' => 'fa-solid'], 'label' => 'Secure Checkout', 'sub' => 'Trusted payments'],
                ['icon' => ['value' => 'fas fa-headset', 'library' => 'fa-solid'], 'label' => 'Real Support', 'sub' => 'Talk to a human'],
            ],
        ]);
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        ?>
        <section class="bac-block bac-trust <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-trust-inner">
            <?php foreach ((array) $s['items'] as $it) : ?>
              <div class="bac-trust-item">
                <span class="bac-trust-icon"><?php \Elementor\Icons_Manager::render_icon($it['icon'], ['aria-hidden' => 'true']); ?></span>
                <span class="bac-trust-text">
                  <strong><?php echo esc_html($it['label']); ?></strong>
                  <?php if (! empty($it['sub'])) : ?><span><?php echo esc_html($it['sub']); ?></span><?php endif; ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php
    }
}
