<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Header / Nav widget — renders a REAL WordPress menu.
 * The client edits links at Appearance → Menus; this widget just displays them.
 */
class BAC_Nav_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_nav'; }
    public function get_title() { return 'BAC Header / Nav'; }
    public function get_icon() { return 'eicon-nav-menu'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['nav', 'menu', 'header', 'bac']; }

    /** Build a list of the site's menus for the dropdown. */
    private function get_menu_options() {
        $options = ['' => '— Select a menu —'];
        $menus = wp_get_nav_menus();
        if (! empty($menus)) {
            foreach ($menus as $menu) {
                $options[$menu->term_id] = $menu->name;
            }
        }
        return $options;
    }

    protected function register_controls() {

        $this->start_controls_section('brand', [
            'label' => 'Brand',
        ]);

        $this->add_control('header_theme', [
            'label'   => 'Header style',
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => ['dark' => 'Dark (navy background)', 'light' => 'Light (white background)'],
            'default' => 'dark',
        ]);

        $this->add_control('logo', [
            'label'   => 'Logo — DARK header (use a light / white logo)',
            'type'    => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true],
            'description' => 'Shown when Header style is Dark. Leave empty to use the text logo below.',
        ]);
        $this->add_control('logo_dark', [
            'label'   => 'Logo — LIGHT header (use a dark logo)',
            'type'    => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true],
            'description' => 'Shown when Header style is Light. Falls back to the other logo if empty.',
        ]);
        $this->add_control('logo_height', [
            'label'      => 'Logo height (px)',
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 16, 'max' => 60, 'step' => 1]],
            'default'    => ['unit' => 'px', 'size' => 40],
            'description' => 'Constrained to fit the nav bar.',
        ]);
        $this->add_control('logo_max_width', [
            'label'      => 'Logo max width (px, optional)',
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 40, 'max' => 400, 'step' => 1]],
            'default'    => ['unit' => 'px', 'size' => ''],
            'description' => 'Leave blank to keep the aspect ratio from the height.',
        ]);
        $this->add_control('brand_line1', [
            'label'   => 'Brand text (line 1)',
            'type'    => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Branded',
        ]);
        $this->add_control('brand_line2', [
            'label'   => 'Brand text (line 2)',
            'type'    => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Apparel Club',
        ]);
        $this->add_control('home_url', [
            'label'   => 'Logo links to',
            'type'    => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true],
            'default' => ['url' => home_url('/')],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('menu', [
            'label' => 'Menu',
        ]);

        $menus = wp_get_nav_menus();
        if (empty($menus)) {
            $this->add_control('no_menus', [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw'  => 'No menus yet. Create one at <strong>Appearance → Menus</strong>, then pick it here.',
                'content_classes' => 'elementor-descriptor',
            ]);
        }

        $this->add_control('menu_id', [
            'label'   => 'Which menu to show',
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => $this->get_menu_options(),
            'default' => '',
            'description' => 'Leave on "Select a menu" to auto-use the menu named <strong>BAC Main Menu</strong>. Edit the links at Appearance → Menus.',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('actions', [
            'label' => 'Buttons',
        ]);

        $this->add_control('login_label', [
            'label'   => 'Button text',
            'type'    => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Login / Join',
            'description' => 'Styled as the primary button. Opens the login modal.',
        ]);
        $this->add_control('login_url', [
            'label'   => 'Fallback link (if JS is off)',
            'type'    => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true],
            'default' => ['url' => '#login-modal'],
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();

        $home  = ! empty($s['home_url']['url']) ? $s['home_url']['url'] : home_url('/');
        $theme = (isset($s['header_theme']) && $s['header_theme'] === 'light') ? 'light' : 'dark';

        // Pick the right logo for the header style, with a fallback to the other.
        $logo_light = ! empty($s['logo']['url']) ? $s['logo']['url'] : '';
        $logo_dark  = ! empty($s['logo_dark']['url']) ? $s['logo_dark']['url'] : '';
        $logo_url   = ($theme === 'light') ? ($logo_dark ?: $logo_light) : ($logo_light ?: $logo_dark);

        // Size controls -> CSS vars so the logo is always constrained to the bar.
        $lh = (isset($s['logo_height']['size']) && $s['logo_height']['size'] !== '') ? (int) $s['logo_height']['size'] : 40;
        $lw = (isset($s['logo_max_width']['size']) && $s['logo_max_width']['size'] !== '') ? (int) $s['logo_max_width']['size'] : 0;
        $brand_style = '--bac-logo-h:' . $lh . 'px;';
        if ($lw > 0) {
            $brand_style .= '--bac-logo-maxw:' . $lw . 'px;';
        }
        ?>
        <nav class="bac-block bac-nav bac-nav--<?php echo esc_attr($theme); ?>" aria-label="Main navigation">
          <div class="bac-nav-inner">

            <a class="bac-nav-brand" href="<?php echo esc_url($home); ?>" style="<?php echo esc_attr($brand_style); ?>">
              <?php if (! empty($logo_url)) : ?>
                <?php echo bac_img_tag($logo_url, trim($s['brand_line1'] . ' ' . $s['brand_line2']), ['lazy' => false]); ?>
              <?php else : ?>
                <span class="bac-nav-mark" aria-hidden="true"><?php echo esc_html(substr($s['brand_line1'], 0, 1)); ?></span>
                <span class="bac-nav-brandtext">
                  <span class="l1"><?php echo esc_html($s['brand_line1']); ?></span>
                  <span class="l2"><?php echo esc_html($s['brand_line2']); ?></span>
                </span>
              <?php endif; ?>
            </a>

            <button class="bac-nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="bac-nav-collapse">
              <span></span><span></span><span></span>
            </button>

            <div class="bac-nav-collapse" id="bac-nav-collapse">
              <?php
              // Use the picked menu, else auto-detect a menu named "BAC Main Menu".
              $menu_id = ! empty($s['menu_id']) ? (int) $s['menu_id'] : 0;
              if (! $menu_id) {
                  $fallback = wp_get_nav_menu_object('BAC Main Menu');
                  if ($fallback) {
                      $menu_id = (int) $fallback->term_id;
                  }
              }
              if ($menu_id) {
                  wp_nav_menu([
                      'menu'        => $menu_id,
                      'container'   => false,
                      'menu_class'  => 'bac-nav-links',
                      'depth'       => 2,
                      'fallback_cb' => false,
                  ]);
              } elseif (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                  echo '<p class="bac-nav-hint">Create a menu named <strong>BAC Main Menu</strong> at Appearance → Menus (or pick one in the widget\'s Menu settings).</p>';
              }
              ?>
              <div class="bac-nav-actions">
                <?php if (is_user_logged_in()) : ?>
                  <div class="bac-nav-user">
                    <button type="button" class="bac-nav-user-trigger" aria-haspopup="true" aria-expanded="false" aria-controls="bac-nav-user-menu" aria-label="Account menu">
                      <i class="fa-solid fa-gauge" aria-hidden="true"></i>
                    </button>
                    <div class="bac-nav-popover" id="bac-nav-user-menu" hidden>
                      <a class="bac-nav-popover-link" href="<?php echo esc_url(admin_url('/')); ?>">
                        <i class="fa-solid fa-gauge" aria-hidden="true"></i> Dashboard
                      </a>
                      <a class="bac-nav-popover-link" href="<?php echo esc_url(home_url('/dashboard/')); ?>">
                        <i class="fa-solid fa-handshake" aria-hidden="true"></i> Affiliate Dashboard
                      </a>
                    </div>
                  </div>
                <?php elseif (! empty($s['login_label'])) : ?>
                  <a class="bac-btn bac-btn-primary bac-nav-cta" href="<?php echo esc_url($s['login_url']['url'] ?: '#login-modal'); ?>" data-bac-open="login"><?php echo esc_html($s['login_label']); ?></a>
                <?php endif; ?>
              </div>
            </div>

          </div>
        </nav>
        <?php
    }
}
