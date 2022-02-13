<?php

namespace KennethTrecy\Virdafils\Util;

use League\Flysystem\Config;

class PathHelper
{
    public const SEPARATOR = "/";
    public const ABSOLUTE_ROOT = "/";

    /**
     * Joins directory names into one path (unresolved).
     *
     * Note: It will have root at the front of the string regardless whethere the root already exist
     * or not in the parameter. Empty directory names will be filtered out.
     *
     * @param array $names
     * @return string
     */
    public static function join(array $names)
    {
        $filtered_names = array_filter($names, function ($name) {
            return $name !== "" && $name !== static::SEPARATOR;
        });
        return implode(static::SEPARATOR, $filtered_names);
    }

    /**
     * Split directory into individual names but does not resolve it.
     *
     * @param string $directory_name
     * @return string
     */
    public static function split(array $path)
    {
        return explode(static::SEPARATOR, $path);
    }

    /**
     * Resolves the path and splits into directory path and basename in an array to process properly.
     *
     * Uses `resolvedSplitCompletely` internally.
     *
     * @param string $path The path to be split
     * @param \League\Flysystem\Config $configuration Contains the root of the path
     * @return array
     */
    public static function resolvedSplitDirectoryAndBase(string $path, Config $configuration)
    {
        $path_parts = static::resolvedSplitCompletely($path, $configuration);
        $directory_parts = $path_parts["dirname"];
        $filename = $path_parts["basename"];
        $directory_path = PathHelper::join($directory_parts);

        return [ $directory_path, $filename ];
    }

    /**
     * Resolves the path and splits into parts to understand properly.
     *
     * Uses `resolvedSplit` internally.
     *
     * @param string $path The path to be split
     * @param \League\Flysystem\Config $configuration Contains the root of the path
     * @return array
     */
    public static function resolve(string $path, Config $configuration)
    {
        return static::join(static::resolvedSplit($path, $configuration));
    }

    /**
     * Resolves the path and splits into parts to understand properly.
     *
     * Uses `resolvedSplitCompletely` internally.
     *
     * @param string $path The path to be split
     * @param \League\Flysystem\Config $configuration Contains the root of the path
     * @return array
     */
    public static function resolvedSplit(string $path, Config $configuration)
    {
        return static::resolvedSplitCompletely($path, $configuration, true)["dirname"];
    }

    /**
     * Resolves the path and split into parts to understand properly along with other infos.
     *
     * The returned array will contain the following keys with their associated value:
     * - `dirname`. An array of directory names
     * - `basename`. Name of the file with extension
     * - `filename`. Name of the file only
     * - `extension`. Extension of the file
     *
     * @param string $path The path to be split
     * @param \League\Flysystem\Config $configuration Contains the root of the path
     * @param bool $has_no_basename If true, the `basename` will be moved to `dirname`
     * @return array
     */
    public static function resolvedSplitCompletely(
        string $path,
        Config $configuration,
        bool $has_no_basename = false
    ) {
        $root = $configuration->get("root", static::ABSOLUTE_ROOT);
        $root_parts = [ static::ABSOLUTE_ROOT ];

        if ($root !== static::ABSOLUTE_ROOT) {
            $root_parts = static::resolvedSplit(
                $root,
                new Config([ "root" => static::ABSOLUTE_ROOT ])
            );
        }

        $root_level = count($root_parts);

        $is_relative = strpos($path, static::SEPARATOR) !== 0;
        if ($is_relative) {
            $path = static::join([$root, ".", $path]);
        }

        // TODO: Find a way to set locale for path info
        $parts = pathinfo($path);

        $parts["dirname"] = explode(
            static::SEPARATOR,
            str_replace("\\", static::SEPARATOR, $parts["dirname"])
        );

        if ($parts["basename"] === ".." || $has_no_basename) {
            array_push($parts["dirname"], $parts["basename"]);
            $parts["basename"] = $parts["extension"] = $parts["filename"] = "";
        }

        // Resolve the current parts of the directory
        $current_parts = [];
        foreach ($parts["dirname"] as $current_directory_name) {
            $current_level = count($current_parts);
            if (($current_directory_name === "" || $current_directory_name === ".")) {
                if ($current_level === 0) {
                    array_push($current_parts, static::ABSOLUTE_ROOT);
                }
            } else {
                array_push($current_parts, $current_directory_name);
            }
        }

        $parts["dirname"] = $current_parts;

        return $parts;
    }
}
