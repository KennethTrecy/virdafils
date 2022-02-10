<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;

class MoveTest extends TestCase {
	public function testRootFileRename() {
		$adapter = new VirdafilsAdapter([]);
		$old_path = "/present_a.txt";
		$new_path = "/present_b.txt";
		$file = File::factory()->setPath($old_path)->create();

		$adapter->move($old_path, $new_path, new Config([]));

		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => "present_b.txt",
			"directory_id" => Directory::path("/")->first()->id
		]);
	}

	public function testRootFiletoDeepFile() {
		$adapter = new VirdafilsAdapter([]);
		$old_path = "/present_a.txt";
		$new_directory_path = "/a/b";
		$new_path = "$new_directory_path/present_c.txt";
		$file = File::factory()->setPath($old_path)->create();

		$adapter->move($old_path, $new_path, new Config([]));

		$this->assertDatabaseCount("directories", 3);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => "present_c.txt",
			"directory_id" => Directory::path($new_directory_path)->first()->id
		]);
	}
}
