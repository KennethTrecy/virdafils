<?php

namespace KennethTrecy\Virdafils\Node;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractNodeModel extends Model {
	use HasFactory;

	public function parentDirectory() {
		return $this->belongsTo(Directory::class, "directory_id");
	}
}
