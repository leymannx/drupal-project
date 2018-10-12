<?php
/**
 * @file
 * Contains \WordpressProject\composer\ScriptHandler.
 */

namespace WordpressProject\composer;

require_once __DIR__ . '/../../vendor/autoload.php';

use Composer\Script\Event;
use WordpressFinder\WordpressFinder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ScriptHandler {

  public static function createRequiredFiles(Event $event) {

    $fs = new Filesystem();
    $wordpressFinder = new WordpressFinder();
    $wordpressFinder->locateRoot(getcwd());

    $composerRoot = $wordpressFinder->getComposerRoot();

    $dirs = [
      'mu-plugins',
      'plugins',
      'themes',
    ];

    // Create folders for custom plugins and themes.
    foreach ($dirs as $dir) {
      if (!$fs->exists($composerRoot . '/wp-custom/' . $dir)) {

        $fs->mkdir($composerRoot . '/wp-custom/' . $dir);
        $fs->touch($composerRoot . '/wp-custom/' . $dir . '/.gitkeep');
        $event->getIO()
          ->write("  *  Created $composerRoot/$dir directory");
      }
    }

    // Create the uploads directory with chmod 0777.
    if (!$fs->exists($composerRoot . '/uploads')) {

      $fs->mkdir($composerRoot . '/uploads', 0777);
      $event->getIO()
        ->write("  *  Created $composerRoot/uploads directory with chmod 0777");
    }
  }

  public static function createSymlinks(Event $event) {

    $wordpressFinder = new WordpressFinder();
    $wordpressFinder->locateRoot(getcwd());

    $webRoot = $wordpressFinder->getWebRoot();
    $composerRoot = $wordpressFinder->getComposerRoot();
    $pluginsDir = $wordpressFinder->getPluginsDir();
    $themesDir = $wordpressFinder->getThemesDir();
    $muPluginsDir = $wordpressFinder->getMuPluginsDir();

    $custom_pluginsDir = $composerRoot . '/wp-custom/plugins';
    $custom_themesDir = $composerRoot . '/wp-custom/themes';
    $custom_muPluginsDir = $composerRoot . '/wp-custom/mu-plugins';

    $dirsToCheck = [
      $pluginsDir          => 'plugins',
      $custom_pluginsDir   => 'plugins',
      $themesDir           => 'themes',
      $custom_themesDir    => 'themes',
      $muPluginsDir        => 'mu-plugins',
      $custom_muPluginsDir => 'mu-plugins',
    ];

    $fs = new Filesystem();

    foreach ($dirsToCheck as $dir => $type) {
      if ($fs->exists($dir)) {

        $finder = new Finder();

        // Symlink wp-custom and wp-vendor directories into web/wp-content.
        foreach ($finder->in($dir)->depth('== 0')->directories()
          as $path) {

          $name = basename($path);

          if ($fs->exists($dir . '/' . $name)
            && !$fs->exists($webRoot . '/wp-content/' . $type . '/' . $name)) {

            $fs->symlink($path, $webRoot . '/wp-content/' . $type . '/' . $name);
            $event->getIO()
              ->write("  *  Symlinked $path to $webRoot/wp-content/$type/$name");
          }
        }

        // Symlink wp-custom and wp-vendor single PHP files into web/wp-content.
        foreach ($finder->in($dir)->depth('== 0')->files()->name('*.php')
          as $path) {

          $name = basename($path);

          if ($fs->exists($dir . '/' . $name)
            && !$fs->exists($webRoot . '/wp-content/' . $type . '/' . $name)) {

            $fs->symlink($path, $webRoot . '/wp-content/' . $type . '/' . $name);
            $event->getIO()
              ->write("  *  Symlinked $path to $webRoot/wp-content/$type/$name");
          }
        }
      }
    }

    // Symlink uploads folder into web/wp-content/uploads.
    if ($fs->exists($composerRoot . '/uploads')
      && !$fs->exists($webRoot . '/wp-content/uploads')) {

      $fs->symlink($composerRoot . '/uploads', $webRoot . '/wp-content/uploads');
      $event->getIO()
        ->write("  *  Symlinked $composerRoot/uploads to $webRoot/wp-content/uploads");
    }

    // Symlink wp-config/wp-config.php into webroot.
    if ($fs->exists($composerRoot . '/wp-config/wp-config.php')
      && !$fs->exists($webRoot . '/wp-config.php')) {

      $fs->symlink($composerRoot . '/wp-config/wp-config.php', $webRoot . '/wp-config.php');
      $event->getIO()
        ->write("  *  Symlinked $composerRoot/wp-config/wp-config.php to $webRoot/wp-config.php");
    }
  }

  public static function removeBrokenSymlinks(Event $event) {

    $wordpressFinder = new WordpressFinder();
    $wordpressFinder->locateRoot(getcwd());

    $webRoot = $wordpressFinder->getWebRoot();

    $dirsToCheck = [
      $webRoot . '/wp-content/plugins',
      $webRoot . '/wp-content/themes',
      $webRoot . '/wp-content/mu-plugins',
    ];

    $fs = new Filesystem();

    foreach ($dirsToCheck as $dir) {
      if ($fs->exists($dir)) {

        $finder = new Finder();

        // Remove broken web/wp-content folder symlinks.
        foreach ($finder->in($dir)->depth('== 0')->directories()
          as $path) {

          if ($link = $fs->readlink($path)) {
            if (!$fs->exists($link)) {

              $fs->remove([$path]);
              $event->getIO()->write("  *  Removed broken symlink: $path");
            }
          }
        }

        // Remove broken web/wp-content single PHP file symlinks.
        foreach ($finder->in($dir)->depth('== 0')->files()->name('*.php')
          as $path) {

          if ($link = $fs->readlink($path)) {
            if (!$fs->exists($link)) {

              $fs->remove([$path]);
              $event->getIO()->write("  *  Removed broken symlink: $path");
            }
          }
        }
      }
    }
  }

}
