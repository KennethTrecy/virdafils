<?php

namespace Tests\Online;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use KennethTrecy\Virdafils\Util\GeneralHelper;
use KennethTrecy\Virdafils\Node\File;

class FileTest extends TestCase {
	public function testRootImageCreationFromStream() {
		$path = "/downloaded_image.png";
		$image = fopen($this->faker->imageUrl(3, 3), "r");
		$copied_stream = GeneralHelper::createMemoryStream();
		stream_copy_to_stream($image, $copied_stream);
		$image_contents = stream_get_contents($copied_stream);
		fclose($copied_stream);

		Storage::put($path, $image);

		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => "downloaded_image.png",
			"type" => "image/png",
			"contents" => base64_encode($image_contents)
		]);
	}
}
