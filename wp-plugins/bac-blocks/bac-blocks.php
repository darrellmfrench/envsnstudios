<?php
/**
 * Plugin Name:       BAC Blocks
 * Description:        Custom Elementor widgets for the Branded Apparel Club site (header/nav, hero, stats, card grid). Client-editable, no code required.
 * Version:           0.9.17
 * Author:            ENVSN Studios
 * Requires Plugins:  elementor
 * Text Domain:       bac-blocks
 * Update URI:        https://github.com/darrellmfrench/envsnstudios
 *
 * Assets live in this plugin (not the theme), so a staging sync that overwrites
 * the theme will NOT wipe the styling.
 */

if (! defined('ABSPATH')) {
    exit;
}

define('BAC_BLOCKS_VER', '0.9.17');
define('BAC_BLOCKS_PATH', plugin_dir_path(__FILE__));
define('BAC_BLOCKS_URL', plugin_dir_url(__FILE__));

/**
 * Self-updater: check the GitHub repo's latest Release and let WordPress update
 * this plugin from it. Loads independently of Elementor so updates always work.
 */
require_once BAC_BLOCKS_PATH . 'includes/class-bac-paths.php';
require_once BAC_BLOCKS_PATH . 'includes/class-bac-github-updater.php';
new ENVSN_GitHub_Updater(__FILE__, [
    'slug'       => 'bac-blocks',
    'owner'      => 'darrellmfrench',
    'repo'       => 'envsnstudios',
    'version'    => BAC_BLOCKS_VER,
    'asset'      => 'bac-blocks.zip',
    'tag_prefix' => 'bac-blocks-', // monorepo: release tags like bac-blocks-0.9.17
]);

/**
 * Shared, accessible image tag for all BAC widgets (now and going forward).
 *
 * - Always sets loading + decoding="async" (defaults to lazy; pass 'lazy'=>false
 *   for above-the-fold LCP images like the hero/logo so SEO isn't hurt).
 * - aria_hidden=true  -> outputs aria-hidden="true" AND an empty alt (decorative).
 * - aria_hidden=false -> outputs the descriptive alt (accessibility + SEO).
 *
 * @param string $url  Image URL.
 * @param string $alt  Descriptive alt text (used only when not aria-hidden).
 * @param array  $opts lazy(bool), aria_hidden(bool), class(string), fetchpriority(string).
 */
function bac_img_tag($url, $alt = '', $opts = []) {
    if (empty($url)) {
        return '';
    }
    $lazy = array_key_exists('lazy', $opts) ? (bool) $opts['lazy'] : true;
    $out  = '<img src="' . esc_url($url) . '"';
    $out .= ' loading="' . ($lazy ? 'lazy' : 'eager') . '"';
    $out .= ' decoding="async"';
    if (! empty($opts['fetchpriority'])) {
        $out .= ' fetchpriority="' . esc_attr($opts['fetchpriority']) . '"';
    }
    if (! empty($opts['class'])) {
        $out .= ' class="' . esc_attr($opts['class']) . '"';
    }
    if (! empty($opts['aria_hidden'])) {
        $out .= ' alt="" aria-hidden="true"';
    } else {
        $out .= ' alt="' . esc_attr($alt) . '"';
    }
    return $out . '>';
}

/**
 * Reusable Elementor controls other widgets can add for a single image:
 * a descriptive alt field + an aria-hidden (decorative) switcher.
 * Call from a widget's register_controls(): bac_add_image_a11y_controls($this, 'image');
 */
function bac_add_image_a11y_controls($widget, $prefix = 'image') {
    $widget->add_control($prefix . '_alt', [
        'label'       => 'Image alt text (SEO / screen readers)',
        'type'        => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
        'default'     => '',
        'description' => 'Describe the image. Leave blank only if the image is purely decorative.',
    ]);
    $widget->add_control($prefix . '_aria_hidden', [
        'label'        => 'Hide image from screen readers',
        'type'         => \Elementor\Controls_Manager::SWITCHER,
        'label_on'     => 'Yes',
        'label_off'    => 'No',
        'return_value' => 'yes',
        'default'      => '',
        'description'  => 'On = decorative (adds aria-hidden="true"). Off = announced to screen readers.',
    ]);
}

