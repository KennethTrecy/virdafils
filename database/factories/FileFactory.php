<?php

namespace Database\Factories\KennethTrecy\Virdafils\Node;

use League\Flysystem\Config;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\Util\PathHelper;
use KennethTrecy\Virdafils\Util\GeneralHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $filename = $this->faker->word().".".$this->faker->fileExtension();
        return [
            "name" => $filename,
            "type" => GeneralHelper::detectMimeType($filename),
            "visibility" => "private",
            "contents" => $this->faker->text(150)
        ];
    }

    public function setPath($path, $existing_parent_directory = null, $configuration = [])
    {
        if (is_array($configuration)) {
            $configuration = new Config([]);
        }
        GeneralHelper::withDefaults($configuration);

        $path_parts = PathHelper::resolvedSplitCompletely($path, $configuration);
        $directory_names = $path_parts["dirname"];
        $basename = $path_parts["basename"];

        return $this
            ->state(function (array $attributes) use ($basename) {
                $attributes["name"] = $basename;
                $attributes["type"] = GeneralHelper::detectMimeType($attributes["name"]);
                return $attributes;
            })
            ->for(
                Directory::factory()->setPathParts($directory_names, $existing_parent_directory),
                "parentDirectory"
            );
    }

    public function streamContents()
    {
        return $this->state(function (array $attributes) {
            $contents = $attributes["contents"];
            if (is_string($contents)) {
                $attributes["contents"] = GeneralHelper::createDataStream($attributes["type"], $contents);
            }
            return $attributes;
        });
    }
}
