<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('documents'))
		
			Schema::create ('documents', function (Blueprint $table)
			{
				$table->increments ('id');
				$table->string ('name', 128);
				$table->text ('description')->nullable ();
				$table->text ('meta')->nullable ();
				$table->integer ('expense_id')->nullable ();
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
		Schema::dropIfExists ('documents');
	}
}
