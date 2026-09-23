<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Comparison table — features by plan. Cell values: "yes"/"no" render as
 * check / dash, anything else renders as text.
 */
class BAC_Compare_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_compare'; }
    public function get_title() { return 'BAC Comparison Table'; }
    public function get_icon() { return 'eicon-table'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['compare', 'table', 'plans', 'bac']; }

    private function cell($val) {
        $v = strtolower(trim((string) $val));
        if ($v === 'yes' || $v === 'true' || $v === '✓') {
            return '<svg class="bac-cmp-yes" width="18" height="18" viewBox="0 0 24 24" aria-label="Included"><path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }
        if ($v === 'no' || $v === 'false' || $v === '-' || $v === '') {
            return '<span class="bac-cmp-no" aria-label="Not included">&ndash;</span>';
        }
        return '<span class="bac-cmp-txt">' . esc_html($val) . '</span>';
    }

    protected function register_controls() {
        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', ['label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Compare']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Compare Membership Plans']);
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();

        $this->start_controls_section('cols', ['label' => 'Plan columns']);
        $cr = new \Elementor\Repeater();
        $cr->add_control('name', ['label' => 'Plan name', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Standard']);
        $cr->add_control('featured', ['label' => 'Highlight', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '']);
        $this->add_control('plans', [
            'label' => 'Plans (max 4 shown)', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $cr->get_controls(), 'title_field' => '{{{ name }}}',
            'default' => [['name' => 'Standard', 'featured' => ''], ['name' => 'Pro', 'featured' => 'yes'], ['name' => 'Enterprise', 'featured' => '']],
        ]);
        $this->end_controls_section();

        $this->start_controls_section('rows_section', ['label' => 'Feature rows']);
        $rr = new \Elementor\Repeater();
        $rr->add_control('feature', ['label' => 'Feature', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Member pricing', 'label_block' => true]);
        $rr->add_control('v1', ['label' => 'Plan 1 (yes/no/text)', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'yes']);
        $rr->add_control('v2', ['label' => 'Plan 2', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'yes']);
        $rr->add_control('v3', ['label' => 'Plan 3', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'yes']);
        $rr->add_control('v4', ['label' => 'Plan 4', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('rows', [
            'label' => 'Rows', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rr->get_controls(), 'title_field' => '{{{ feature }}}',
            'default' => [
                ['feature' => 'Member pricing', 'v1' => 'yes', 'v2' => 'yes', 'v3' => 'yes'],
                ['feature' => 'Reorder minimums', 'v1' => 'None', 'v2' => 'None', 'v3' => 'None'],
                ['feature' => 'Priority production', 'v1' => 'no', 'v2' => 'yes', 'v3' => 'yes'],
                ['feature' => 'Net-30 credit terms', 'v1' => 'no', 'v2' => 'yes', 'v3' => 'yes'],
                ['feature' => 'Branded webstore', 'v1' => 'no', 'v2' => 'no', 'v3' => 'yes'],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $plans = array_slice((array) $s['plans'], 0, 4);
        $n = count($plans);
        ?>
        <section class="bac-block bac-cmp <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-cmp-inner">
            <?php if (! empty($s['eyebrow']) || ! empty($s['heading'])) : ?>
              <div class="bac-cmp-headwrap">
                <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
                <?php if (! empty($s['heading'])) : ?><h2 class="bac-section-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
              </div>
            <?php endif; ?>
            <div class="bac-cmp-scroll">
              <table class="bac-cmp-table">
                <thead>
                  <tr>
                    <th scope="col" class="bac-cmp-corner"></th>
                    <?php foreach ($plans as $p) : ?>
                      <th scope="col"<?php echo (! empty($p['featured']) && $p['featured'] === 'yes') ? ' class="is-featured"' : ''; ?>><?php echo esc_html($p['name']); ?></th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ((array) $s['rows'] as $row) : ?>
                    <tr>
                      <th scope="row"><?php echo esc_html($row['feature']); ?></th>
                      <?php for ($i = 1; $i <= $n; $i++) :
                          $key = 'v' . $i;
                          $featured = (! empty($plans[$i - 1]['featured']) && $plans[$i - 1]['featured'] === 'yes');
                      ?>
                        <td<?php echo $featured ? ' class="is-featured"' : ''; ?>><?php echo $this->cell(isset($row[$key]) ? $row[$key] : ''); ?></td>
                      <?php endfor; ?>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
        <?php
    }
}
