<?php

namespace KennethTrecy\Virdafils\Util;

use League\Flysystem\Config;

class GeneralHelper {
	/**
	 * Sets a fallback (or default) values for the configuration.
	 */
	public static function setFallback(Config $configuration) {
		$fallback = new Config([
			"root" => PathHelper::SEPARATOR,
			"visibility" => "private"
		]);

		$configuration->setFallback($fallback);
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
}
