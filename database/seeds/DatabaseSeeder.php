<?php

use App\Models\Account;
use App\Models\Team;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Cloudoki\OaStack\Seeds\OaStackSeeder;

class DatabaseSeeder extends Seeder
{
	/**
	* Run the database seeds.
	*
	* @return void
	*/
	public function run()
		{
		# Oauth2-Stack dependency
		if (class_exists ('Cloudoki\OaStack\Seeds\OaStackSeeder'))
		
		$this->call(OaStackSeeder::class);
		
		# Accounts
		$account = Account::create (['name' => 'Savvy LX', 'slug' => 'savvy-lx']);
		
		# Teams
		$cloudoki = new Team (['name' => 'Cloudoki']);
		$sympl = new Team (['name' => 'Sympl.Works']);
		$moovly = new Team (['name' => 'Moovly']);
		
		$account->teams ()->saveMany ([$cloudoki, $sympl, $moovly]);
		
		# Users
		$cloudoki->users ()->saveMany ([
			$delio = new User (['firstname'=> 'Delio', 'lastname'=> 'Amaral', 'email'=> 'delio@cloudoki.com']),
			$edgar = new User (['firstname'=> 'Edgar', 'lastname'=> 'Ribeiro', 'email'=> 'edgar@cloudoki.com']),
			$maria = new User (['firstname'=> 'Maria', 'lastname'=> 'Ribeiro', 'email'=> 'maria@cloudoki.com']),
			$nuno = new User (['firstname'=> 'Nuno', 'lastname'=> 'Jorge', 'email'=> 'nuno@cloudoki.com']),
			$ricardo = new User (['firstname'=> 'Ricardo', 'lastname'=> 'Malta', 'email'=> 'ricardo@cloudoki.com']),
			$simon = new User (['firstname'=> 'Simon', 'lastname'=> 'Verboven', 'email'=> 'simon@cloudoki.com'])
		]);
		
		$sympl->users ()->saveMany ([
			$tiago = new User (['firstname'=> 'Tiago', 'lastname'=> 'Raposeira', 'email'=> 'tiago@cloudoki.com']),
			$tomas = new User (['firstname'=> 'Tomas', 'lastname'=> 'Monteiro', 'email'=> 'tomas@cloudoki.com'])
		]);
		
		$moovly->users ()->saveMany ([
			$catia = new User (['firstname'=> 'Catia', 'lastname'=> 'Araujo', 'email'=> 'catia@cloudoki.com'])
		]);
		
		$bram = User::create (['firstname'=> 'Bram', 'lastname'=> 'Van Oost', 'email'=> 'bram@cloudoki.com']);
		$koen = User::create (['firstname'=> 'Koen', 'lastname'=> 'Betsens', 'email'=> 'koen@cloudoki.com']);
		$rui = User::create (['firstname'=> 'Rui', 'lastname'=> 'Molefas', 'email'=> 'rui@cloudoki.com']);
		$tim = User::create (['firstname'=> 'Tim', 'lastname'=> 'De Coninck', 'email'=> 'tim@cloudoki.com']);
		
		$account->attach ([$bram, $catia, $delio, $edgar, $koen, $maria, $nuno, $ricardo, $rui, $simon, $tiago, $tim, $tomas]);
		
		
		# Wallets
		$catia->wallets ()->saveMany ([
			new Wallet (['name'=> 'Travel Wallet', 'cumul'=> 0, 'total'=> 200]), 
			new Wallet (['name'=> 'Playtime Wallet', 'cumul'=> 0, 'total'=> 150])
		]);
		
		$delio->wallets ()->saveMany ([
			new Wallet (['name'=> 'Travel Wallet', 'cumul'=> 0, 'total'=> 200]), 
			new Wallet (['name'=> 'Playtime Wallet', 'cumul'=> 0, 'total'=> 150])
		]);
		
		$edgar->wallets ()->saveMany ([
			new Wallet (['name'=> 'Travel Wallet', 'cumul'=> 0, 'total'=> 200]), 
			new Wallet (['name'=> 'Playtime Wallet', 'cumul'=> 0, 'total'=> 150])
		]);
		
		$maria->wallets ()->saveMany ([
			new Wallet (['name'=> 'Travel Wallet', 'cumul'=> 0, 'total'=> 200]), 
			new Wallet (['name'=> 'Playtime Wallet', 'cumul'=> 0, 'total'=> 150])
		]);
		
		$nuno->wallets ()->saveMany ([
			new Wallet (['name'=> 'Travel Wallet', 'cumul'=> 0, 'total'=> 200]), 
			new Wallet (['name'=> 'Playtime Wallet', 'cumul'=> 0, 'total'=> 150])
		]);
		
		$ricardo->wallets ()->saveMany ([
			new Wallet (['name'=> 'Travel Wallet', 'cumul'=> 0, 'total'=> 200]), 
			new Wallet (['name'=> 'Playtime Wallet', 'cumul'=> 0, 'total'=> 150])
		]);
		
		$simon->wallets ()->saveMany ([
			new Wallet (['name'=> 'Travel Wallet', 'cumul'=> 0, 'total'=> 200]), 
			new Wallet (['name'=> 'Playtime Wallet', 'cumul'=> 0, 'total'=> 150])
		]);
		
		$tiago->wallets ()->saveMany ([
			new Wallet (['name'=> 'Travel Wallet', 'cumul'=> 0, 'total'=> 200]), 
			new Wallet (['name'=> 'Playtime Wallet', 'cumul'=> 0, 'total'=> 150])
		]);
		
		$tomas->wallets ()->saveMany ([
			new Wallet (['name'=> 'Travel Wallet', 'cumul'=> 0, 'total'=> 200]), 
			new Wallet (['name'=> 'Playtime Wallet', 'cumul'=> 0, 'total'=> 150])
		]);
		
	}
}
