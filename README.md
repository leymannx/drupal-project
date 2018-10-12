# Composer Template for WordPress Projects

```
composer create-project leymannx/wordpress-project some-dir --stability stable --no-interaction
```

Replace `some-dir` with whatever directory name you wish.

### Usage

1. Run above command.
2. Point your vhost to `some-dir/web`.
3. Open site in browser and start WordPress installation as usual.

Optionally:

* After successful installation move `some-dir/web/wp-config.php` into `some-dir/wp-config/wp-config.php` and run `composer install` once again.
* Install [`leymannx/wp-cli-launcher`][1] to let the `wp` command use this project's `some-dir/bin/wp` from any location inside the project.

[1]: https://github.com/leymannx/wp-cli-launcher

### Custom plugins and themes

Custom stuff all goes inside `some-dir/wp-custom`. There are subfolders for themes and plugins. They will get symlinked automatically into the right location on every `composer install` run.

### wordpress.org plugins and themes

Plugins are added to your project by running `composer require wpackagist-plugin/plugin-name` and themes by `composer require wpackagist-theme/theme-name`. You then can enable them normally via WP-CLI or from the backend as usual.

```bash
cd some-dir
composer require wpackagist-plugin/wordpress-seo
```

### Recommendations

I strongly recommend to use [WP-CFM][2] to synchronize your WordPress configuration across all environments. After you activated the plugin you may want to have the config exported to/imported from `some-dir/wp-config/cfm`. So place the following code into a custom mu-plugin under `some-dir/wp-custom/mu-plugins/some-file.php` and run `composer install` once to have it symlinked into the right location. 

[2]: https://wordpress.org/plugins/wp-cfm/

```php
<?php
/*
  Plugin Name: Custom CFM config directory
  Description: Override default CFM config location.
  Version: 1.0
*/

// Override wp-cfm configuration directory.
add_filter('wpcfm_config_dir', function () {
  return dirname($_SERVER['DOCUMENT_ROOT']) . '/wp-config/cfm';
});
add_filter('wpcfm_config_url', function () {
  return dirname($_SERVER['DOCUMENT_ROOT']) . '/wp-config/cfm';
});
```

After you've set up your initial config in the backend you can export changes into some `.json` file(s) anytime either from the backend by running `wp config push some-config`. From now on it's as easy as putting the following commands into your deployment routine to have these changes synced across all environments.

```bash
../bin/wp plugin activate wp-cfm
../bin/wp config pull some-config
```

### Why?

Why another Composer template for WordPress? Well, at the time of writing this I found all other templates out there either too bloated or too minimalistic. I wanted to have a template that devides custom code and contrib code into dedicated locations (like in Drupal) and then have everything tied together into an absolutely normal WordPress.

And I wanted to be able to simply delete the whole `some-dir/web` folder and then just run `composer install` to have everything up and running again. Try it out yourself. That's a good feeling. With this template you shouldn't need to ever actually put a foot into the `some-dir/web` folder.

I achieved that by using symlinks that get created/removed automatically as `post-install-cmd` and `post-update-cmd` commands from Composer. It's a pitty that WordPress doesn't come with its own `composer.json`.

Why Composer at all? Because it sucks to have the whole monolithic codebase of WordPress pushed into a repo when all you really need are some few custom files whereas everything else should be just another dependency to pull.

### Credits

* [drupal-composer/drupal-project][3]
* [wodby/wordpress-composer][4]

[3]: https://github.com/drupal-composer/drupal-project
[4]: https://github.com/wodby/wordpress-composer
