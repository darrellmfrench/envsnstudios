<?php
if (! defined('ABSPATH')) { exit; }

/**
 * BAC_GitHub_Updater
 *
 * Minimal, self-contained WordPress plugin updater that pulls new versions from
 * a GitHub repository's Releases. No external library, no wordpress.org.
 *
 * Monorepo-friendly: one repo can hold several plugins, each in its own folder
 * and released independently using a per-plugin tag prefix. For example, in the
 * repo "envsnstudios":
 *
 *   envsnstudios/
 *     bac-blocks/        -> tags like  bac-blocks-0.9.15
 *     another-plugin/    -> tags like  another-plugin-1.4.0
 *
 * Each plugin filters the repo's releases down to the ones whose tag starts with
 * its own prefix, so plugins update independently.
 *
 * Config (from the main plugin file):
 *   slug       : plugin folder slug, e.g. 'bac-blocks'
 *   owner      : GitHub account/org, e.g. 'darrellmfrench'
 *   repo       : GitHub repository name, e.g. 'envsnstudios'
 *   version    : current installed version (e.g. BAC_BLOCKS_VER)
 *   asset      : preferred release-asset filename (e.g. 'bac-blocks.zip'); falls
 *                back to GitHub's source zipball if the asset isn't attached.
 *   tag_prefix : per-plugin release-tag prefix (e.g. 'bac-blocks-'). Leave empty
 *                only for a single-plugin repo where every release is this plugin.
 *
 * Shared across every ENVSN plugin: the class name is generic and guarded so
 * multiple plugins can each bundle an identical copy without colliding.
 */
if (! class_exists('ENVSN_GitHub_Updater')) :
class ENVSN_GitHub_Updater {

    private $file;
    private $basename;
    private $slug;
    private $owner;
    private $repo;
    private $version;
    private $asset_name;
    private $tag_prefix;
    private $cache_key;
    private $cache_ttl;
    private static $notice_hooked = false;

    public function __construct($file, array $args) {
        $this->file       = $file;
        $this->basename   = plugin_basename($file);
        $this->slug       = $args['slug'];
        $this->owner      = $args['owner'];
        $this->repo       = $args['repo'];
        $this->version    = $args['version'];
        $this->asset_name = isset($args['asset']) ? $args['asset'] : '';
        $this->tag_prefix = isset($args['tag_prefix']) ? $args['tag_prefix'] : '';
        // Cache the release LIST once per repo — shared by every plugin in it.
        $this->cache_key  = 'bac_gh_rels_' . md5($this->owner . '/' . $this->repo);
        $this->cache_ttl  = 1 * HOUR_IN_SECONDS;

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('upgrader_source_selection', [$this, 'fix_source_dir'], 10, 4);
        add_action('upgrader_process_complete', [$this, 'clear_cache'], 10, 0);

        // "Check for updates" link on the plugin row + its handler.
        add_filter('plugin_action_links_' . $this->basename, [$this, 'row_link']);
        add_action('admin_init', [$this, 'maybe_force_check']);
        if (! self::$notice_hooked) {
            self::$notice_hooked = true;
            add_action('admin_notices', [__CLASS__, 'checked_notice']);
        }
    }

    /** Append a "Check for updates" link to this plugin's row. */
    public function row_link($links) {
        $url = wp_nonce_url(
            add_query_arg('bac_gh_check', $this->slug, admin_url('plugins.php')),
            'bac_gh_check_' . $this->slug
        );
        $links[] = '<a href="' . esc_url($url) . '">Check for updates</a>';
        return $links;
    }

    /** Clear the cache + WordPress's update list, then re-check immediately. */
    public function maybe_force_check() {
        if (empty($_GET['bac_gh_check']) || $_GET['bac_gh_check'] !== $this->slug) { return; }
        if (! current_user_can('update_plugins')) { return; }
        check_admin_referer('bac_gh_check_' . $this->slug);

        delete_transient($this->cache_key);        // drop our cached release list
        delete_site_transient('update_plugins');   // force WordPress to rebuild its update list

        wp_safe_redirect(add_query_arg('bac_gh_checked', '1', admin_url('plugins.php')));
        exit;
    }

    /** One-time success notice after a manual check. */
    public static function checked_notice() {
        if (empty($_GET['bac_gh_checked'])) { return; }
        echo '<div class="notice notice-success is-dismissible"><p>Checked GitHub for plugin updates. Any available update now shows below.</p></div>';
    }

