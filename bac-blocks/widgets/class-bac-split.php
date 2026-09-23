<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Split feature — image on one side, text + numbered goal tags on the other.
 * Covers "The Power of Branded Merchandise".
 */
class BAC_Split_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'bac_split'; }
    public function get_title() { return 'BAC Split Feature'; }
    public function get_icon() { return 'eicon-columns'; }
    public function get_categories() { return ['bac-blocks']; }
    public function get_keywords() { return ['split', 'feature', 'bac']; }

    protected function register_controls() {

        $this->start_controls_section('content', ['label' => 'Content']);
        $this->add_control('eyebrow', [
            'label' => 'Small label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'The Power of Branded Merchandise',
        ]);
        $this->add_control('heading', [
            'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => 'Branded apparel is often just the beginning.',
        ]);
        $this->add_control('subtext', [
            'label' => 'Sub text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'dynamic' => ['active' => true],
            'default' => 'When used strategically, branded merchandise can strengthen your professional image, build company culture, increase visibility, create new revenue opportunities, and simplify the way your business manages branded products.',
        ]);
        $this->add_control('tags_label', [
            'label' => 'Tags intro', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true],
            'default' => 'What are you trying to accomplish?',
        ]);
        $this->add_control('image', ['label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => ['active' => true]]);
        bac_add_image_a11y_controls($this, 'image');
        $this->add_control('image_side', [
            'label' => 'Image side', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => ['left' => 'Left', 'right' => 'Right'], 'default' => 'left',
        ]);
        bac_add_theme_control($this, 'light');
        $this->end_controls_section();

        $this->start_controls_section('tags_section', ['label' => 'Goal tags']);
        $rep = new \Elementor\Repeater();
        $rep->add_control('label', ['label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT, 'dynamic' => ['active' => true], 'default' => 'Professional Image']);
        $this->add_control('tags', [
            'label' => 'Tags', 'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $rep->get_controls(), 'title_field' => '{{{ label }}}',
            'default' => [
                ['label' => 'Professional Image'],
                ['label' => 'Stronger Culture'],
                ['label' => 'Increase Visibility'],
                ['label' => 'Generate Revenue'],
                ['label' => 'Simplify Operations'],
            ],
        ]);
        $this->end_controls_section();
        bac_add_spacing_controls($this);
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $media_first = ($s['image_side'] !== 'right');
        ?>
        <section class="bac-block bac-split <?php echo esc_attr(bac_theme_class($s, 'light')); ?>">
          <div class="bac-split-inner <?php echo $media_first ? 'media-left' : 'media-right'; ?>">
            <div class="bac-split-media">
              <?php if (! empty($s['image']['url'])) :
                  $split_ah = (! empty($s['image_aria_hidden']) && $s['image_aria_hidden'] === 'yes');
                  echo bac_img_tag($s['image']['url'], isset($s['image_alt']) ? $s['image_alt'] : '', ['aria_hidden' => $split_ah]);
              else : ?>
                <div class="bac-media-placeholder"><span>Image</span></div>
              <?php endif; ?>
            </div>
            <div class="bac-split-text">
              <?php if (! empty($s['eyebrow'])) : ?><p class="bac-eyebrow"><?php echo esc_html($s['eyebrow']); ?></p><?php endif; ?>
              <?php if (! empty($s['heading'])) : ?><h2 class="bac-section-title" style="text-align:left;margin-left:0;"><?php echo esc_html($s['heading']); ?></h2><?php endif; ?>
              <?php if (! empty($s['subtext'])) : ?><p class="bac-section-sub" style="text-align:left;margin-left:0;max-width:none;"><?php echo esc_html($s['subtext']); ?></p><?php endif; ?>
              <?php if (! empty($s['tags_label'])) : ?><p class="bac-tags-label"><?php echo esc_html($s['tags_label']); ?></p><?php endif; ?>
              <div class="bac-tags">
                <?php $n = 1; foreach ((array) $s['tags'] as $tag) : ?>
                  <div class="bac-tag"><span class="bac-tag-num"><?php echo esc_html(sprintf('%02d', $n++)); ?></span><span class="bac-tag-label"><?php echo esc_html($tag['label']); ?></span></div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </section>
        <?php
    }
}
