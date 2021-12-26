<?php

namespace KennethTrecy\Virdafils;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\RootViolationException;
use KennethTrecy\Virdafils\Util\GeneralHelper;
use KennethTrecy\Virdafils\Util\PathHelper;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\Node\File;

/**
 * For more information, please read the links below.
 * @link https://github.com/thephpleague/flysystem/blob/1.1.9/src/AdapterInterface.php
 * @link https://github.com/thephpleague/flysystem/blob/1.1.9/src/ReadInterface.php
 */
class VirdafilsAdapter implements AdapterInterface {
	protected Config $configuration;

	function __construct(array $configuration = []) {
		$this->configuration = new Config($configuration);

		GeneralHelper::setFallback($this->configuration);
	}

	public function has($path) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);

		return Directory::navigateByPathParts($path_parts, $this->configuration)->exists()
			||	File::navigateByPathParts($path_parts, $this->configuration)->exists();
	}

	public function getMetadata($path) {

	}

	public function getSize($path) {

	}

	public function getMimeType($path) {

	}

	public function getTimestamp($path) {

	}

	public function listContents($directory = "", $recursive = false) {

	}

	public function getVisibility($path) {

	}

	public function setVisibility($path, $visibility) {

	}

	public function read($path) {

	}

	public function readStream($path) {

	}

	public function write($path, $contents, Config $configuration) {

	}

	public function writeStream($path, $resource, Config $configuration) {

	}

	public function update($path, $contents, Config $configuration) {

	}

	public function updateStream($path, $resource, Config $configuration) {

	}

	public function rename($old_path, $new_path) {

	}

	public function copy($old_path, $new_path) {

	}

	public function delete($path) {

	}

	public function createDir($path, Config $configuration) {
		$this->setFallbackConfiguration($configuration);

		$directory_parts = PathHelper::resolvedSplit($path, $configuration);
		return $this->createDirectoryFromParts($directory_parts, $configuration->get("visibility"));
	}

	public function deleteDir($path) {
		$directory_parts = PathHelper::resolvedSplit($path, $this->configuration);

		if ($directory_parts === PathHelper::resolvedSplit(
			$this->configuration->get("root"),
			new Config([ "root" => "/"])
		)) {
			throw new RootViolationException();
		}

		$directory = Directory::navigateByPathParts($directory_parts, $this->configuration)->first();

		if ($directory === null) {
			return false;
		}

		return $directory->delete();
	}

	protected function findOrCreateDirectory($directory_path, $configuration) {
		$path_parts = PathHelper::resolvedSplit($directory_path, $configuration);
		$directory_builder = Directory::navigateByPathParts($path_parts, $configuration);
		$directory = $directory_builder->first();

		if (is_null($directory)) {
			$this->createDirectoryFromParts($path_parts, $configuration->get("visibility"));
			$directory = $directory_builder->first();
		};

		return $directory;
	}

	protected function createDirectoryFromParts($directory_parts, $visibility) {
		$resolved_path = PathHelper::join($directory_parts);

		$directory = Directory::firstOrCreate([
			"name" => array_shift($directory_parts),
			"visibility" => $visibility
		]);

		foreach ($directory_parts as $directory_name) {
			$directory = $directory->childDirectories()->firstOrCreate([
				"name" => $directory_name
			], [
				"visibility" => $visibility
			]);
		}

		// TODO: Return false if the path is a file
		return [
			"path" => $resolved_path,
			"type" => "dir"
		];
	}

	protected function setFallbackConfiguration(Config $configuration) {
		// Prevent setting fallback to itself
		if ($configuration !== $this->configuration) {
			$configuration->setFallback($this->configuration);
		}
	}

	protected function whenDirectoryExists($path, $present_closure, $absent_closure = null) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);

		return $this->whenDirectoryAsPartsExists($path_parts, $present_closure, $absent_closure);
	}

	protected function whenFileExists($path, $present_closure, $absent_closure = null) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);

		return $this->whenFileAsPartsExists($path_parts, $present_closure, $absent_closure);
	}

	protected function whenDirectoryAsPartsExists(
		$resolved_path_parts,
		$present_closure,
		$absent_closure = null
	) {
		return $this->whenModelExists(
			$resolved_path_parts,
			Directory::navigateByPathParts($resolved_path_parts),
			$present_closure,
			$absent_closure);
	}

	protected function whenFileAsPartsExists(
		$resolved_path_parts,
		$present_closure,
		$absent_closure = null
	) {
		return $this->whenModelExists(
			$resolved_path_parts,
			File::navigateByPathParts($resolved_path_parts),
			$present_closure,
			$absent_closure);
	}

	protected function whenModelExists(
		$resolved_path_parts,
		$builder,
		$present_closure,
		$absent_closure = null
	) {
		$model = $builder->first();
		if (is_null($model)) {
			if (is_null($absent_closure)) {
				// TODO: Throw error when file/directory not found
				return false;
			} else {
				return $absent_closure($resolved_path_parts, $builder);
			}
		} else {
			$resolved_path =  PathHelper::join($resolved_path_parts);
			return $present_closure($model, $resolved_path, $resolved_path_parts);
		}
	}
}
