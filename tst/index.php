<h1>SlimORM test page</h1>
	
<?php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require 'User.php';

use \BenGee\Slim\Db\SlimORM;

try
{
	// Configure connections through Slim's configuration array
	$config = array
	(
		SlimORM::CONNECTIONS => array
		(
			SlimORM::DEFAULT_CNX => array
			(
				SlimORM::DSN => 'mysql:host=localhost;port=3306;dbname=slim1',
				SlimORM::USR => 'test',
				SlimORM::PWD => '',
				SlimORM::OPT => null
			),
			'named_connection' => array
			(
				SlimORM::DSN => 'mysql:host=localhost;port=3306;dbname=slim2',
				SlimORM::USR => 'test',
				SlimORM::PWD => '',
				SlimORM::OPT => null
			)
		)
	);
	
	// Create an instance of your Slim application
	 
	$app = new \Slim\Slim($config);
	
	// Register the ORM in the app.
	// It will configure itself using settings set inside Slim's configuration array.
	
	SlimORM::register($app);
	
	// Get a reference to the 'user' table through Idiorm and get contents.
	
	$users1 = $app->db->table('user')->find_many();
	
	// Get a reference to you own 'User' model class through Paris and get contents.
	
	$users2 = $app->db->model('User', 'named_connection')->find_many();
	
	// Display results
	
	$app->get('/', function() use($users1, $users2)
	{
		echo '<h3>Test using Idiorm and accessing data directly using table name on default connection</h1>';
		echo '<p>' . count($users1) . ' users found</p>';
		if (!empty($users1))
		{
			echo '<ul>';
			foreach ($users1 as $user)
			{
				echo '<li>' . $user->username . ' (' . $user->id . ')</li>';
			}
			echo '</ul>';
		}
		echo '<h3>Test using Paris and accessing data using model class on named connection</h1>';
		echo '<p>' . count($users2) . ' users found</p>';
		if (!empty($users2))
		{
			echo '<ul>';
			foreach ($users2 as $user)
			{
				echo '<li>' . $user->username . ' (' . $user->id . ')</li>';
			}
			echo '</ul>';
		}
	});
	
	// Run the Slim app
	
	$app->run();
}
catch (\Exception $e)
{
	echo 'An exception occurred while testing !';
	echo '<div><pre>' . $e . '</pre></div>';
}