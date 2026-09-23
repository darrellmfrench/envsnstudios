<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Logo wall — a static grid OR an auto-scrolling marquee of brand/partner logos.
 * Covers the dark brands strip, the light "Membership Has Its Perks" grid, and
 * the scrolling logo ribbon (with editable speed).
 */
class BAC_Logos_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_logos'; }
    public function get_title() { return 'BAC Logo Wall'; }
    public function get_icon() { return 'eicon-logo'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['logos', 'brands', 'partners', 'marquee', 'scroll', 'bac']; }

    protected function register_controls() {

        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', ['label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Brands']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Access The Brands You Know & Trust']);
        $this->add_control('theme', [
            'label' => 'Background', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['dark' => 'Dark (navy)', 'light' => 'Light', 'gray' => 'Gray'], 'default' => 'dark',
        ]);
        $this->add_control('style', [
            'label' => 'Item style', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['text' => 'Text names', 'boxes' => 'Bordered boxes'], 'default' => 'text',
            'description' => 'Text = names/logos in a row. Boxes = bordered tiles. Ignored when Scrolling marquee is on.',
        ]);
        $this->add_control('logo_recolor', [
            'label' => 'Recolor logo images', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['none' => 'Keep original', 'white' => 'White', 'dark' => 'Dark'], 'default' => 'none',
            'description' => 'Set White for black logo PNGs on the navy background.',
        ]);
        $this->add_control('subtext', ['label' => 'Sub text (optional)', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('cta_label', ['label' => 'Button text (optional)', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('cta_url', ['label' => 'Button link', 'type' => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true], 'default' => ['url' => '']]);
        $this->end_controls_section();

        $this->start_controls_section('motion', ['label' => 'Scrolling (marquee)']);
        $this->add_control('scroll', [
            'label' => 'Scroll logos (marquee)', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => 'On', 'label_off' => 'Off', 'return_value' => 'yes', 'default' => '',
        ]);
        $this->add_control('scroll_speed', [
            'label' => 'Scroll duration (seconds)', 'type' => \Elementor\Controls_Manager::NUMBER,
            'min' => 5, 'max' => 200, 'step' => 1, 'default' => 40,
            'condition' => ['scroll' => 'yes'],
            'description' => 'Time for one full loop. Lower number = faster.',
        ]);
        $this->add_control('scroll_pause', [
            'label' => 'Pause on hover', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes', 'default' => 'yes', 'condition' => ['scroll' => 'yes'],
        ]);
        $this->end_controls_section();

        $this->start_controls_section('items', ['label' => 'Logos']);
        $rep = new \Elementor\Repeater();
        $rep->add_control('name', ['label' => 'Name', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Brand']);
        $rep->add_control('image', ['label' => 'Logo image (optional)', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true]]);
        $rep->add_control('aria_hidden', [
            'label' => 'Hide from screen readers', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => 'Yes', 'label_off' => 'No', 'return_value' => 'yes', 'default' => '',
            'description' => 'On adds aria-hidden="true" (decorative). The "name" is still used as the alt text when off.',
        ]);
        $this->add_control('logos', [
            'label' => 'Items', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rep->get_controls(), 'title_field' => '{{{ name }}}',
            'default' => [
                ['name' => 'Nike'], ['name' => 'Carhartt'], ['name' => 'adidas'],
                ['name' => 'BELLA+CANVAS'], ['name' => 'New Era'], ['name' => 'Richardson'],
                ['name' => 'TravisMathew'], ['name' => 'The North Face'],
                ['name' => 'Port Authority'], ['name' => 'Sport-Tek'],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $theme   = (isset($s['theme']) && in_array($s['theme'], ['light', 'gray'], true)) ? $s['theme'] : 'dark';
        $style   = (isset($s['style']) && $s['style'] === 'boxes') ? 'boxes' : 'text';
        $recolor = (isset($s['logo_recolor']) && in_array($s['logo_recolor'], ['white', 'dark'], true)) ? ' bac-logos--recolor-' . $s['logo_recolor'] : '';
        $is_scroll = (! empty($s['scroll']) && $s['scroll'] === 'yes');

        // Build the logo items once (reused for both marquee tracks).
        $item_html = '';
        foreach ((array) $s['logos'] as $logo) {
            $item_html .= '<div class="bac-logo">';
            if (! empty($logo['image']['url'])) {
                $ah = (! empty($logo['aria_hidden']) && $logo['aria_hidden'] === 'yes');
                $item_html .= bac_img_tag($logo['image']['url'], $logo['name'], ['aria_hidden' => $ah]);
            } else {
                $item_html .= '<span>' . esc_html($logo['name']) . '</span>';
            }
            $item_html .= '</div>';
        }
        ?>
        <section class="bac-block bac-logos bac-logos--<?php echo esc_attr($theme); ?> bac-logos--<?php echo esc_attr($style); ?><?php echo esc_attr($recolor); ?><?php echo $is_scroll ? ' bac-logos--scroll' : ''; ?>">
          <div class="bac-logos-inner">
            <?php if (! empty($s['eyebrow']) || ! empty($s['heading']) || ! empty($s['subtext'])) : ?>
              <div class="bac-logos-head">
                <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow <?php echo $theme === 'dark' ? 'light' : ''; ?>"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
                <?php if (! empty($s['heading'])) : ?><h2 class="bac-logos-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
                <?php if (! empty($s['subtext'])) : ?><p class="bac-logos-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
              </div>
            <?php endif; ?>
            <?php
            if ($is_scroll) {
                $dur   = (! empty($s['scroll_speed'])) ? (float) $s['scroll_speed'] : 40;
                $pause = (! empty($s['scroll_pause']) && $s['scroll_pause'] === 'yes') ? ' bac-logos-marquee--pause' : '';
                echo '<div class="bac-logos-marquee' . $pause . '" style="--bac-marquee-duration:' . esc_attr($dur) . 's;">';
                echo '<div class="bac-logos-track">' . $item_html . '</div>';
                echo '<div class="bac-logos-track" aria-hidden="true">' . $item_html . '</div>';
                echo '</div>';
            } else {
                echo '<div class="bac-logos-grid">' . $item_html . '</div>';
            }
            ?>
            <?php if (! empty($s['cta_label'])) : ?>
              <div class="bac-logos-cta"><a class="bac-btn bac-btn-primary" href="<?php echo esc_url($s['cta_url']['url'] ?: '#'); ?>"><?php echo esc_html($s['cta_label']); ?></a></div>
            <?php endif; ?>
          </div>
        </section>
        <?php
    }
}
