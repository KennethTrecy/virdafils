<?php

namespace Database\Factories\KennethTrecy\Virdafils\Node;

use League\Flysystem\Config;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\Util\PathHelper;
use KennethTrecy\Virdafils\Util\GeneralHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

class DirectoryFactory extends Factory
{
    protected $model = Directory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->word(),
            "visibility" => "private"
        ];
    }

    public function setPath($path, $existing_parent = null, $configuration = [])
    {
        if (is_array($configuration)) {
            $configuration = new Config([]);
        }
        GeneralHelper::withDefaults($configuration);

        $directory_names = PathHelper::resolvedSplit($path, $configuration);
        return $this->setPathParts($directory_names, $existing_parent);
    }

    public function setPathParts(array $path_parts, $existing_parent = null)
    {
        $first_directory_name = array_shift($path_parts);
        $path_parts_count = count($path_parts);

        if ($path_parts_count === 0) {
            return $this->state([ "name" => $first_directory_name ]);
        } elseif ($path_parts_count > 0) {
            $latest_parent_directory = Directory::firstOrNew([
                "name" => $first_directory_name
            ], [
                "visibility" => "private"
            ]);

            $has_found_parent = false;
            if (!is_null($existing_parent) && $existing_parent->is($latest_parent_directory)) {
                $latest_parent_directory = $existing_parent;
                $has_found_parent = true;
            } else {
                $latest_parent_directory->save();
            }

            $last_directory = [
                "name" => array_pop($path_parts),
                "visibility" => "private"
            ];

            foreach ($path_parts as $directory_name) {
                if ($has_found_parent || is_null($existing_parent)) {
                    $latest_parent_directory = Directory::factory()
                        ->state([
                            "name" => $directory_name
                        ])
                        ->for($latest_parent_directory, "parentDirectory");
                } else {
                    $latest_parent_directory = $latest_parent_directory->childDirectories()->firstWhere([
                        "name" => $directory_name
                    ]);

                    if ($existing_parent->is($latest_parent_directory)) {
                        $latest_parent_directory = $existing_parent;
                        $has_found_parent = true;
                    }
                }
            }

            return $this->state($last_directory)->for($latest_parent_directory, "parentDirectory");
        }

        return $this;
    }
}
