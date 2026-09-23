<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Inner page header — compact banner (title + breadcrumb + optional bg image)
 * for interior pages like About, Pricing, Benefits.
 */
class BAC_PageHeader_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_pageheader'; }
    public function get_title() { return 'BAC Page Header'; }
    public function get_icon() { return 'eicon-header'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['page header', 'banner', 'breadcrumb', 'title', 'bac']; }

    protected function register_controls() {
        $this->start_controls_section('content', ['label' => 'Content']);
        $this->add_control('title', ['label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => '', 'description' => 'Leave blank to use the current page title.']);
        $this->add_control('subtext', ['label' => 'Sub text (optional)', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true], 'default' => '']);
        $this->add_control('breadcrumb', ['label' => 'Show breadcrumb', 'type' => \Elementor\Controls_Manager::SWITCHER, 'label_on' => 'Yes', 'label_off' => 'No', 'return_value' => 'yes', 'default' => 'yes']);
        $this->add_control('bg_image', ['label' => 'Background image (optional)', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true]]);
        bac_add_theme_control($this, 'dark');
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $title = ! empty($s['title']) ? $s['title'] : get_the_title();
        $has_bg = ! empty($s['bg_image']['url']);
        $bg = $has_bg ? ' style="background-image:url(' . esc_url($s['bg_image']['url']) . ');"' : '';
        ?>
        <section class="bac-block bac-ph <?php echo esc_attr(bac_theme_class($s, 'dark')); ?><?php echo $has_bg ? ' has-bg' : ''; ?>"<?php echo $bg; ?>>
          <div class="bac-ph-inner">
            <?php if (! empty($s['breadcrumb']) && $s['breadcrumb'] === 'yes') : ?>
              <nav class="bac-ph-crumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page"><?php echo esc_html($title); ?></span>
              </nav>
            <?php endif; ?>
            <?php if (! empty($title)) : ?><h1 class="bac-ph-title"><?php echo esc_html($title); ?></h1><?php endif; ?>
            <?php if (! empty($s['subtext'])) : ?><p class="bac-ph-sub"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
          </div>
        </section>
        <?php
    }
}
