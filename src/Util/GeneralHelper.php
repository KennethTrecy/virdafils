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
}
