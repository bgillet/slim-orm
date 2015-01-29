# Slim-Orm

This package contains a wrapper for to [Idiorm & Paris ORM](http://j4mie.github.io/idiormandparis/ "Idiorm & Paris ORM").

# Requirements

This package is compatible with :

* Slim-Utils 1.3 or greater
* Slim 2.4 or greater
* Paris 1.5 or greater

# Installation

You have two options :

* Downloading from GitHub
  * You have only one class to include from `src` folder called `BenGee\Slim\Db\SlimORM`
  * Of course, you must previously import `Slim` and `Paris` bootstrap classes to your code before importing this one

* Downloading from Composer
  * Add the `bgillet/slim-orm` package to your `composer.json`
  * Then just include your `vendor/autoload.php` file as usual and its done

# Configuration

In your Slim configuration, set your connections parameters as follow :

    $config = array
	(
	    SlimORM::CONNECTIONS => array
	    (
	        SlimORM::DEFAULT_CNX => array
	        (
	            SlimORM::DSN => 'mysql:host=localhost;port=3306;dbname=slim1',
	            SlimORM::USR => 'root',
	            SlimORM::PWD => '',
	            SlimORM::OPT => null
	        ),
	        'named_connection' => array
	        (
	            SlimORM::DSN => 'mysql:host=localhost;port=3306;dbname=slim2',
	            SlimORM::USR => 'root',
	            SlimORM::PWD => '',
	            SlimORM::OPT => null
	        )
	    )
	);

Here are explanations for each parameter :

* `SlimORM::CONNECTIONS`
  * Array of connections to one or more database
* `SlimORM::DEFAULT_CNX`
  * Shortcut to the name of the configuration array of the default connection used by the ORM when none is specified
* `named_connection`
  * Configuration array for a given named connection
* `SlimORM::DSN`
  * PDO compliant connection string to be used by the ORM to connect to database
* `SlimORM::USR`
  * Login to use to connect to the database
* `SlimORM::PWD`
  * Password to use to connect to the database
* `SlimORM::OPT`
  * Additional connection parameters for PDO driver             
* `SlimORM::CACHE`
  * Query caching activation parameter (default: false)  
* `SlimORM::CACHE_CLEAN`
  * Query caching automatic cleaning activation parameter (default: false) 
* `SlimORM::LOG`    
  * Query logging activation parameter (default: false)
* `SlimORM::LOG_FNC`
  * Query logging function parameter. This let you set your own callable function to log your queries. See Idiorm documentation for more details.
* `SlimORM::RES_SET`
  * Connection's results return method parameter. This let you choose how results are returned (array or result set). By default, results are returned as array. 
* `SlimORM::ERR_MOD`
  * PDO error mode parameter. Accept one of the PDO error mode constant (default: PDO::ERRMODE_EXCEPTION).
* `SlimORM::QOT_CAR`
  * Identifier quote character parameter. Detected automatically by default.
* `SlimORM::ID_COL`
  * Default ID column name parameter. Let you define name of primary key used by the ORM for every table (default : 'id').
* `SlimORM::ID_OVR`
  * Let you configure name of primary key for a set of tables as an array with table name as key and primary key name as value. By default, this parameter is not specified and the default 'id_column' parameter value is used.
* `SlimORM::LIM_STY`
  * Let you define limit clause style (TOP or LIMIT) for your connection. By default, this parameter is not specified and the clause style is detected.

All these constants correspond to parameters available in Idiorm or Paris. If there is one or more missing, you still may use the `SlimORM::configure()` static method detailed further. This is a shortcut to the Idiorm's `configure()` method. For more details about available parameters, please consult Idiorm's documentation at [http://idiorm.readthedocs.org/en/latest/configuration.html](http://idiorm.readthedocs.org/en/latest/configuration.html) or Paris' documentation at [http://paris.readthedocs.org/en/latest/configuration.html](http://paris.readthedocs.org/en/latest/configuration.html).
 
# How to use

Let's have a look to the sample code below :

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

Here is expected result of the test :

![Sample of expected test result](https://raw.githubusercontent.com/bgillet/slim-orm/master/tst/expected_result_sample.png)

Use steps are :

1. Add ORM connections and their parameters to Slim's configuration array.
2. Create an instance of your Slim application.
3. Use the `SlimORM::register()` method to configure and register the ORM inside you Slim app under `$app->db`.
4. Then use whatever `$app->db->table()` or `$app->db->model()` method depending on which ORM  you want to use (either Idiorm or Paris). Once you go a reference to a table or a model, you can use methods available in the corresponding ORM.

# API  

## Class `\BenGee\Slim\Db\SlimORM`

* `public static function register(\Slim\Slim $app)`
  * Static method to register the ORM wrapper inside the given Slim app. Make the ORM available via `$app->db` and ensure it is a singleton. When the unique instance of the ORM will be created then the default constructor will look for a list of connections to setup in parent Slim app configuration under the `'slim.orm.connections'` setting name. Connections must be defined in an array using connection name as key and another array for connection parameters :

  * > 	SlimORM::CONNECTIONS => array(
				SlimORM::DEFAULT_CNX => array(
     				SlimORM::DSN => 'mysql:host=localhost;port=3306;dbname=test',
     				SlimORM::USR => 'user',
     				SlimORM::PWD => 'password',
     				SlimORM::OPT => 'some_options',
     			),
     			'connection2' => array(
     				...
				)
			);

  * If no name is given to a connection parameters array then configured connection is considered as the default one.
  * Parameters :
     * `\Slim\Slim $app` : Reference to the parent Slim app.
* `private function __construct(\Slim\Slim $app)`
  * Private default constructor. During construction process, it looks for connections settings in the parent app's configuration and setup them if found.
  * Parameters :
     * `\Slim\Slim $app` : Reference to the parent Slim app.
* `public function app()`
  * Return a reference to the parent Slim app.
* `public function resetConnections()`
  * Reset all configured connections in Idiorm and Paris.
* `public function setConnection($dsn, $usr = false, $pwd = false, $opt = null)`
  * Configure a unique default connection in Idiorm & Paris. Calling this method will reset all existing connections first.
  * Parameters :
     * `string $dsn` : Connection string to the data source.
     * `string $usr` : Username to use to connect to the data source or false (default) if there is no identification needed.
     * `string $pwd` : Password to use to connect to the data source or false (default) if there is no identification needed.
     * `string $opt` : Additional connection options or null (default) if none.
  * Exceptions :
     * `\ErrorException` : If DSN is empty or not a string.
* `public function addConnection($dsn, $usr = false, $pwd = false, $opt = null, $name = false)`    
  * Add a new connection to existing ones in Idiorm & Paris. Every connection is identified by a name given in parameters to the method. If no name is given then it configures the default connection without deleting those already existing.
  * Parameters :
     * `string $dsn` : Connection string to the data source.
     * `string $usr` : Username to use to connect to the data source or false (default) if there is no identification needed.
     * `string $pwd` : Password to use to connect to the data source or false (default) if there is no identification needed.
     * `string $opt`: Additional connection options or null (default) if none.
     * `string $name` : Name of the connection or false (default) to configure the default one.
  * Exceptions :
     * `\ErrorException` : If DSN is empty or not a string.
* `public function model($model, $connection = self::DEFAULT_CNX)`
  * Create an instance of a given model class using the given named connection.
  * Parameters :
     * `string $model` : Fully qualified class name of the model.
     * `string $connection` : Name used to register the connection in Paris ORM.
  * Result :
     * `\BenGee\Slim\Db\SlimModel` : An instance of the requested model class.
* `public function table($table, $connection = self::DEFAULT_CNX)`
  * Create an Idiorm query on a given table using the given named connection.
  * Parameters :
     * `string $table` : Table name to query.
     * `string $connection` : Name used to register the connection in Idiorm / Paris ORM.
  * Results :
     * An Idiorm query on the requested table using the requested connection.
* `public static function configure($key, $value = null, $connection_name = self::DEFAULT_CNX)`
  * Shortcut to internal ORM configuration method.
  * Parameters :
     * `string $key` : Parameter's name.
     * `mixed $value` : Parameter's value.
     * `string $connection_name` : Name of the connection parameter applies to.
