<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\VirdafilsAdapter;

class SetVisibilityTest extends TestCase
{
    public function testRootDirectory()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/";
        $directory = Directory::factory()->setPath($path)->create();
        $new_visibility = "public";

        $attributes = $adapter->setVisibility($path, $new_visibility);

        $this->assertDatabaseHas("directories", [
            "visibility" => $new_visibility
        ]);
        $this->assertDatabaseCount("directories", 1);
        $this->assertDatabaseCount("files", 0);
    }

    public function testRootFileVisibility()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/present_a.txt";
        $file = File::factory()->setPath($path)->create();
        $new_visibility = "public";

        $attributes = $adapter->setVisibility($path, $new_visibility);

        $this->assertDatabaseHas("files", [
            "visibility" => $new_visibility
        ]);
        $this->assertDatabaseCount("directories", 1);
        $this->assertDatabaseCount("files", 1);
    }
}
