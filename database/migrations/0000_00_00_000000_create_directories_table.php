<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirectoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("directories", function (Blueprint $table) {
            $table->id();
            $table->foreignId("directory_id")->nullable()->constrained()->onDelete("cascade");
            $table->string("name");
            $table->string("visibility");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("directories");
    }
}
