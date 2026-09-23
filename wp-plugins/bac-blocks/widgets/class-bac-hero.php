<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Split Hero — text left, image right. All fields editable.
 */
class BAC_Hero_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_hero'; }
    public function get_title() { return 'BAC Hero'; }
    public function get_icon() { return 'eicon-banner'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['hero', 'banner', 'bac']; }

    protected function register_controls() {

        $this->start_controls_section('content', ['label' => 'Content']);

        $this->add_control('eyebrow', [
            'label'   => 'Small label (eyebrow)',
            'type'    => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Member-Exclusive Pricing',
        ]);
        $this->add_control('heading', [
            'label'   => 'Heading',
            'type'    => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => 'Branded Apparel That Moves Business Forward.',
        ]);
        $this->add_control('subtext', [
            'label'   => 'Sub text',
            'type'    => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => 'Professional apparel, branded merchandise, and webstore solutions that help businesses outfit their teams, promote their brand, and sell online.',
        ]);
        $this->add_control('image', [
            'label'       => 'Hero image',
            'type'        => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true],
            'description' => 'Drop the hero photo here (people in branded apparel). Until set, a gray placeholder shows. Loads eagerly (it is the LCP image).',
        ]);
        bac_add_image_a11y_controls($this, 'image');

        $this->add_control('img_fit', [
            'label'       => 'Image fit',
            'type'        => \Elementor\Controls_Manager::SELECT,
            'options'     => [
                'cover'   => 'Cover (fill & crop)',
                'contain' => 'Contain (fit inside)',
                'fill'    => 'Fill (stretch to 100%)',
            ],
            'default'     => 'cover',
            'description' => 'How the hero image sits in its box.',
        ]);
        $this->add_control('img_pos', [
            'label'       => 'Image focal point',
            'type'        => \Elementor\Controls_Manager::SELECT,
            'options'     => [
                'center'       => 'Center',
                'top'          => 'Top',
                'bottom'       => 'Bottom',
                'left'         => 'Left',
                'right'        => 'Right',
                'left top'     => 'Top left',
                'right top'    => 'Top right',
                'left bottom'  => 'Bottom left',
                'right bottom' => 'Bottom right',
            ],
            'default'     => 'center',
            'description' => 'Which part of the image to keep in view when cropped.',
            'condition'   => ['img_fit' => 'cover'],
        ]);
        $this->add_control('img_radius', [
            'label'      => 'Image corner radius (px)',
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 60, 'step' => 1]],
            'default'    => ['unit' => 'px', 'size' => 14],
            'description' => 'Rounds the corners of the hero image. Set 0 for square corners.',
        ]);

        bac_add_theme_control($this, 'light');

        $this->end_controls_section();

        $this->start_controls_section('buttons', ['label' => 'Buttons']);

        $this->add_control('btn1_label', [
            'label' => 'Primary button text',
            'type'  => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Become a Member',
        ]);
        $this->add_control('btn1_url', [
            'label' => 'Primary button link',
            'type'  => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true],
            'default' => ['url' => '/join/'],
        ]);
        $this->add_control('btn2_label', [
            'label' => 'Secondary button text',
            'type'  => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'How It Works',
        ]);
        $this->add_control('btn2_url', [
            'label' => 'Secondary button link',
            'type'  => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true],
            'default' => ['url' => '/how-it-works/'],
        ]);

        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $fit_ok = ['cover', 'contain', 'fill'];
        $img_fit = (! empty($s['img_fit']) && in_array($s['img_fit'], $fit_ok, true)) ? $s['img_fit'] : 'cover';
        $pos_ok  = ['center', 'top', 'bottom', 'left', 'right', 'left top', 'right top', 'left bottom', 'right bottom'];
        $img_pos = (! empty($s['img_pos']) && in_array($s['img_pos'], $pos_ok, true)) ? $s['img_pos'] : 'center';
        $img_radius = (isset($s['img_radius']['size']) && $s['img_radius']['size'] !== '') ? (int) $s['img_radius']['size'] : 14;
        $media_style = '--bac-hero-fit:' . $img_fit . ';--bac-hero-pos:' . $img_pos . ';--bac-hero-radius:' . $img_radius . 'px;';
        ?>
        <section class="bac-block bac-hero <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-hero-inner">

            <div class="bac-hero-text">
              <?php if (! empty($s['eyebrow'])) : ?>
                <p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p>
              <?php endif; ?>
              <?php if (! empty($s['heading'])) : ?>
                <h1 class="bac-hero-title"><?php echo esc_html($s['heading']); ?></h1>
              <?php endif; ?>
              <?php if (! empty($s['subtext'])) : ?>
                <p class="bac-hero-sub"><?php echo esc_html($s['subtext']); ?></p>
              <?php endif; ?>

              <div class="bac-hero-ctas">
                <?php if (! empty($s['btn1_label'])) : ?>
                  <a class="bac-btn bac-btn-primary" href="<?php echo esc_url($s['btn1_url']['url'] ?: '#'); ?>"><?php echo esc_html($s['btn1_label']); ?></a>
                <?php endif; ?>
                <?php if (! empty($s['btn2_label'])) : ?>
                  <a class="bac-btn bac-btn-ghost" href="<?php echo esc_url($s['btn2_url']['url'] ?: '#'); ?>"><?php echo esc_html($s['btn2_label']); ?> <span aria-hidden="true">&rarr;</span></a>
                <?php endif; ?>
              </div>
            </div>

            <div class="bac-hero-media" style="<?php echo esc_attr($media_style); ?>">
              <?php if (! empty($s['image']['url'])) :
                  $hero_ah = (! empty($s['image_aria_hidden']) && $s['image_aria_hidden'] === 'yes');
                  echo bac_img_tag($s['image']['url'], isset($s['image_alt']) ? $s['image_alt'] : '', ['lazy' => false, 'fetchpriority' => 'high', 'aria_hidden' => $hero_ah]);
              else : ?>
                <div class="bac-media-placeholder"><span>Hero image</span></div>
              <?php endif; ?>
            </div>

          </div>
        </section>
        <?php
    }
}
