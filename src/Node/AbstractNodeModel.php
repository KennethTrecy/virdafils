<?php

namespace KennethTrecy\Virdafils\Node;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\Util\PathHelper;

abstract class AbstractNodeModel extends Model {
	use HasFactory;

	public function parentDirectory() {
		return $this->belongsTo(Directory::class, "directory_id");
	}

	public function scopeChildOf($query, array $parent_path_parts) {
		if (count($parent_path_parts) > 0) {
			$query = $query->whereHas("parentDirectory", function($query) use ($parent_path_parts) {
				$parent_name = array_pop($parent_path_parts);
				$query->where("name", $parent_name)->childOf($parent_path_parts);
			});
		}
		return $query;
	}

	public function scopeNavigateByPathParts($query, array $resolved_path_parts) {
		$target_name = array_pop($resolved_path_parts);

		return $query->where("name", $target_name)->childOf($resolved_path_parts);
	}

	public function scopePath($query, string $relative_path, Config $configuration = null) {
		if (is_null($configuration)) {
			$configuration = new Config([]);
		}

		$path_parts = PathHelper::resolvedSplit($relative_path, $configuration);

		return $query->navigateByPathParts($path_parts);
	}
}
