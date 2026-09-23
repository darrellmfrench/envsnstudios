<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Footer — an HTML brand column + repeatable link columns (h3 + ul) + an
 * editable bottom bar (copyright, legal links, "We Accept" payment icons).
 * Add it in Theme Builder just like the header/nav. Client-editable.
 */
class BAC_Footer_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_footer'; }
    public function get_title() { return 'BAC Footer'; }
    public function get_icon() { return 'eicon-footer'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['footer', 'links', 'social', 'bac']; }

    /** Turn "Label | /url" lines into [label,url] pairs. */
    private function parse_links($text) {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
            $line = trim($line);
            if ($line === '') { continue; }
            $parts = array_map('trim', explode('|', $line, 2));
            $out[] = ['label' => $parts[0], 'url' => isset($parts[1]) ? $parts[1] : '#'];
        }
        return $out;
    }

    /** Map a link URL to a modal opener (login / contact) if it points at one. */
    private function modal_attr($url) {
        if ($url === '#login-modal')   { return ' data-bac-open="login"'; }
        if ($url === '#contact-modal') { return ' data-bac-open="contact"'; }
        return '';
    }

    private function default_brand_html() {
        return '<a class="bac-footer-brand" href="/" aria-label="Branded Apparel Club — home"><strong>Branded</strong> Apparel Club</a>' . "\n"
             . '<p class="bac-footer-blurb">Premium branded apparel and membership perks for local businesses who take their brand seriously.</p>' . "\n"
             . '<div class="bac-footer-social">' . "\n"
             . '  <a href="https://www.facebook.com/brandedapparelclub" aria-label="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>' . "\n"
             . '  <a href="https://www.instagram.com/brandedapparelclub" aria-label="Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>' . "\n"
             . '  <a href="https://youtube.com/@brandedapparelclub" aria-label="YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>' . "\n"
             . '  <a href="https://www.linkedin.com/company/brandedapparelclub/" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>' . "\n"
             . '</div>';
    }

    protected function register_controls() {

        /* ---- Brand column: free HTML block ---- */
        $this->start_controls_section('brand', ['label' => 'Brand column (HTML)']);
        $this->add_control('brand_html', [
            'label'       => 'Brand column HTML',
            'type'        => \Elementor\Controls_Manager::CODE,
            'language'    => 'html',
            'rows'        => 14,
            'default'     => $this->default_brand_html(),
            'description' => 'The first footer column. Full HTML — logo/blurb/social/trust badges. Use the classes bac-footer-brand, bac-footer-blurb, bac-footer-social for the built-in styling.',
        ]);
        bac_add_theme_control($this, 'dark');
        $this->end_controls_section();

        /* ---- Link columns (h3 + ul). Add 1-4. ---- */
        $this->start_controls_section('cols', ['label' => 'Link columns']);
        $cr = new \Elementor\Repeater();
        $cr->add_control('title', ['label' => 'Column title (h3)', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Membership']);
        $cr->add_control('links', [
            'label'       => 'Links',
            'type'        => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'rows'        => 6,
            'default'     => "Plans & Pricing | /pricing/\nMember Perks | /benefits/\nHow It Works | /how-it-works/\nJoin the Club | /join/\nMember Login | #login-modal",
            'description' => 'One link per line as: Label | /url. Use #login-modal or #contact-modal to open those pop-ups.',
        ]);
        $this->add_control('columns', [
            'label'       => 'Link columns',
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $cr->get_controls(),
            'title_field' => '{{{ title }}}',
            'description' => 'Add or remove columns (1–4 recommended). The grid resizes automatically.',
            'default'     => [
                ['title' => 'Membership', 'links' => "Plans & Pricing | /pricing/\nMember Perks | /benefits/\nHow It Works | /how-it-works/\nJoin the Club | /join/\nMember Login | #login-modal"],
                ['title' => 'Products',   'links' => "T-Shirts | #\nHoodies & Sweatshirts | #\nPolos & Uniforms | #\nHats & Headwear | #\nBags & Accessories | #"],
                ['title' => 'Support',    'links' => "Track My Order | #\nFAQs | /faqs/\nReturn & Refund Policy | #"],
                ['title' => 'Company',    'links' => "About Us | /about/\nContact Us | #contact-modal\nBlog | /blog/\nFAQ | /faqs/"],
            ],
        ]);
        $this->end_controls_section();

        /* ---- Bottom bar ---- */
        $this->start_controls_section('bottom', ['label' => 'Bottom bar']);
        $this->add_control('copyright', [
            'label'   => 'Copyright',
            'type'    => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => '© ' . date('Y') . ' Branded Apparel Club. All rights reserved.',
        ]);
        $this->add_control('legal', [
            'label'       => 'Legal / policy links',
            'type'        => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'rows'        => 4,
            'default'     => "Privacy Policy | /privacy/\nTerms of Service | /terms/\nAccessibility | /accessibility/",
            'description' => 'One per line as: Label | /url',
        ]);
        $this->add_control('payments', [
            'label' => 'Show "We Accept" row', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes', 'default' => 'yes',
        ]);
        $this->add_control('pay_label', [
            'label' => '"We Accept" label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'We Accept', 'condition' => ['payments' => 'yes'],
        ]);
        $pr = new \Elementor\Repeater();
        $pr->add_control('icon', ['label' => 'Icon', 'type' => \Elementor\Controls_Manager::ICONS, 'default' => ['value' => 'fab fa-cc-visa', 'library' => 'fa-brands']]);
        $pr->add_control('label', ['label' => 'Accessible label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Visa']);
        $this->add_control('pay_icons', [
            'label'       => 'Payment icons',
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $pr->get_controls(),
            'title_field' => '{{{ label }}}',
            'condition'   => ['payments' => 'yes'],
            'default'     => [
                ['icon' => ['value' => 'fab fa-cc-visa', 'library' => 'fa-brands'], 'label' => 'Visa'],
                ['icon' => ['value' => 'fab fa-cc-mastercard', 'library' => 'fa-brands'], 'label' => 'Mastercard'],
                ['icon' => ['value' => 'fab fa-cc-discover', 'library' => 'fa-brands'], 'label' => 'Discover'],
                ['icon' => ['value' => 'fab fa-cc-amex', 'library' => 'fa-brands'], 'label' => 'American Express'],
                ['icon' => ['value' => 'fab fa-cc-paypal', 'library' => 'fa-brands'], 'label' => 'PayPal'],
                ['icon' => ['value' => 'fab fa-cc-diners-club', 'library' => 'fa-brands'], 'label' => 'Diners Club'],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $cols  = (array) $s['columns'];
        $fcols = max(1, count($cols));
        ?>
        <footer class="bac-block bac-footer <?php echo esc_attr(bac_theme_class($s, 'dark')); ?>" aria-labelledby="bac-footer-heading">
          <h2 class="bac-sr-only" id="bac-footer-heading">Footer</h2>
          <div class="bac-footer-inner">
            <div class="bac-footer-grid" style="--bac-fcols: <?php echo esc_attr($fcols); ?>;">

              <div class="bac-footer-brandcol">
                <?php echo do_shortcode((string) $s['brand_html']); // trusted admin HTML ?>
              </div>

              <?php foreach ($cols as $col) : ?>
                <nav class="bac-footer-col" aria-label="<?php echo esc_attr($col['title']); ?>">
                  <?php if (! empty($col['title'])) : ?><h3><?php echo esc_html($col['title']); ?></h3><?php endif; ?>
                  <ul role="list">
                    <?php foreach ($this->parse_links($col['links']) as $lnk) :
                        $modal = $this->modal_attr($lnk['url']);
                        $href  = $modal ? esc_attr($lnk['url']) : esc_url($lnk['url']);
                    ?>
                      <li><a href="<?php echo $href; ?>"<?php echo $modal; ?>><?php echo esc_html($lnk['label']); ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                </nav>
              <?php endforeach; ?>

            </div>

            <div class="bac-footer-bottom">
              <?php if (! empty($s['copyright'])) : ?><p class="bac-footer-copy"><?php echo esc_html($s['copyright']); ?></p><?php endif; ?>
              <?php $legal = $this->parse_links($s['legal']); if (! empty($legal)) : ?>
                <nav class="bac-footer-legal" aria-label="Legal">
                  <?php foreach ($legal as $lnk) :
                      $modal = $this->modal_attr($lnk['url']);
                      $href  = $modal ? esc_attr($lnk['url']) : esc_url($lnk['url']);
                  ?>
                    <a href="<?php echo $href; ?>"<?php echo $modal; ?>><?php echo esc_html($lnk['label']); ?></a>
                  <?php endforeach; ?>
                </nav>
              <?php endif; ?>
            </div>

            <?php if (! empty($s['payments']) && $s['payments'] === 'yes' && ! empty($s['pay_icons'])) : ?>
              <div class="bac-footer-pay-wrap">
                <?php if (! empty($s['pay_label'])) : ?><span class="bac-footer-pay-label"><?php echo esc_html($s['pay_label']); ?></span><?php endif; ?>
                <div class="bac-footer-pay">
                  <?php foreach ((array) $s['pay_icons'] as $p) : ?>
                    <span class="bac-footer-pay-ic" role="img" aria-label="<?php echo esc_attr($p['label']); ?>"><?php \Elementor\Icons_Manager::render_icon($p['icon'], ['aria-hidden' => 'true']); ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </footer>
        <?php
    }
}
