<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Editable auth / account paths.
 *
 * Login, forgot-password, join, dashboard and affiliate links used to be
 * hard-coded. They're now editable at Settings → BAC Blocks Paths. Any field
 * left blank falls back to the original default, so nothing breaks out of the
 * box — you only set a value when you want to override it.
 */

/** Live defaults (computed at runtime because some depend on the site/login setup). */
function bac_path_defaults() {
    return [
        'login_action' => wp_login_url(),
        'forgot'       => wp_lostpassword_url(),
        'register'     => home_url('/membership-registration/'),
        'dashboard'    => admin_url('/'),
        'affiliate'    => home_url('/dashboard/'),
    ];
}

/** Human labels + help text for each editable path. */
function bac_path_fields() {
    return [
        'login_action' => ['Login form submits to', 'Where the Member Login form posts (the login handler).'],
        'forgot'       => ['Forgot password link', 'The "Forgot password?" link inside the login modal.'],
        'register'     => ['Join / Register link', 'The "Join the Club" link inside the login modal.'],
        'dashboard'    => ['Member dashboard', 'The Dashboard link in the logged-in account menu.'],
        'affiliate'    => ['Affiliate dashboard', 'The Affiliate Dashboard link in the account menu.'],
    ];
}

/** Return a configured path, or its default when the setting is blank. */
function bac_path($key) {
    $opts = get_option('bac_blocks_paths', []);
    if (is_array($opts) && ! empty($opts[$key])) {
        return $opts[$key];
    }
    $defaults = bac_path_defaults();
    return isset($defaults[$key]) ? $defaults[$key] : '';
}

add_action('admin_menu', function () {
    add_options_page(
        'BAC Blocks Paths',
        'BAC Blocks Paths',
        'manage_options',
        'bac-blocks-paths',
        'bac_paths_render_page'
    );
});

add_action('admin_init', function () {
    register_setting('bac_blocks_paths_group', 'bac_blocks_paths', [
        'type'              => 'array',
        'sanitize_callback' => 'bac_paths_sanitize',
        'default'           => [],
    ]);
});

/** Keep only known keys; blank = drop (so it falls back to default). */
function bac_paths_sanitize($input) {
    $out = [];
    if (! is_array($input)) { return $out; }
    foreach (array_keys(bac_path_fields()) as $key) {
        if (! isset($input[$key])) { continue; }
        $val = trim((string) $input[$key]);
        if ($val === '') { continue; }
        // Allow #anchors and relative or absolute URLs.
        $out[$key] = (strpos($val, '#') === 0) ? $val : esc_url_raw($val);
    }
    return $out;
}

function bac_paths_render_page() {
    if (! current_user_can('manage_options')) { return; }
    $opts = get_option('bac_blocks_paths', []);
    if (! is_array($opts)) { $opts = []; }
    $defaults = bac_path_defaults();
    ?>
    <div class="wrap">
      <h1>BAC Blocks Paths</h1>
      <p>Custom URLs for the login, account, and membership links. Leave a field
         blank to use the default shown in grey.</p>
      <form method="post" action="options.php">
        <?php settings_fields('bac_blocks_paths_group'); ?>
        <table class="form-table" role="presentation"><tbody>
        <?php foreach (bac_path_fields() as $key => $meta) :
            $val = isset($opts[$key]) ? $opts[$key] : '';
            $ph  = isset($defaults[$key]) ? $defaults[$key] : '';
        ?>
          <tr>
            <th scope="row">
              <label for="bac-path-<?php echo esc_attr($key); ?>"><?php echo esc_html($meta[0]); ?></label>
            </th>
            <td>
              <input type="text" class="regular-text" id="bac-path-<?php echo esc_attr($key); ?>"
                     name="bac_blocks_paths[<?php echo esc_attr($key); ?>]"
                     value="<?php echo esc_attr($val); ?>"
                     placeholder="<?php echo esc_attr($ph); ?>">
              <p class="description">
                <?php echo esc_html($meta[1]); ?>
                Default: <code><?php echo esc_html($ph); ?></code>
              </p>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody></table>
        <?php submit_button(); ?>
      </form>
    </div>
    <?php
}