/**
 * Add a "Section theme" (Light / Dark) control to a widget. Every section can
 * flip between light and dark; the theming CSS keeps text readable (WCAG AA).
 */
function bac_add_theme_control($widget, $default = 'light') {
    $widget->add_control('section_theme', [
        'label'       => 'Section theme',
        'type'        => \Elementor\Controls_Manager::SELECT,
        'options'     => ['light' => 'Light', 'gray' => 'Gray', 'dark' => 'Dark'],
        'default'     => $default,
        'description' => 'Light, soft gray band, or dark. Text colors adjust automatically to stay readable.',
    ]);
}

/** Return the theme class for a section based on its setting. */
function bac_theme_class($settings, $default = 'light') {
    $t = (isset($settings['section_theme']) && in_array($settings['section_theme'], ['light', 'gray', 'dark'], true))
        ? $settings['section_theme'] : $default;
    return 'bac-theme-' . $t;
}

/**
 * Add native-feeling Spacing controls (Style tab): padding top/bottom on the
 * section inner, and margin on the widget wrapper — so users control spacing
 * without fighting the block's built-in padding.
 */
function bac_add_spacing_controls($widget) {
    $widget->start_controls_section('bac_spacing', [
        'label' => 'Spacing',
        'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
    ]);
    $widget->add_responsive_control('bac_pad_top', [
        'label' => 'Padding top', 'type' => \Elementor\Controls_Manager::SLIDER,
        'size_units' => ['px', 'rem', 'em'], 'range' => ['px' => ['min' => 0, 'max' => 320]],
        'selectors' => ['{{WRAPPER}} [class$="-inner"]' => 'padding-top: {{SIZE}}{{UNIT}} !important;'],
    ]);
    $widget->add_responsive_control('bac_pad_bottom', [
        'label' => 'Padding bottom', 'type' => \Elementor\Controls_Manager::SLIDER,
        'size_units' => ['px', 'rem', 'em'], 'range' => ['px' => ['min' => 0, 'max' => 320]],
        'selectors' => ['{{WRAPPER}} [class$="-inner"]' => 'padding-bottom: {{SIZE}}{{UNIT}} !important;'],
    ]);
    $widget->add_responsive_control('bac_pad_x', [
        'label' => 'Padding left/right', 'type' => \Elementor\Controls_Manager::SLIDER,
        'size_units' => ['px', 'rem', 'em'], 'range' => ['px' => ['min' => 0, 'max' => 200]],
        'selectors' => ['{{WRAPPER}} [class$="-inner"]' => 'padding-left: {{SIZE}}{{UNIT}} !important; padding-right: {{SIZE}}{{UNIT}} !important;'],
    ]);
    $widget->add_responsive_control('bac_margin', [
        'label' => 'Margin', 'type' => \Elementor\Controls_Manager::DIMENSIONS,
        'size_units' => ['px', 'rem', '%'],
        'selectors' => ['{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
    ]);
    $widget->end_controls_section();
}

/** Aspect-ratio choices for card images. */
function bac_ratio_options() {
    return [
        '16 / 9' => '16:9 (landscape)',
        '4 / 3'  => '4:3',
        '1 / 1'  => '1:1 (square)',
        '7 / 5'  => '7:5',
        '5 / 7'  => '5:7 (portrait)',
        '3 / 4'  => '3:4 (portrait)',
        '9 / 16' => '9:16 (tall)',
        'auto'   => 'Original (no crop)',
    ];
}

/** Add "Image aspect ratio" + "Image fit" controls for card images. */
function bac_add_ratio_control($widget, $default = '16 / 9') {
    $widget->add_control('img_ratio', [
        'label'       => 'Image aspect ratio',
        'type'        => \Elementor\Controls_Manager::SELECT,
        'options'     => bac_ratio_options(),
        'default'     => $default,
        'description' => 'Crops all card images to this ratio at every breakpoint. "Original" keeps each image\'s natural shape.',
    ]);
    $widget->add_control('img_fit', [
        'label'       => 'Image fit',
        'type'        => \Elementor\Controls_Manager::SELECT,
        'options'     => [
            'cover'   => 'Cover (fill & crop)',
            'contain' => 'Contain (fit inside)',
            'fill'    => 'Fill (stretch to 100%)',
        ],
        'default'     => 'cover',
        'description' => 'How the image sits in the ratio box.',
    ]);
}

/** Resolve the chosen ratio to a CSS aspect-ratio value. */
function bac_ratio_value($settings, $default = '16 / 9') {
    $opts = bac_ratio_options();
    return (! empty($settings['img_ratio']) && isset($opts[$settings['img_ratio']])) ? $settings['img_ratio'] : $default;
}

/** Resolve the chosen object-fit value. */
function bac_fit_value($settings, $default = 'cover') {
    $ok = ['cover', 'contain', 'fill'];
    return (! empty($settings['img_fit']) && in_array($settings['img_fit'], $ok, true)) ? $settings['img_fit'] : $default;
}

/**
 * Register a menu location so the Header/Nav widget can pull a real WP menu.
 * The client edits this at Appearance → Menus.
 */
add_action('after_setup_theme', function () {
    register_nav_menus(['bac_primary' => 'BAC Primary Menu']);
});

/**
 * Front-end + editor stylesheet (bundled with the plugin).
 */
function bac_blocks_enqueue_styles() {
    wp_enqueue_style(
        'bac-blocks',
        BAC_BLOCKS_URL . 'assets/bac-blocks.css',
        [],
        BAC_BLOCKS_VER
    );
    // Inter font for the brand look.
    wp_enqueue_style(
        'bac-blocks-inter',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',
        [],
        null
    );
}
add_action('wp_enqueue_scripts', 'bac_blocks_enqueue_styles');
add_action('elementor/frontend/after_enqueue_styles', 'bac_blocks_enqueue_styles');
add_action('elementor/editor/after_enqueue_styles', 'bac_blocks_enqueue_styles');

/**
 * Small script for the mobile nav toggle.
 */
function bac_blocks_enqueue_scripts() {
    wp_enqueue_script(
        'bac-blocks',
        BAC_BLOCKS_URL . 'assets/bac-blocks.js',
        [],
        BAC_BLOCKS_VER,
        true
    );
}
add_action('wp_enqueue_scripts', 'bac_blocks_enqueue_scripts');

/**
 * Output the site-wide Login and Contact dialog modals once, in the footer.
 * Trigger them from any link with data-bac-open="login" / "contact",
 * or a link whose URL is #login-modal / #contact-modal.
 */
function bac_render_modals() {
    static $done = false;
    if ($done || is_admin()) {
        return;
    }
    $done = true;
    ?>
    <dialog id="login-modal" class="bac-block bac-modal" aria-labelledby="bac-login-title">
      <div class="bac-modal-inner">
        <div class="bac-modal-head">
          <div>
            <h2 id="bac-login-title" class="bac-modal-title">Member Login</h2>
            <p class="bac-modal-sub">Sign in to your member dashboard.</p>
          </div>
          <button type="button" class="bac-modal-close" data-bac-close aria-label="Close login form">&times;</button>
        </div>
        <form class="bac-modal-form" method="post" action="<?php echo esc_url(bac_path('login_action')); ?>">
          <div class="bac-field">
            <label for="bac-login-user">Email or Username</label>
            <input type="text" id="bac-login-user" name="log" autocomplete="username" required>
          </div>
          <div class="bac-field">
            <label for="bac-login-pass">Password</label>
            <input type="password" id="bac-login-pass" name="pwd" autocomplete="current-password" required>
          </div>
          <div class="bac-modal-meta">
            <label class="bac-modal-check"><input type="checkbox" name="rememberme" value="forever"> Remember me</label>
            <a href="<?php echo esc_url(bac_path('forgot')); ?>">Forgot password?</a>
          </div>
          <button type="submit" class="bac-btn bac-btn-primary">Log In</button>
        </form>
        <p class="bac-modal-alt">Not a member yet? <a href="<?php echo esc_url(bac_path('register')); ?>">Join the Club</a></p>
      </div>
    </dialog>

    <dialog id="contact-modal" class="bac-block bac-modal" aria-labelledby="bac-contact-title">
      <div class="bac-modal-inner">
        <div class="bac-modal-head">
          <div>
            <h2 id="bac-contact-title" class="bac-modal-title">Contact Us</h2>
            <p class="bac-modal-sub">We typically respond within 1 business day.</p>
          </div>
          <button type="button" class="bac-modal-close" data-bac-close aria-label="Close contact form">&times;</button>
        </div>
        <form class="bac-modal-form" id="bac-contact-form" novalidate>
          <div class="bac-modal-row">
            <div class="bac-field"><label for="bac-cf-name">Full Name</label><input type="text" id="bac-cf-name" name="name" autocomplete="name" required></div>
            <div class="bac-field"><label for="bac-cf-email">Email</label><input type="email" id="bac-cf-email" name="email" autocomplete="email" required></div>
          </div>
          <div class="bac-field">
            <label for="bac-cf-subject">Subject</label>
            <select id="bac-cf-subject" name="subject" required>
              <option value="" disabled selected>Please select one</option>
              <option>Order Issue</option>
              <option>Membership Question</option>
              <option>Billing Question</option>
              <option>Product Question</option>
              <option>Other</option>
            </select>
          </div>
          <div class="bac-field"><label for="bac-cf-message">Message</label><textarea id="bac-cf-message" name="message" rows="4" required></textarea></div>
          <button type="submit" class="bac-btn bac-btn-primary">Send Message</button>
        </form>
      </div>
    </dialog>
    <?php
}
add_action('wp_footer', 'bac_render_modals', 20);

/**
 * Widget category so our widgets group together in the Elementor panel.
 */
add_action('elementor/elements/categories_registered', function ($manager) {
    $manager->add_category('bac-blocks', [
        'title' => 'Branded Apparel Club',
        'icon'  => 'fa fa-plug',
    ]);
});

/**
 * Register the widgets.
 */
add_action('elementor/widgets/register', function ($widgets_manager) {
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-nav.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-hero.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-fullhero.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-stats.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-cards.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-split.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-logos.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-steps.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-industries.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-cta.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-faq.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-features.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-testimonials.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-pricing.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-compare.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-trust.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-newsletter.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-pageheader.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-posts.php';
    require_once BAC_BLOCKS_PATH . 'widgets/class-bac-footer.php';

    $widgets_manager->register(new \BAC_Nav_Widget());
    $widgets_manager->register(new \BAC_Hero_Widget());
    $widgets_manager->register(new \BAC_FullHero_Widget());
    $widgets_manager->register(new \BAC_Stats_Widget());
    $widgets_manager->register(new \BAC_Cards_Widget());
    $widgets_manager->register(new \BAC_Split_Widget());
    $widgets_manager->register(new \BAC_Logos_Widget());
    $widgets_manager->register(new \BAC_Steps_Widget());
    $widgets_manager->register(new \BAC_Industries_Widget());
    $widgets_manager->register(new \BAC_Cta_Widget());
    $widgets_manager->register(new \BAC_Faq_Widget());
    $widgets_manager->register(new \BAC_Features_Widget());
    $widgets_manager->register(new \BAC_Testimonials_Widget());
    $widgets_manager->register(new \BAC_Pricing_Widget());
    $widgets_manager->register(new \BAC_Compare_Widget());
    $widgets_manager->register(new \BAC_Trust_Widget());
    $widgets_manager->register(new \BAC_Newsletter_Widget());
    $widgets_manager->register(new \BAC_PageHeader_Widget());
    $widgets_manager->register(new \BAC_Posts_Widget());
    $widgets_manager->register(new \BAC_Footer_Widget());
});

/**
 * Friendly notice if Elementor isn't active.
 */
add_action('admin_notices', function () {
    if (did_action('elementor/loaded')) {
        return;
    }
    echo '<div class="notice notice-warning"><p><strong>BAC Blocks</strong> needs Elementor active to work.</p></div>';
});
