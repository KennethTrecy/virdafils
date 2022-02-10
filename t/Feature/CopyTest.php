<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;

class CopyTest extends TestCase {
	public function testRootFileDuplication() {
		$adapter = new VirdafilsAdapter([]);
		$old_path = "/present_a.txt";
		$new_path = "/present_b.txt";
		$file = File::factory()->setPath($old_path)->create();

		$adapter->copy($old_path, $new_path, new Config());

		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 2);
		$root_id = Directory::path("/")->first()->id;
		$this->assertDatabaseHas("files", [
			"name" => "present_a.txt",
			"directory_id" => $root_id,
			"contents" => base64_encode($file->contents)
		]);
		$this->assertDatabaseHas("files", [
			"name" => "present_b.txt",
			"directory_id" => $root_id,
			"contents" => base64_encode($file->contents)
		]);
	}
}
