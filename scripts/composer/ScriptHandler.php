<?php
/**
 * @file
 * Contains \WordpressProject\composer\ScriptHandler.
 */

namespace WordpressProject\composer;

use Composer\Script\Event;
use WordpressFinder\WordpressFinder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ScriptHandler {

	public static function createRequiredFiles( Event $event ) {

		$fs              = new Filesystem();
		$wordpressFinder = new WordpressFinder();
		$wordpressFinder->locateRoot( getcwd() );

		$composerRoot = $wordpressFinder->getComposerRoot();

		$dirs = [
			'mu-plugins',
			'plugins',
			'themes',
		];

		// Create folders for custom plugins and themes.
		foreach ( $dirs as $dir ) {
			if ( ! $fs->exists( $composerRoot . '/wp-custom/' . $dir ) ) {
				$fs->mkdir( $composerRoot . '/wp-custom/' . $dir );
				$fs->touch( $composerRoot . '/wp-custom/' . $dir . '/.gitkeep' );
				$event->getIO()
				      ->write( "Created " . $composerRoot . "/" . $dir . " directory" );
			}
		}

		// Create the upload directory with chmod 0777.
		if ( ! $fs->exists( $composerRoot . '/uploads' ) ) {
			$fs->mkdir( $composerRoot . '/uploads', 0777 );
			$event->getIO()
			      ->write( "Created " . $composerRoot . "/uploads directory with chmod 0777" );
		}
	}

	public static function createSymlinks( Event $event ) {

		$fs     = new Filesystem();
		$finder = new Finder();

		$wordpressFinder = new WordpressFinder();
		$wordpressFinder->locateRoot( getcwd() );

		$webRoot      = $wordpressFinder->getWebRoot();
		$composerRoot = $wordpressFinder->getComposerRoot();
		$pluginsDir   = $wordpressFinder->getPluginsDir();
		$themesDir    = $wordpressFinder->getThemesDir();
		$muPluginsDir = $wordpressFinder->getMuPluginsDir();

		$custom_pluginsDir   = $composerRoot . '/wp-custom/plugins';
		$custom_themesDir    = $composerRoot . '/wp-custom/themes';
		$custom_muPluginsDir = $composerRoot . '/wp-custom/mu-plugins';

		$dirsToCheck = [
			$pluginsDir          => 'plugins',
			$custom_pluginsDir   => 'plugins',
			$themesDir           => 'themes',
			$custom_themesDir    => 'themes',
			$muPluginsDir        => 'mu-plugins',
			$custom_muPluginsDir => 'mu-plugins',
		];

		foreach ( $dirsToCheck as $dir => $type ) {
			if ( $fs->exists( $dir ) ) {

				foreach (
					$finder->in( $dir )
					       ->depth( '== 0' )
					       ->directories() as $path
				) {
					$name = basename( $path );
					if ( $fs->exists( $dir . '/' . $name ) && ! $fs->exists( $webRoot . '/wp-content/' . $type . '/' . $name ) ) {

						$fs->symlink( $path, $webRoot . '/wp-content/' . $type . '/' . $name );
						$event->getIO()
						      ->write( "Symlinked $path to $webRoot/wp-content/$type/$name" );
					}
				}

				foreach (
					$finder->in( $dir )
					       ->depth( '== 0' )
					       ->files()
					       ->name( '*.php' ) as $path
				) {
					$name = basename( $path );
					if ( $fs->exists( $dir . '/' . $name ) && ! $fs->exists( $webRoot . '/wp-content/' . $type . '/' . $name ) ) {

						$fs->symlink( $path, $webRoot . '/wp-content/' . $type . '/' . $name );
						$event->getIO()
						      ->write( "Symlinked $path to $webRoot/wp-content/$type/$name" );
					}
				}
			}
		}

		if ( $fs->exists( $composerRoot . '/wp-config/wp-config.php' ) && ! $fs->exists( $webRoot . '/wp-config.php' ) ) {

			$fs->symlink( $composerRoot . '/wp-config/wp-config.php', $webRoot . '/wp-config.php' );
			$event->getIO()
			      ->write( "Symlinked $composerRoot/wp-config/wp-config.php to $webRoot/wp-config.php" );
		}
	}

}
