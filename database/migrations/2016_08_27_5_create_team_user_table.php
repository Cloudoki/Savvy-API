<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up ()
	{
		if (!Schema::hasTable('team_user'))
		
			Schema::create ('team_user', function (Blueprint $table)
			{
				$table->integer ('team_id');
				$table->integer ('user_id');
				$table->date ('start_date')->nullable ();
				$table->date ('end_date')->nullable ();
				$table->tinyInteger ('primary');
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down ()
	{
		Schema::dropIfExists ('team_user');
	}

}
