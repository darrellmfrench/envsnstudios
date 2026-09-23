# ENVSN Studios — WordPress Plugins (monorepo)

One repository, several self-updating WordPress plugins. Each plugin lives in
its own top-level folder and updates on client sites straight from this repo's
**GitHub Releases** — no manual re-uploading.

## Structure

```
envsnstudios/
  bac-blocks/                 A plugin (installs as wp-content/plugins/bac-blocks)
    bac-blocks.php            Main file: header, version, updater bootstrap
    includes/
      class-bac-github-updater.php   Shared self-updater (guarded class)
    widgets/  assets/
  <next-plugin>/              Add more plugins here, same pattern
  .github/workflows/release.yml   Builds + attaches <folder>.zip on release
  README.md  .gitignore
```

## Release convention (important)

Because one repo holds many plugins, each release is tied to a plugin by its
**tag prefix**:

```
<plugin-folder>-<version>
```

Examples: `bac-blocks-0.9.16`, `another-plugin-1.4.0`. A leading `v` is optional
(`bac-blocks-v0.9.16`). Each plugin only sees releases whose tag starts with its
own folder name, so plugins update independently.

## Publishing an update to a plugin

1. Edit the plugin's files.
2. Bump the version in **two places** in that plugin's main PHP file — the
   `* Version:` header and the version constant — and keep them equal.
3. Commit and push to `main`.
4. Create a **GitHub Release** with the tag `<plugin-folder>-<version>`
   (e.g. `bac-blocks-0.9.16`) and publish it (not a draft/pre-release).
5. The Action zips that plugin's folder and attaches `<plugin-folder>.zip`.
   Every site updates within ~6 hours, or instantly via **Check for updates**.

## Adding a NEW plugin to the monorepo

1. Create a new top-level folder named after the plugin slug (e.g. `envsn-forms/`).
2. Copy `includes/class-bac-github-updater.php` into it (the class is generic
   and guarded, so multiple plugins can each ship a copy safely).
3. In the new plugin's main file, add the `Update URI` header and boot the
   updater with that plugin's own settings:
   ```php
   require_once plugin_dir_path(__FILE__) . 'includes/class-bac-github-updater.php';
   new ENVSN_GitHub_Updater(__FILE__, [
       'slug'       => 'envsn-forms',
       'owner'      => 'darrellmfrench',
       'repo'       => 'envsnstudios',
       'version'    => ENVSN_FORMS_VER,
       'asset'      => 'envsn-forms.zip',
       'tag_prefix' => 'envsn-forms-',
   ]);
   ```
4. Release it with a tag like `envsn-forms-1.0.0`. The Action already handles
   any folder — no workflow changes needed.

## First-time install (once per site, per plugin)

The updater must be present before auto-updates can work, so install the plugin
manually the first time: download its zip from the Release (or build locally),
then WordPress → Plugins → Add New → Upload Plugin → Activate. After that,
updates arrive automatically.

## How updates work

Each plugin bundles a small updater (no external library, no wordpress.org). On
each site it reads this repo's releases, filters to its own tag prefix, and if
the newest matching tag is higher than the installed version, offers a one-click
update. The releases list is cached ~6 hours to stay well under GitHub's API
limits; one API call serves every ENVSN plugin on the site.

## Requirements

- WordPress + Elementor for the widget plugins (Dynamic Tags for custom fields
  needs Elementor Pro). PHP 7.4+.
