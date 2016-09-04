<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GuardianCreateRolegroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up ()
	{
		if (!Schema::hasTable('rolegroups'))
		
			Schema::create ('rolegroups', function (Blueprint $table)
			{
				$table->increments ('id');
				$table->string ('name', 32);
				$table->string ('description', 256)->nullable ();
				$table->text ('limits')->nullable ();
				
				$table->softDeletes();
				$table->timestamps ();
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down ()
	{
		Schema::dropIfExists ('rolegroups');
	}

}
