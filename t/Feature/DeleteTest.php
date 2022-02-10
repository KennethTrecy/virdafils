<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;

class DeleteTest extends TestCase {
	public function testRootFileDeletion() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present_a.txt";
		$file = File::factory()->setPath($path)->create();

		$adapter->delete($path);

		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 0);
	}
}
