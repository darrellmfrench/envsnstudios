<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Newsletter signup — heading + inline email form.
 * Point the Form action at your Klaviyo / Mailchimp endpoint.
 */
class BAC_Newsletter_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_newsletter'; }
    public function get_title() { return 'BAC Newsletter Signup'; }
    public function get_icon() { return 'eicon-email-field'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['newsletter', 'email', 'subscribe', 'signup', 'bac']; }

    protected function register_controls() {
        $this->start_controls_section('content', ['label' => 'Content']);
        $this->add_control('eyebrow', ['label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Stay On Brand']);
        $this->add_control('heading', ['label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Get Practical Branding Tips']);
        $this->add_control('subtext', ['label' => 'Sub text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => 'Product drops, special offers, and business strategies delivered straight to your inbox.']);
        $this->add_control('placeholder', ['label' => 'Field placeholder', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Enter your email']);
        $this->add_control('button', ['label' => 'Button text', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Subscribe']);
        bac_add_theme_control($this, 'dark');
        $this->end_controls_section();

        $this->start_controls_section('form', ['label' => 'Form']);
        $this->add_control('action', [
            'label' => 'Form action URL', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '',
            'description' => 'Your Klaviyo / Mailchimp POST URL. Leave blank to collect nothing (placeholder).',
        ]);
        $this->add_control('field_name', [
            'label' => 'Email field name', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'email',
            'description' => 'Mailchimp uses EMAIL; Klaviyo uses email. Match your provider.',
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $action = ! empty($s['action']) ? esc_url($s['action']) : '';
        $field  = ! empty($s['field_name']) ? sanitize_key($s['field_name']) : 'email';
        ?>
        <section class="bac-block bac-nl <?php echo esc_attr(bac_theme_class($s, 'dark')); ?>">
          <div class="bac-nl-inner">
            <div class="bac-nl-text">
              <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
              <?php if (! empty($s['heading'])) : ?><h2 class="bac-nl-title"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
              <?php if (! empty($s['subtext'])) : ?><p class="bac-nl-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
            </div>
            <form class="bac-nl-form"<?php echo $action ? ' method="post" action="' . $action . '"' : ''; ?>>
              <label class="bac-nl-label" for="bac-nl-email">Email address</label>
              <input class="bac-nl-input" id="bac-nl-email" type="email" name="<?php echo esc_attr($field); ?>" placeholder="<?php echo esc_attr($s['placeholder']); ?>" autocomplete="email" required>
              <button class="bac-btn bac-btn-primary" type="submit"><?php echo esc_html($s['button']); ?></button>
            </form>
          </div>
        </section>
        <?php
    }
}
