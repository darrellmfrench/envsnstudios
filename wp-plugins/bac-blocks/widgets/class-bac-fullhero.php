<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Full Hero — full-bleed background image with an overlay, eyebrow, big heading,
 * subtext, up to a few buttons (each with its own style), and an optional
 * icon/stat bar. Art-directed: separate desktop / tablet / mobile images, with
 * per-breakpoint object-fit and object-position. Text can sit left, center, or
 * right. All fields support Dynamic Tags.
 */
class BAC_FullHero_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_full_hero'; }
    public function get_title() { return 'BAC Full Hero'; }
    public function get_icon() { return 'eicon-hero'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['hero', 'full', 'banner', 'cover', 'header', 'bac']; }

    private function btn_style_options() {
        return [
            'primary'       => 'Blue (solid)',
            'white'         => 'White (blue text)',
            'outline-white' => 'Outlined (white)',
            'ghost'         => 'White (navy text)',
        ];
    }
    private function btn_style_class($v) {
        $ok = ['primary' => 'bac-btn-primary', 'white' => 'bac-btn-white', 'outline-white' => 'bac-btn-outline-white', 'ghost' => 'bac-btn-ghost'];
        return isset($ok[$v]) ? $ok[$v] : 'bac-btn-primary';
    }

    protected function register_controls() {

        /* ---------- Content ---------- */
        $this->start_controls_section('content', ['label' => 'Content']);
        $this->add_control('eyebrow', [
            'label' => 'Small label (eyebrow)', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Branded Apparel Club',
        ]);
        $this->add_control('heading', [
            'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => 'Looking Good Is Good For Business',
        ]);
        $this->add_control('heading_tag', [
            'label' => 'Heading tag (SEO)', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['h1' => 'H1 (main page title)', 'h2' => 'H2'], 'default' => 'h1',
            'description' => 'Use H1 once per page — for the primary page title.',
        ]);
        $this->add_control('subtext', [
            'label' => 'Sub text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => "Branded Apparel Club is built for businesses that understand looking the part isn't about vanity—it's a competitive advantage.",
        ]);
        $this->add_control('align', [
            'label' => 'Text position', 'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'left'   => ['title' => 'Left',   'icon' => 'eicon-text-align-left'],
                'center' => ['title' => 'Center', 'icon' => 'eicon-text-align-center'],
                'right'  => ['title' => 'Right',  'icon' => 'eicon-text-align-right'],
            ],
            'default' => 'left', 'toggle' => false,
        ]);
        $this->add_control('scheme', [
            'label' => 'Text color', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['light' => 'Light (for dark images)', 'dark' => 'Dark (for light images)'], 'default' => 'light',
            'description' => 'Pick the one that stays readable over your image + overlay.',
        ]);
        $this->add_responsive_control('min_height', [
            'label' => 'Height', 'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['vh', 'px'], 'range' => ['vh' => ['min' => 40, 'max' => 100], 'px' => ['min' => 320, 'max' => 1200]],
            'default' => ['unit' => 'vh', 'size' => 88],
            'tablet_default' => ['unit' => 'vh', 'size' => 70],
            'mobile_default' => ['unit' => 'vh', 'size' => 78],
            'selectors' => ['{{WRAPPER}} .bac-fhero' => 'min-height: {{SIZE}}{{UNIT}};'],
        ]);
        $this->add_control('overlay', [
            'label' => 'Overlay darkness (%)', 'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['%'], 'range' => ['%' => ['min' => 0, 'max' => 90]],
            'default' => ['unit' => '%', 'size' => 45],
            'description' => 'Darkens the image so text stays readable (accessibility). Raise it if text is hard to read.',
        ]);
        $this->end_controls_section();

        /* ---------- Background image (art-directed) ---------- */
        $this->start_controls_section('bg', ['label' => 'Background image']);
        $this->add_control('bg_desktop', [
            'label' => 'Desktop image', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true],
            'description' => 'The main background. Loads eagerly (it is the hero/LCP image).',
        ]);
        $this->add_control('bg_tablet', [
            'label' => 'Tablet image (optional)', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true],
            'description' => 'Shown ≤ 1024px. Falls back to the desktop image if empty.',
        ]);
        $this->add_control('bg_mobile', [
            'label' => 'Mobile image (optional)', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true],
            'description' => 'Shown ≤ 767px. Falls back to the desktop image if empty.',
        ]);
        $this->add_control('bg_alt', [
            'label' => 'Image alt text', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => '',
            'description' => 'Describe the image for SEO / screen readers. Leave blank if the image is purely decorative.',
        ]);
        $this->add_control('bg_decorative', [
            'label' => 'Image is decorative', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => 'Yes', 'label_off' => 'No', 'return_value' => 'yes', 'default' => 'yes',
            'description' => 'On = hidden from screen readers (recommended when the heading already conveys the meaning).',
        ]);
        $this->add_responsive_control('obj_fit', [
            'label' => 'Image fit', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['cover' => 'Cover (fill & crop)', 'contain' => 'Contain (fit inside)', 'fill' => 'Fill (stretch)'],
            'default' => 'cover',
            'selectors' => ['{{WRAPPER}} .bac-fhero-bg img' => 'object-fit: {{VALUE}};'],
        ]);
        $this->add_responsive_control('obj_pos', [
            'label' => 'Image focal point', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'center center' => 'Center', 'center top' => 'Top', 'center bottom' => 'Bottom',
                'left center' => 'Left', 'right center' => 'Right',
                'left top' => 'Top left', 'right top' => 'Top right',
                'left bottom' => 'Bottom left', 'right bottom' => 'Bottom right',
            ],
            'default' => 'center center',
            'selectors' => ['{{WRAPPER}} .bac-fhero-bg img' => 'object-position: {{VALUE}};'],
        ]);
        $this->end_controls_section();

        /* ---------- Buttons ---------- */
        $this->start_controls_section('buttons', ['label' => 'Buttons']);
        $br = new \Elementor\Repeater();
        $br->add_control('label', ['label' => 'Button text', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Become a Member']);
        $br->add_control('url', ['label' => 'Link', 'type' => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true], 'default' => ['url' => '/join/']]);
        $br->add_control('style', ['label' => 'Style', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => $this->btn_style_options(), 'default' => 'primary']);
        $this->add_control('buttons', [
            'label' => 'Buttons', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $br->get_controls(), 'title_field' => '{{{ label }}}',
            'description' => 'Add up to a few. Each button picks its own style.',
            'default' => [
                ['label' => 'Become a Member', 'url' => ['url' => '/join/'], 'style' => 'primary'],
                ['label' => 'How It Works', 'url' => ['url' => '/how-it-works/'], 'style' => 'outline-white'],
            ],
        ]);
        $this->end_controls_section();

        /* ---------- Icon / stat bar ---------- */
        $this->start_controls_section('features', ['label' => 'Icon / stat bar']);
        $this->add_control('show_features', [
            'label' => 'Show the stat bar', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => 'Yes', 'label_off' => 'No', 'return_value' => 'yes', 'default' => 'yes',
        ]);
        $fr = new \Elementor\Repeater();
        $fr->add_control('icon', ['label' => 'Icon (optional)', 'type' => \Elementor\Controls_Manager::ICONS, 'description' => 'Leave empty for no icon.']);
        $fr->add_control('title', ['label' => 'Top line', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '10,000+']);
        $fr->add_control('subtitle', ['label' => 'Bottom line', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Businesses Served']);
        $this->add_control('feature_items', [
            'label' => 'Stat items', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $fr->get_controls(), 'title_field' => '{{{ title }}}',
            'condition' => ['show_features' => 'yes'],
            'description' => 'Icons are optional per item — leave the icon empty for a text-only stat.',
            'default' => [
                ['icon' => ['value' => 'fas fa-users', 'library' => 'fa-solid'], 'title' => '10,000+', 'subtitle' => 'Businesses Served'],
                ['icon' => ['value' => 'fas fa-medal', 'library' => 'fa-solid'], 'title' => 'Premium', 'subtitle' => 'Apparel Brands'],
                ['icon' => ['value' => 'fas fa-truck', 'library' => 'fa-solid'], 'title' => 'Fast', 'subtitle' => 'Turnaround'],
                ['icon' => ['value' => 'fas fa-globe', 'library' => 'fa-solid'], 'title' => 'Nationwide', 'subtitle' => 'Shipping'],
            ],
        ]);
        $this->end_controls_section();

        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();

        $align  = in_array(($s['align'] ?? 'left'), ['left', 'center', 'right'], true) ? $s['align'] : 'left';
        $scheme = (($s['scheme'] ?? 'light') === 'dark') ? 'dark' : 'light';
        $ovnum  = isset($s['overlay']['size']) && $s['overlay']['size'] !== '' ? (float) $s['overlay']['size'] : 45;
        $ov     = max(0, min(90, $ovnum)) / 100;

        $decorative = (! empty($s['bg_decorative']) && $s['bg_decorative'] === 'yes');
        $alt        = isset($s['bg_alt']) ? $s['bg_alt'] : '';
        $d = ! empty($s['bg_desktop']['url']) ? $s['bg_desktop']['url'] : '';
        $t = ! empty($s['bg_tablet']['url'])  ? $s['bg_tablet']['url']  : '';
        $m = ! empty($s['bg_mobile']['url'])  ? $s['bg_mobile']['url']  : '';

        $img_opts = ['lazy' => false, 'fetchpriority' => 'high', 'aria_hidden' => $decorative];
        $tag = ($s['heading_tag'] ?? 'h1') === 'h2' ? 'h2' : 'h1';
        ?>
        <section class="bac-block bac-fhero bac-fhero--<?php echo esc_attr($align); ?> bac-fhero--<?php echo esc_attr($scheme); ?>" style="--bac-fhero-ov: <?php echo esc_attr($ov); ?>;">

          <div class="bac-fhero-bg" aria-hidden="<?php echo $decorative ? 'true' : 'false'; ?>">
            <?php if ($d) : ?>
              <?php echo bac_img_tag($d, $alt, array_merge($img_opts, ['class' => 'bac-fhero-img bac-fhero-img--desktop' . (($t || $m) ? '' : ' is-only')])); ?>
            <?php endif; ?>
            <?php if ($t) : ?>
              <?php echo bac_img_tag($t, $alt, array_merge($img_opts, ['class' => 'bac-fhero-img bac-fhero-img--tablet'])); ?>
            <?php endif; ?>
            <?php if ($m) : ?>
              <?php echo bac_img_tag($m, $alt, array_merge($img_opts, ['class' => 'bac-fhero-img bac-fhero-img--mobile'])); ?>
            <?php endif; ?>
            <?php if (! $d) : ?><span class="bac-media-placeholder" aria-hidden="true"></span><?php endif; ?>
          </div>

          <div class="bac-fhero-overlay" aria-hidden="true"></div>

          <div class="bac-fhero-inner">
            <div class="bac-fhero-content">
              <?php if (! empty($s['eyebrow'])) : ?><p class="bac-fhero-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
              <?php if (! empty($s['heading'])) : ?><<?php echo $tag; ?> class="bac-fhero-title"><?php echo esc_html($s['heading']); ?></<?php echo $tag; ?>><?php endif; ?>
              <?php if (! empty($s['subtext'])) : ?><p class="bac-fhero-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>

              <?php if (! empty($s['buttons'])) : ?>
                <div class="bac-fhero-ctas">
                  <?php foreach ((array) $s['buttons'] as $b) :
                      if (empty($b['label'])) { continue; }
                      $url   = ! empty($b['url']['url']) ? $b['url']['url'] : '#';
                      $cls   = $this->btn_style_class($b['style'] ?? 'primary');
                      $ext   = ! empty($b['url']['is_external']) ? ' target="_blank"' : '';
                      $rel   = ! empty($b['url']['nofollow']) ? ' rel="nofollow"' : '';
                      $modal = ($url === '#login-modal') ? ' data-bac-open="login"' : (($url === '#contact-modal') ? ' data-bac-open="contact"' : '');
                      $href  = $modal ? esc_attr($url) : esc_url($url);
                  ?>
                    <a class="bac-btn <?php echo esc_attr($cls); ?>" href="<?php echo $href; ?>"<?php echo $ext . $rel . $modal; ?>><?php echo esc_html($b['label']); ?></a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <?php if (! empty($s['show_features']) && $s['show_features'] === 'yes' && ! empty($s['feature_items'])) : ?>
                <ul class="bac-fhero-stats" role="list">
                  <?php foreach ((array) $s['feature_items'] as $f) :
                      if (empty($f['title']) && empty($f['subtitle'])) { continue; }
                      $has_icon = ! empty($f['icon']['value']);
                  ?>
                    <li class="bac-fhero-stat<?php echo $has_icon ? '' : ' no-icon'; ?>">
                      <?php if ($has_icon) : ?>
                        <span class="bac-fhero-stat-ic" aria-hidden="true"><?php \Elementor\Icons_Manager::render_icon($f['icon'], ['aria-hidden' => 'true']); ?></span>
                      <?php endif; ?>
                      <span class="bac-fhero-stat-txt">
                        <?php if (! empty($f['title'])) : ?><span class="bac-fhero-stat-top"><?php echo esc_html($f['title']); ?></span><?php endif; ?>
                        <?php if (! empty($f['subtitle'])) : ?><span class="bac-fhero-stat-sub"><?php echo esc_html($f['subtitle']); ?></span><?php endif; ?>
                      </span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          </div>
        </section>
        <?php
    }
}
