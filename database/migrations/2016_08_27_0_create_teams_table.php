<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('teams'))
		
			Schema::create ('teams', function (Blueprint $table)
			{
				$table->increments ('id');
				$table->string ('name', 128);
				$table->string ('logo', 96)->nullable ();
				$table->integer ('account_id')->nullable ();
				
				$table->softDeletes ();
				$table->timestamps ();
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists ('teams');
	}
}
