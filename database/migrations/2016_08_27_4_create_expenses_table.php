<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpensesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('expenses'))
		
			Schema::create ('expenses', function (Blueprint $table)
			{
				$table->increments ('id');
				$table->string ('name', 128);
				$table->integer ('sum')->nullable ();
				$table->tinyInteger ('approved');
				$table->text ('description')->nullable ();
				$table->text ('meta')->nullable ();
				$table->integer ('team_id')->nullable ();
				$table->integer ('wallet_id')->nullable ();
				
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
		Schema::dropIfExists ('expenses');
	}
}
