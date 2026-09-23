<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Steps — numbered process cards. Covers "Bring Your Brand to Life in Four Simple Steps".
 */
class BAC_Steps_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_steps'; }
    public function get_title() { return 'BAC Steps'; }
    public function get_icon() { return 'eicon-number-field'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['steps', 'process', 'how it works', 'bac']; }

    protected function register_controls() {

        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', ['label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'How It Works']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => 'Bring Your Brand to Life in Four Simple Steps']);
        $this->add_control('subtext', ['label' => 'Sub text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => 'From your first idea to final delivery, our team makes it simple to create, produce, and manage branded apparel for your business.']);
        $this->add_control('columns', [
            'label' => 'Columns', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'], 'default' => '4',
        ]);
        bac_add_ratio_control($this, '16 / 9');
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();

        $this->start_controls_section('items', ['label' => 'Steps']);
        $rep = new \Elementor\Repeater();
        $rep->add_control('image', ['label' => 'Image (optional)', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true]]);
        $rep->add_control('title', ['label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Become a Member']);
        $rep->add_control('text', ['label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => 'Choose the membership tier that fits your business and unlock access to products, support, services, and member benefits.']);
        $this->add_control('steps', [
            'label' => 'Steps', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rep->get_controls(), 'title_field' => '{{{ title }}}',
            'default' => [
                ['title' => 'Become a Member', 'text' => 'Choose the membership tier that fits your business and unlock access to products, support, and member benefits.'],
                ['title' => 'Create Your Project', 'text' => 'Work one-on-one with our team to build your custom apparel and product guidance, DIY or Done-For-You.'],
                ['title' => 'We Produce & Fulfill', 'text' => 'Once your project is approved, our production partners decorate, prepare, and fulfill your order.'],
                ['title' => 'Delivered to Your Door', 'text' => 'Your completed order is carefully packaged and delivered to your business, team, customers, or event.'],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $cols  = ! empty($s['columns']) ? (int) $s['columns'] : 4;
        $ratio = bac_ratio_value($s, '16 / 9');
        $fit   = bac_fit_value($s);
        ?>
        <section class="bac-block bac-steps <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-steps-inner">
            <div class="bac-steps-head">
              <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
              <?php if (! empty($s['heading'])) : ?><h2 class="bac-section-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
              <?php if (! empty($s['subtext'])) : ?><p class="bac-section-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
            </div>
            <div class="bac-steps-grid" style="--bac-cols: <?php echo esc_attr($cols); ?>; --bac-img-ratio: <?php echo esc_attr($ratio); ?>; --bac-img-fit: <?php echo esc_attr($fit); ?>;">
              <?php $n = 1; foreach ((array) $s['steps'] as $step) : ?>
                <div class="bac-step">
                  <div class="bac-step-media">
                    <?php if (! empty($step['image']['url'])) : ?>
                      <?php echo bac_img_tag($step['image']['url'], $step['title']); ?>
                    <?php else : ?>
                      <span class="bac-media-placeholder small"></span>
                    <?php endif; ?>
                    <span class="bac-step-num"><?php echo esc_html('Step ' . $n++); ?></span>
                  </div>
                  <h3 class="bac-step-title"><?php echo esc_html($step['title']); ?></h3>
                  <p class="bac-step-text"><?php echo esc_html($step['text']); ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php
    }
}
