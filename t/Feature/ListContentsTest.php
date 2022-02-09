<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use League\Flysystem\RootViolationException;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Util\PathHelper;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\Node\File;

class ListContentsTest extends TestCase {
	public function testDirectoryList() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/";
		$root = Directory::factory()->setPath($path)->create();
		$directories = Directory::factory(3)->for($root, "parentDirectory")->create();
		$files = File::factory(3)->for($root, "parentDirectory")->create();

		$list = $adapter->listContents($path);

		$this->assertEquals([
			...$directories->map(function($directory) {
				return [
					"type" => "dir",
					"path" => PathHelper::resolve("/".$directory->name, new Config([])),
					"timestamp" => $directory->updated_at->timestamp
				];
			})->toArray(),
			...$files->map(function($file) {
				return [
					"type" => "file",
					"path" => PathHelper::resolve("/".$file->name, new Config([])),
					"size" => $file->content_size,
					"timestamp" => $file->updated_at->timestamp
				];
			})->toArray(),
		], $list);
	}

	public function testRecursiveDirectoryList() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/";
		$root = Directory::factory()->setPath($path)->create();
		$directories = Directory::factory(2)
			->has(Directory::factory(2), "childDirectories")
			->for($root, "parentDirectory")
			->create();
		$files = File::factory(1)->for($directories[0], "parentDirectory")->create();

		$list = $adapter->listContents($path, true);

		$this->assertEquals([
			...$directories->map(function($directory) {
				return [
					"type" => "dir",
					"path" => PathHelper::resolve("/".$directory->name, new Config([])),
					"timestamp" => $directory->updated_at->timestamp
				];
			})->toArray(),
			...$directories->map(function($directory, $index) use ($files) {
				$subdirectories = $directory
					->childDirectories()
					->get()
					->map(function ($subdirectory) use ($directory) {
						return [
							"type" => "dir",
							"path" => PathHelper::resolve(
								PathHelper::join(["/", $directory->name, $subdirectory->name]),
								new Config([])),
							"timestamp" => $directory->updated_at->timestamp
						];
					});

				$subfiles = collect([]);

				if ($index === 0) {
					$subfiles = $files->map(function($file) {
						return [
							"type" => "file",
							"path" => PathHelper::resolve(
								PathHelper::join(["/", $file->parentDirectory->name, $file->name]),
								new Config([])),
							"size" => $file->content_size,
							"timestamp" => $file->updated_at->timestamp
						];
					});
				}

				return $subdirectories->merge($subfiles);
			})->reduce(function ($previousDirectories, $currentDirectories) {
				return $previousDirectories->merge($currentDirectories);
			}, collect([]))->toArray(),
		], $list);
	}
}
