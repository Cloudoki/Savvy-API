<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('wallets'))
		
			Schema::create ('wallets', function (Blueprint $table)
			{
				$table->increments ('id');
				$table->string ('name', 128);
				$table->tinyInteger ('cumul');
				$table->float ('total')->nullable ();;
				$table->text ('description')->nullable ();
				$table->text ('meta')->nullable ();
				$table->integer ('team_id')->nullable ();
				$table->integer ('user_id')->nullable ();
				
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
		Schema::dropIfExists ('wallets');
	}
}
