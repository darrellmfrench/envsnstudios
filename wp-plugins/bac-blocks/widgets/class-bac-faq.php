<?php
if (! defined('ABSPATH')) { exit; }

/**
 * FAQ accordion — accessible <button>/aria-expanded accordion with optional
 * FAQPage JSON-LD schema for SEO rich results.
 */
class BAC_Faq_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_faq'; }
    public function get_title() { return 'BAC FAQ Accordion'; }
    public function get_icon() { return 'eicon-help-o'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['faq', 'accordion', 'questions', 'bac']; }

    protected function register_controls() {

        $this->start_controls_section('head', ['label' => 'Heading']);
        $this->add_control('eyebrow', ['label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Support']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Frequently Asked Questions']);
        $this->add_control('subtext', ['label' => 'Sub text (optional)', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('schema', [
            'label' => 'Add FAQ schema (SEO)', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => 'Yes', 'label_off' => 'No', 'return_value' => 'yes', 'default' => 'yes',
            'description' => 'Outputs FAQPage structured data so answers can show as Google rich results.',
        ]);
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();

        $this->start_controls_section('items', ['label' => 'Questions']);
        $rep = new \Elementor\Repeater();
        $rep->add_control('question', ['label' => 'Question', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'How do I become a member?', 'label_block' => true]);
        $rep->add_control('answer', ['label' => 'Answer', 'type' => \Elementor\Controls_Manager::WYSIWYG, 'dynamic' => ['active' => true], 'default' => 'Pick a membership tier, create your account, and log in to your dashboard in minutes.']);
        $rep->add_control('open', [
            'label' => 'Open by default', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes', 'default' => '',
        ]);
        $this->add_control('faqs', [
            'label' => 'Questions', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rep->get_controls(), 'title_field' => '{{{ question }}}',
            'default' => [
                ['question' => 'How do I become a member?', 'answer' => 'Pick a membership tier, create your account, and log in to your member dashboard in minutes.'],
                ['question' => 'What is the turnaround time?', 'answer' => 'Most orders ship within 48 to 72 hours. Rush options are available.'],
                ['question' => 'Is there a minimum order?', 'answer' => 'No minimums on reorders. Order exactly what you need, when you need it.'],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s   = $this->get_settings_for_display();
        $uid = $this->get_id();
        $faqs = (array) $s['faqs'];
        ?>
        <section class="bac-block bac-faq <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-faq-inner">
            <?php if (! empty($s['eyebrow']) || ! empty($s['heading']) || ! empty($s['subtext'])) : ?>
              <div class="bac-faq-head">
                <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
                <?php if (! empty($s['heading'])) : ?><h2 class="bac-section-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
                <?php if (! empty($s['subtext'])) : ?><p class="bac-section-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
              </div>
            <?php endif; ?>
            <div class="bac-faq-list">
              <?php foreach ($faqs as $i => $faq) :
                  $open = (! empty($faq['open']) && $faq['open'] === 'yes');
              ?>
                <details class="bac-faq-item"<?php echo $open ? ' open' : ''; ?>>
                  <summary class="bac-faq-trigger">
                    <span><?php echo esc_html($faq['question']); ?></span>
                    <svg class="bac-faq-icon" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </summary>
                  <div class="bac-faq-panel">
                    <div class="bac-faq-answer"><?php echo wp_kses_post($faq['answer']); ?></div>
                  </div>
                </details>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php
        if (! empty($s['schema']) && $s['schema'] === 'yes' && ! empty($faqs)) {
            $entities = [];
            foreach ($faqs as $faq) {
                if (empty($faq['question'])) { continue; }
                $entities[] = [
                    '@type' => 'Question',
                    'name'  => wp_strip_all_tags($faq['question']),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => wp_strip_all_tags($faq['answer']),
                    ],
                ];
            }
            if ($entities) {
                $schema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $entities];
                echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
            }
        }
    }
}
