<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GuardianCreateRoleRolegroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up ()
	{
		if (!Schema::hasTable('rolegroup_role'))
		
			Schema::create ('rolegroup_role', function (Blueprint $table)
			{
				$table->increments ('id');
				$table->integer ('rolegroup_id');
				$table->integer ('role_id');
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down ()
	{
		Schema::dropIfExists ('rolegroup_role');
	}

}
