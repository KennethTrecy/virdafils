<?php

namespace KennethTrecy\Virdafils\Util;

use League\Flysystem\Config;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

class GeneralHelper {
	public static array $fallback = [
		"root" => PathHelper::SEPARATOR,
		"visibility" => "private"
	];

	/**
	 * Sets a fallback (or default) values for the configuration.
	 */
	public static function withDefaults(Config $configuration) {
		$fallback = static::$fallback;

		$configuration->withDefaults($fallback);
	}

	public static function createMemoryStream() {
		return fopen("php://memory", "r+");
	}

	public static function createWrittenMemoryStream($contents) {
		$stream = static::createMemoryStream();
		fwrite($stream, $contents);
		fseek($stream, 0, SEEK_SET);
		return $stream;
	}

	public static function createDataStream($type, $contents) {
		return fopen("data://$type,".urlencode($contents), "rb");
	}

	public static function detectMimeType($path) {
		return (new FinfoMimeTypeDetector())->detectMimeTypeFromPath($path);
	}
}
