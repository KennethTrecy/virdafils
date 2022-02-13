<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use KennethTrecy\Virdafils\Node\File;
use League\Flysystem\DirectoryAttributes;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\Util\PathHelper;
use KennethTrecy\Virdafils\VirdafilsAdapter;

class ListContentsTest extends TestCase
{
    public function testDirectoryList()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/";
        $root = Directory::factory()->setPath($path)->create();
        $directories = Directory::factory(3)->for($root, "parentDirectory")->create();
        $files = File::factory(3)->for($root, "parentDirectory")->create();

        $list = $adapter->listContents($path);

        $this->assertEquals([
            ...$directories->map(function ($directory) {
                return new DirectoryAttributes(
                    PathHelper::resolve("/".$directory->name, new Config([])),
                    $directory->visibility,
                    $directory->updated_at->timestamp
                );
            })->toArray(),
            ...$files->map(function ($file) {
                return new FileAttributes(
                    PathHelper::resolve("/".$file->name, new Config([])),
                    $file->content_size,
                    $file->visibility,
                    $file->updated_at->timestamp,
                    $file->type
                );
            })->toArray(),
        ], iterator_to_array($list));
    }

    public function testRecursiveDirectoryList()
    {
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
            ...$directories->map(function ($directory) {
                return new DirectoryAttributes(
                    PathHelper::resolve("/".$directory->name, new Config([])),
                    $directory->visibility,
                    $directory->updated_at->timestamp
                );
            })->toArray(),
            ...$directories->map(function ($directory, $index) use ($files) {
                $subdirectories = $directory
                    ->childDirectories()
                    ->get()
                    ->map(function ($subdirectory) use ($directory) {
                        return new DirectoryAttributes(
                            PathHelper::resolve(
                                PathHelper::join(["/", $directory->name, $subdirectory->name]),
                                new Config([])
                            ),
                            $directory->visibility,
                            $directory->updated_at->timestamp
                        );
                    });

                $subfiles = collect([]);

                if ($index === 0) {
                    $subfiles = $files->map(function ($file) {
                        return new FileAttributes(
                            PathHelper::resolve(
                                PathHelper::join(["/", $file->parentDirectory->name, $file->name]),
                                new Config([])
                            ),
                            $file->content_size,
                            $file->visibility,
                            $file->updated_at->timestamp,
                            $file->type
                        );
                    });
                }

                return $subdirectories->merge($subfiles);
            })->reduce(function ($previousDirectories, $currentDirectories) {
                return $previousDirectories->merge($currentDirectories);
            }, collect([]))->toArray(),
        ], iterator_to_array($list));
    }
}
