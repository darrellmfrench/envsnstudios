<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Card grid — heading + a repeatable set of image/title/text cards.
 * Covers "Why Branded Apparel Club", "Solutions", etc.
 */
class BAC_Cards_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_cards'; }
    public function get_title() { return 'BAC Card Grid'; }
    public function get_icon() { return 'eicon-gallery-grid'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['cards', 'grid', 'features', 'bac']; }

    protected function register_controls() {

        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', [
            'label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'Why Branded Apparel Club',
        ]);
        $this->add_control('heading', [
            'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => 'Most apparel companies focus on helping you place an order.',
        ]);
        $this->add_control('subtext', [
            'label' => 'Sub text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => 'Branded Apparel Club helps businesses get more from branded merchandise through ongoing support, smarter purchasing, and exclusive member advantages.',
        ]);
        $this->add_control('columns', [
            'label' => 'Columns', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'], 'default' => '4',
        ]);
        bac_add_ratio_control($this, '16 / 9');
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();

        $this->start_controls_section('items', ['label' => 'Cards']);

        $rep = new \Elementor\Repeater();
        $rep->add_control('image', ['label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true]]);
        $rep->add_control('title', [
            'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Our Approach',
        ]);
        $rep->add_control('text', [
            'label' => 'Text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => 'We look beyond the order to help you uncover opportunities where branded merchandise creates real value.',
        ]);
        $rep->add_control('link', ['label' => 'Link (optional)', 'type' => \Elementor\Controls_Manager::URL, 'dynamic' => ['active' => true]]);

        $this->add_control('cards', [
            'label'       => 'Cards',
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $rep->get_controls(),
            'title_field' => '{{{ title }}}',
            'default'     => [
                ['title' => 'Our Approach',    'text' => 'We look beyond the order to help you uncover opportunities where branded merchandise creates real value.'],
                ['title' => 'Business Credit', 'text' => 'Access a business credit line with net-30 payment terms that help your business purchase today while building business credit over time.'],
                ['title' => 'Merch Programs',  'text' => 'Launch branded webstores with on-demand fulfillment to create merch programs without investing in inventory.'],
                ['title' => 'Member Perks',    'text' => 'Enjoy exclusive savings, business benefits, and lifestyle perks designed to deliver value beyond every apparel order.'],
            ],
        ]);

        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $cols = ! empty($s['columns']) ? (int) $s['columns'] : 4;
        $ratio = bac_ratio_value($s, '16 / 9');
        $fit   = bac_fit_value($s);
        ?>
        <section class="bac-block bac-cards <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-cards-inner">
            <?php if (! empty($s['eyebrow']) || ! empty($s['heading']) || ! empty($s['subtext'])) : ?>
              <div class="bac-cards-head">
                <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
                <?php if (! empty($s['heading'])) : ?><h2 class="bac-section-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
                <?php if (! empty($s['subtext'])) : ?><p class="bac-section-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="bac-cards-grid" style="--bac-cols: <?php echo esc_attr($cols); ?>; --bac-img-ratio: <?php echo esc_attr($ratio); ?>; --bac-img-fit: <?php echo esc_attr($fit); ?>;">
              <?php foreach ((array) $s['cards'] as $card) :
                  $url = ! empty($card['link']['url']) ? $card['link']['url'] : '';
                  $tag = $url ? 'a' : 'div';
              ?>
                <<?php echo $tag; ?> class="bac-card"<?php echo $url ? ' href="' . esc_url($url) . '"' : ''; ?>>
                  <div class="bac-card-media">
                    <?php if (! empty($card['image']['url'])) : ?>
                      <?php echo bac_img_tag($card['image']['url'], $card['title']); ?>
                    <?php else : ?>
                      <span class="bac-media-placeholder small"></span>
                    <?php endif; ?>
                  </div>
                  <h3 class="bac-card-title"><?php echo esc_html($card['title']); ?></h3>
                  <p class="bac-card-text"><?php echo esc_html($card['text']); ?></p>
                </<?php echo $tag; ?>>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php
    }
}
