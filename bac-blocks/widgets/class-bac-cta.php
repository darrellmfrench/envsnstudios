<?php
if (! defined('ABSPATH')) { exit; }

/**
 * CTA banner — dark, centered heading + subtext + two buttons, optional bg image.
 * Covers "Ready to Move Your Business Forward?".
 */
class BAC_Cta_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_cta'; }
    public function get_title() { return 'BAC CTA Banner'; }
    public function get_icon() { return 'eicon-call-to-action'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['cta', 'banner', 'bac']; }

    protected function register_controls() {

        $this->start_controls_section('content', ['label' => 'Content']);
        $this->add_control('eyebrow', ['label' => 'Small label (eyebrow, optional)', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => 'Ready to Move Your Business Forward?']);
        $this->add_control('subtext', ['label' => 'Sub text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => 'Become a member today and discover a better way to plan, purchase, and manage branded apparel and merchandise for your business.']);
        $this->add_control('bg_image', ['label' => 'Background image (optional)', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true]]);
        bac_add_theme_control($this, 'dark');
        $this->end_controls_section();

        $this->start_controls_section('buttons', ['label' => 'Buttons']);
        $this->add_control('btn1_label', ['label' => 'Primary button text', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Become a Member']);
        $this->add_control('btn1_url', ['label' => 'Primary button link', 'type' => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true], 'default' => ['url' => '/join/']]);
        $this->add_control('btn2_label', ['label' => 'Secondary button text', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Schedule a Consultation']);
        $this->add_control('btn2_url', ['label' => 'Secondary button link', 'type' => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true], 'default' => ['url' => '#']]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $bg = ! empty($s['bg_image']['url']) ? ' style="background-image:url(' . esc_url($s['bg_image']['url']) . ');"' : '';
        ?>
        <section class="bac-block bac-cta <?php echo esc_attr(bac_theme_class($s, 'dark')); ?><?php echo ! empty($s['bg_image']['url']) ? ' has-bg' : ''; ?>"<?php echo $bg; ?>>
          <div class="bac-cta-inner">
            <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
            <?php if (! empty($s['heading'])) : ?><h2 class="bac-cta-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
            <?php if (! empty($s['subtext'])) : ?><p class="bac-cta-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
            <div class="bac-cta-actions">
              <?php if (! empty($s['btn1_label'])) : ?>
                <a class="bac-btn bac-btn-primary" href="<?php echo esc_url($s['btn1_url']['url'] ?: '#'); ?>"><?php echo esc_html($s['btn1_label']); ?></a>
              <?php endif; ?>
              <?php if (! empty($s['btn2_label'])) : ?>
                <a class="bac-btn bac-btn-outline-white" href="<?php echo esc_url($s['btn2_url']['url'] ?: '#'); ?>"><?php echo esc_html($s['btn2_label']); ?></a>
              <?php endif; ?>
            </div>
          </div>
        </section>
        <?php
    }
}
