<?php
/**
 * @file
 * Contains \WordpressProject\composer\ScriptHandler.
 */

namespace WordpressProject\composer;

use Composer\Script\Event;
use WordpressFinder\WordpressFinder;
use Symfony\Component\Filesystem\Filesystem;

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

		// Required for unit testing
		foreach ( $dirs as $dir ) {
			if ( ! $fs->exists( $composerRoot . '/wp-custom/' . $dir ) ) {
				$fs->mkdir( $composerRoot . '/wp-custom/' . $dir );
				$fs->touch( $composerRoot . '/wp-custom/' . $dir . '/.gitkeep' );
			}
		}

		// Create the upload directory with chmod 0777
		if ( ! $fs->exists( $composerRoot . '/uploads' ) ) {
			$fs->mkdir( $composerRoot . '/uploads', 0777 );
			$event->getIO()
			      ->write( "Created " . $composerRoot . " /uploads directory with chmod 0777" );
		}
	}
}
