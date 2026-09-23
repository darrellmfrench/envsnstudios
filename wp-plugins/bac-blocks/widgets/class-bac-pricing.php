<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Pricing plans — membership/plan cards with price, feature list and CTA.
 */
class BAC_Pricing_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_pricing'; }
    public function get_title() { return 'BAC Pricing Plans'; }
    public function get_icon() { return 'eicon-price-table'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['pricing', 'plans', 'membership', 'bac']; }

    protected function register_controls() {
        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', ['label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Membership']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Membership Plans']);
        $this->add_control('subtext', ['label' => 'Sub text (optional)', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('columns', ['label' => 'Columns', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'], 'default' => '3']);
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();

        $this->start_controls_section('items', ['label' => 'Plans']);
        $rep = new \Elementor\Repeater();
        $rep->add_control('name', ['label' => 'Plan name', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Standard']);
        $rep->add_control('price', ['label' => 'Price', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '$69.99']);
        $rep->add_control('period', ['label' => 'Period', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '/ year']);
        $rep->add_control('blurb', ['label' => 'Short blurb', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Everything you need to get started.']);
        $rep->add_control('features', ['label' => 'Features (one per line)', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => "Member-only pricing\nArtwork kept on file\nNo reorder minimums\nDesign support", 'description' => 'One feature per line.']);
        $rep->add_control('cta_label', ['label' => 'Button text', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Join Now']);
        $rep->add_control('cta_url', ['label' => 'Button link', 'type' => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true], 'default' => ['url' => '/join/']]);
        $rep->add_control('featured', ['label' => 'Highlight this plan', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '']);
        $rep->add_control('badge', ['label' => 'Badge text (optional)', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('plans', [
            'label' => 'Plans', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rep->get_controls(), 'title_field' => '{{{ name }}}',
            'default' => [
                ['name' => 'Standard', 'price' => '$69.99', 'period' => '/ year', 'blurb' => 'Everything you need to get started.', 'features' => "Member-only pricing\nArtwork kept on file\nNo reorder minimums\nDesign support", 'cta_label' => 'Join Now', 'featured' => '', 'badge' => ''],
                ['name' => 'Pro', 'price' => '$149', 'period' => '/ year', 'blurb' => 'For growing teams that order often.', 'features' => "Everything in Standard\nPriority production\nDedicated account rep\nNet-30 credit terms", 'cta_label' => 'Join Now', 'featured' => 'yes', 'badge' => 'Most Popular'],
                ['name' => 'Enterprise', 'price' => 'Custom', 'period' => '', 'blurb' => 'Programs built around your brand.', 'features' => "Everything in Pro\nBranded webstore\nOn-demand fulfillment\nVolume pricing", 'cta_label' => 'Contact Us', 'featured' => '', 'badge' => ''],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $cols = ! empty($s['columns']) ? (int) $s['columns'] : 3;
        ?>
        <section class="bac-block bac-pricing <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-pricing-inner">
            <?php if (! empty($s['eyebrow']) || ! empty($s['heading']) || ! empty($s['subtext'])) : ?>
              <div class="bac-pricing-head">
                <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
                <?php if (! empty($s['heading'])) : ?><h2 class="bac-section-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
                <?php if (! empty($s['subtext'])) : ?><p class="bac-section-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
              </div>
            <?php endif; ?>
            <div class="bac-pricing-grid" style="--bac-cols: <?php echo esc_attr($cols); ?>;">
              <?php foreach ((array) $s['plans'] as $p) :
                  $featured = (! empty($p['featured']) && $p['featured'] === 'yes');
                  $feats = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $p['features'])));
              ?>
                <div class="bac-plan<?php echo $featured ? ' is-featured' : ''; ?>">
                  <?php if (! empty($p['badge'])) : ?><span class="bac-plan-badge"><?php echo esc_html($p['badge']); ?></span><?php endif; ?>
                  <h3 class="bac-plan-name"><?php echo esc_html($p['name']); ?></h3>
                  <div class="bac-plan-price"><span class="bac-plan-amount"><?php echo esc_html($p['price']); ?></span><?php if (! empty($p['period'])) : ?> <span class="bac-plan-period"><?php echo esc_html($p['period']); ?></span><?php endif; ?></div>
                  <?php if (! empty($p['blurb'])) : ?><p class="bac-plan-blurb"><?php echo esc_html($p['blurb']); ?></p><?php endif; ?>
                  <ul class="bac-plan-features">
                    <?php foreach ($feats as $feat) : ?>
                      <li><svg class="bac-plan-check" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg><span><?php echo esc_html($feat); ?></span></li>
                    <?php endforeach; ?>
                  </ul>
                  <?php if (! empty($p['cta_label'])) : ?>
                    <a class="bac-btn <?php echo $featured ? 'bac-btn-primary' : 'bac-btn-ghost'; ?>" href="<?php echo esc_url($p['cta_url']['url'] ?: '#'); ?>"><?php echo esc_html($p['cta_label']); ?></a>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php
    }
}