    /** Fetch + cache the repo's releases list (one API call serves all plugins). */
    private function get_releases() {
        $cached = get_transient($this->cache_key);
        if ($cached !== false) {
            return is_array($cached) ? $cached : [];
        }

        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/releases?per_page=100";
        $res = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'Accept'     => 'application/vnd.github+json',
                'User-Agent' => 'BAC-Blocks-Updater',
            ],
        ]);

        if (is_wp_error($res) || (int) wp_remote_retrieve_response_code($res) !== 200) {
            set_transient($this->cache_key, [], 30 * MINUTE_IN_SECONDS); // brief negative cache
            return [];
        }

        $data = json_decode(wp_remote_retrieve_body($res), true);
        if (! is_array($data)) { $data = []; }
        set_transient($this->cache_key, $data, $this->cache_ttl);
        return $data;
    }

    /** Turn a release tag into a plain version (strip this plugin's prefix + any leading v). */
    private function version_from_tag($tag) {
        $tag = (string) $tag;
        if ($this->tag_prefix !== '' && strpos($tag, $this->tag_prefix) === 0) {
            $tag = substr($tag, strlen($this->tag_prefix));
        }
        return ltrim($tag, 'vV');
    }

    /** Does this release belong to this plugin? */
    private function release_matches($release) {
        if (! empty($release['draft']) || ! empty($release['prerelease'])) {
            return false;
        }
        if (empty($release['tag_name'])) {
            return false;
        }
        if ($this->tag_prefix !== '') {
            return strpos($release['tag_name'], $this->tag_prefix) === 0;
        }
        return true;
    }

    /** Pick this plugin's newest matching release. */
    private function select_release() {
        $best = null;
        $best_ver = '0.0.0';
        foreach ($this->get_releases() as $release) {
            if (! is_array($release) || ! $this->release_matches($release)) {
                continue;
            }
            $ver = $this->version_from_tag($release['tag_name']);
            if ($ver !== '' && version_compare($ver, $best_ver, '>')) {
                $best = $release;
                $best_ver = $ver;
            }
        }
        return $best;
    }

    /** Prefer the attached release asset zip; fall back to the source zipball. */
    private function package_url(array $release) {
        if (! empty($this->asset_name) && ! empty($release['assets'])) {
            foreach ($release['assets'] as $asset) {
                if (isset($asset['name']) && $asset['name'] === $this->asset_name) {
                    return $asset['browser_download_url'];
                }
            }
        }
        return isset($release['zipball_url']) ? $release['zipball_url'] : '';
    }

    /** Inject an available update into the plugins update transient. */
    public function check_update($transient) {
        if (! is_object($transient) || empty($transient->checked)) {
            return $transient;
        }

        $release = $this->select_release();
        if (! $release) { return $transient; }

        $new = $this->version_from_tag($release['tag_name']);
        if (version_compare($new, $this->version, '<=')) {
            unset($transient->response[$this->basename]);
            return $transient;
        }

        $package = $this->package_url($release);
        if (! $package) { return $transient; }

        $transient->response[$this->basename] = (object) [
            'slug'        => $this->slug,
            'plugin'      => $this->basename,
            'new_version' => $new,
            'url'         => "https://github.com/{$this->owner}/{$this->repo}",
            'package'     => $package,
        ];
        return $transient;
    }

    /** Fill the "View details" popup. */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') { return $result; }
        if (empty($args->slug) || $args->slug !== $this->slug) { return $result; }

        $release = $this->select_release();
        if (! $release) { return $result; }

        $changelog = ! empty($release['body'])
            ? nl2br(esc_html($release['body']))
            : 'See the GitHub releases page for details.';

        return (object) [
            'name'          => $this->slug,
            'slug'          => $this->slug,
            'version'       => $this->version_from_tag($release['tag_name']),
            'author'        => '<a href="https://github.com/' . esc_attr($this->owner) . '">ENVSN Studios</a>',
            'homepage'      => "https://github.com/{$this->owner}/{$this->repo}",
            'download_link' => $this->package_url($release),
            'trunk'         => $this->package_url($release),
            'sections'      => [
                'description' => 'Custom, client-editable Elementor widgets.',
                'changelog'   => $changelog,
            ],
        ];
    }

    /**
     * GitHub source zipballs extract to a folder like "owner-repo-<sha>". Rename
     * the extracted folder to the plugin slug so WordPress updates in place.
     * (With a properly-named release asset the folder already matches — no-op.)
     */
    public function fix_source_dir($source, $remote_source, $upgrader, $hook_extra = null) {
        if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->basename) {
            return $source;
        }
        global $wp_filesystem;
        if (! $wp_filesystem) { return $source; }

        $desired = trailingslashit($remote_source) . $this->slug;
        if (untrailingslashit($source) === untrailingslashit($desired)) {
            return $source;
        }
        if ($wp_filesystem->move(untrailingslashit($source), untrailingslashit($desired), true)) {
            return trailingslashit($desired);
        }
        return $source;
    }

    public function clear_cache() {
        delete_transient($this->cache_key);
    }
}
endif;
