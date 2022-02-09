<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\Util\PathHelper;

class ResolvePathTest extends TestCase {
	public function testRoot() {
		$path = "/";

		$path_parts = PathHelper::resolvedSplitCompletely($path, new Config([]));

		$this->assertEqualsCanonicalizing(["/"], $path_parts["dirname"]);
	}

	public function testPath() {
		$path = "/a/.";

		$path_parts = PathHelper::resolvedSplitCompletely($path, new Config([]));

		$this->assertEqualsCanonicalizing(["/", "a"], $path_parts["dirname"]);
	}

	public function testFile() {
		$path = "/a";

		$path_parts = PathHelper::resolvedSplitCompletely($path, new Config([]));

		$this->assertEqualsCanonicalizing(["/"], $path_parts["dirname"]);
		$this->assertEquals("a", $path_parts["filename"]);
	}

	public function testSingleDotWithoutRoot() {
		$path = ".";

		$path_parts = PathHelper::resolvedSplitCompletely($path, new Config([]));

		$this->assertEqualsCanonicalizing(["/"], $path_parts["dirname"]);
	}

	public function testSingleDotWithRoot() {
		$configuration = new Config([ "root" => "/a" ]);
		$path = ".";

		$path_parts = PathHelper::resolvedSplitCompletely($path, $configuration);

		$this->assertEqualsCanonicalizing(["/", "a"], $path_parts["dirname"]);
	}

	public function testRootDuplicationRemoval() {
		$configuration = new Config([ "root" => "/a/b" ]);
		$path = "/a/b/c/d.txt";

		$path_parts = PathHelper::resolvedSplitCompletely($path, $configuration);

		$this->assertEqualsCanonicalizing(["/", "a", "b", "c"], $path_parts["dirname"]);
		$this->assertEquals("d", $path_parts["filename"]);
		$this->assertEquals("txt", $path_parts["extension"]);
	}
}
