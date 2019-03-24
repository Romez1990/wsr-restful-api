<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
	
	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run() {
		factory(App\Models\User::class, 20)->create();
		factory(App\Models\Session::class, 20)->create();
	}
	
}
