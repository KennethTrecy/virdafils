<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\RootViolationException;
use KennethTrecy\Virdafils\Util\GeneralHelper;
use KennethTrecy\Virdafils\Util\PathHelper;
use KennethTrecy\Virdafils\Node\File;

class FileTest extends TestCase {
	public function testRootImageCreation() {
		$path = "/image";
		$image = UploadedFile::fake()->image("a.png");

		Storage::putFile($path, $image);

		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => $image->hashName(),
			"type" => "image/png",
			"contents" => base64_encode($image->get())
		]);
	}

	public function testRootTextRetrieval() {
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();

		$contents = Storage::get($path);

		$this->assertEquals($present_file->contents, $contents);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => basename($path),
			"type" => "text/plain",
			"contents" => base64_encode($contents)
		]);
	}

	public function testRootTextDeletion() {
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();

		$hasDeleted = Storage::delete($path);

		$this->assertTrue($hasDeleted);
		$this->assertDatabaseCount("files", 0);
		$this->assertDeleted($present_file);
	}

	public function testRootTextAppend() {
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();
		$content_to_append = "hello world";

		$hasUpdated = Storage::append($path, $content_to_append);

		$this->assertTrue($hasUpdated);
		$this->assertDatabaseCount("files", 1);
		// TODO: Make the test platform-agnostic
		$this->assertDatabaseHas("files", [
			"name" => basename($path),
			"type" => "text/plain",
			"contents" => base64_encode("$present_file->contents\r\n$content_to_append")
		]);
	}

	public function testRootTextPrepend() {
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();
		$content_to_prepend = "hello world";

		$hasUpdated = Storage::prepend($path, $content_to_prepend);

		$this->assertTrue($hasUpdated);
		$this->assertDatabaseCount("files", 1);
		// TODO: Make the test platform-agnostic
		$this->assertDatabaseHas("files", [
			"name" => basename($path),
			"type" => "text/plain",
			"contents" => base64_encode("$content_to_prepend\r\n$present_file->contents")
		]);
	}

	public function testRootTextModification() {
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();
		$new_content = "hello world";

		$hasUpdated = Storage::put($path, $new_content);

		$this->assertTrue($hasUpdated);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => basename($path),
			"type" => "text/plain",
			"contents" => base64_encode($new_content)
		]);
	}

	public function testRootTextUrl() {
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();

		$url = Storage::url($path);

		$this->assertEquals(route("verdafils.stream", [
			"path" => ltrim($path, PathHelper::ABSOLUTE_ROOT)
		]), $url);
	}

	public function testRootTextTemporaryUrl() {
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();
		$expiration = now()->addSeconds(1);

		$url = Storage::temporaryUrl($path, $expiration);

		$this->assertEquals(URL::temporarySignedRoute("verdafils.temporary.stream", $expiration, [
			"path" => ltrim($path, PathHelper::ABSOLUTE_ROOT)
		]), $url);
	}
}
