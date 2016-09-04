<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GuardianCreateRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('roles'))
			
			Schema::create ('roles', function (Blueprint $table)
			{
				$table->increments ('id');
				$table->string ('slug', 64);
				$table->string ('description', 256);
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists ('roles');
	}

}
