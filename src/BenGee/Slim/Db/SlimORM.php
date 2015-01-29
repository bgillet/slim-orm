<?php

/**
 * SlimORM (http://github.com/bgillet/slim-orm/)
 * ORM for Slim framework wrapping Idiorm and Paris :
 * http://j4mie.github.io/idiormandparis/
 *
 * @author Benjamin GILLET <bgillet@hotmail.fr>
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace BenGee\Slim\Db;

use \BenGee\Slim\Utils\StringUtils;

/**
 * Slim ORM wrapper class for Idiorm & Paris.
 * Offer a static method to register the ORM wrapper as a resource in a Slim
 * app under 'db' variable (ex: $app->db).
 */
final class SlimORM
{
    /**
     * Constant defining SlimORM setting name.
     */
    const CONNECTIONS = 'slim.orm.connections';
    
    /**
     * Constant defining connection's DSN parameter's name.
     * When configuring a connection, this parameter is mandatory.
     */
    const DSN = 'connection_string';
    
    /**
     * Constant defining connection's user parameter's name.
     * When configuring a connection, this parameter is optional.
     */
    const USR = 'username';
    
    /**
     * Constant defining connection's password parameter's name.
     * When configuring a connection, this parameter is optional.
     */
    const PWD = 'password';
    
    /**
     * Constant defining connection's additional options parameter's name.
     * When configuring a connection, this parameter is optional.
     */
    const OPT = 'driver_options';
    
    /**
     * Constant defining connection's query caching activation parameter's name.
     * When configuring a connection, this parameter is optional.
	 * This lets you activating query caching for your connection.
	 * Query caching is disabled ('false') by default.
     */
    const CACHE = 'caching';
    
    /**
     * Constant defining connection's query caching automatic cleaning parameter's name.
     * When configuring a connection, this parameter is optional.
	 * This lets you activating activating automatic cache cleaning for your connection.
	 * Automatic cache cleaning is disabled ('false') by default.
     */
    const CACHE_CLEAN = 'caching_auto_clear';
    
    /**
     * Constant defining connection's logging activation parameter's name.
     * When configuring a connection, this parameter is optional.
	 * This lets you activating query logging for your connection.
	 * Query logging is disabled ('false') by default.
     */
    const LOG = 'logging';
    
    /**
     * Constant defining connection's logger parameter's name.
     * When configuring a connection, this parameter is optional.
	 * This lets you specifying your own logger through a callable method.
     */
    const LOG_FNC = 'logging';
    
    /**
     * Constant defining connection's results return method's name.
     * When configuring a connection, this parameter is optional.
	 * It specifies how collections of results are returned :
     * - 'true'  : result sets
	 * - 'false' : array (default)
     */
    const RES_SET = 'return_result_sets';
    
    /**
     * Constant defining connection's error mode parameter's name.
     * When configuring a connection, this parameter is optional.
	 * This lets you configuring PDO error mode using one of the PDO constant.
	 * Default is PDO::ERRMODE_EXCEPTION.
     */
    const ERR_MOD = 'error_mode';
    
    /**
     * Constant defining connection's identifier quote character parameter's name.
     * When configuring a connection, this parameter is optional.
	 * By default, this parameter is not specified and quote character is detected.
     */
    const QOT_CAR = 'identifier_quote_character';
    
    /**
     * Constant defining connection's default primary key parameter's name.
     * When configuring a connection, this parameter is optional.
	 * This lets you configuring default name to use to identify your primary key in every tables.
	 * By default, this parameter is set to 'id'.
     */
    const ID_COL = 'id_column';
    
    /**
     * Constant defining connection's per table primary key list parameter's name.
     * When configuring a connection, this parameter is optional.
	 * This lets you configuring a specific primary key name for a set of table.
	 * By default, this parameter is not specified and the default 'id_column' parameter value is used.
     */
    const ID_OVR = 'id_column_overrides';
    
    /**
     * Constant defining connection's limit clause style parameter's name.
     * When configuring a connection, this parameter is optional.
	 * This lets you configuring a limit clause style for you PDO driver.
	 * By default, this parameter is not specified and the clause style is detected.
     */
    const LIM_STY = 'limit_clause_style';
    
    /**
     * Constant defining name of the default connection.
     */
    const DEFAULT_CNX = \ORM::DEFAULT_CONNECTION;
    
    /**
     * Internal reference to the Slim app where is referenced the ORM.
     * @var \Slim\Slim
     */
    private $_app = null;
    
    /**
     * Static method to register the ORM wrapper inside the given Slim app.
     * Make the ORM available via $app->db and ensure it is a singleton.
     * When the unique instance of the ORM will be created then the default
     * constructor will look for a list of connections to setup in parent
     * Slim app configuration under the 'slim.orm.connections' setting name.
     * Connections must be defined in an array using connection name as key
     * and another array for connection parameters :
     *      SlimORM::CONNECTIONS => array(
     *          SlimORM::DEFAULT_CNX => array(
     *              SlimORM::DSN => 'mysql:host=localhost;port=3306;dbname=test',
     *              SlimORM::USR => 'user',
     *              SlimORM::PWD => 'password',
     *              SlimORM::OPT => 'some_options',
     *          ),
     *          'connection2' => array(
     *              ...
     *          )
     *      );
     * If no name is given to a connection parameters array then configured
     * connection is considered as the default one.
     * @param \Slim\Slim $app Reference to the parent Slim app
     */
    public static function register(\Slim\Slim $app)
    {
        if (empty($app)) throw new \ErrorException("Parent Slim app cannot be null !");
        $db = $app->db;
        if (!empty($db))
        {
            if ($db instanceof \BenGee\Slim\Db\SlimORM)
            {
                throw new \ErrorException("An instance of the SlimORM is already registered !");
            }
            else
            {
                throw new \ErrorException("An instance of another ORM is already registered under 'db' resource name !");
            }
        }
        $class = __CLASS__;
        $db = new $class($app);
        $app->container->singleton('db', function() use($db)
        {
            return $db;
        });
    }
    
    /**
     * Private default constructor.
     * During construction process, it look for connections setting in the
     * parent app configuration and setup them if found.
     * @param \Slim\Slim $app Reference to the parent Slim app.
     */
    private function __construct(\Slim\Slim $app)
    {
        if (empty($app)) throw new \ErrorException("Cannot create instance of SlimORM without a reference to the Slim application it belongs to !");
        $this->_app = $app;
        $connections = $app->config(self::CONNECTIONS);
        if (!empty($connections) && !is_array($connections)) throw new \ErrorException("Invalid '" . self::CONNECTIONS . "' setting ! Must be an array of named connections containing array of parameters !");
        foreach ($connections as $name => $parameters)
        {
            if (empty($parameters) || !is_array($parameters) || !array_key_exists(self::DSN, $parameters)) throw new \ErrorException("A connection must be an array of named parameters : '" . self::DSN . "' (mandatory), '" . self::USR . "' (optional), '" . self::PWD . "' (optional) and '" . self::OPT . "' (optional) and lots of more. See documentation for more details about available parameters.");
            $dsn = $parameters[self::DSN];
            $usr = $parameters[self::USR];
            $pwd = $parameters[self::PWD];
            $opt = $parameters[self::OPT];
            $this->addConnection($dsn, $usr, $pwd, $opt, $name);
			foreach ($parameters as $key => $value)
			{
				switch ($key)
				{
					case self::DSN:
					case self::USR:
					case self::PWD:
					case self::OPT:
						break;
					default:
						self::configure($key, $value, $name);
						break;
				}
			}
        }
    }
    
    /**
     * Return a reference to the parent Slim app.
     */
    public function app()
    {
        return $this->_app;
    }
    
    /**
     * Reset all connections configured in Idiorm & Paris.
     */
    public function resetConnections()
    {
        \ORM::reset_config();
    }
    
    /**
     * Configure a unique default connection in Idiorm & Paris.
     * Calling this method will reset all existing connections first.
     * @param string $dsn Connection string to the data source.
     * @param string $usr Username to use to connect to the data source or false (default) if there is no identification needed.
     * @param string $pwd Password to use to connect to the data source or false (default) if there is no identification needed.
     * @param string $opt Additional connection options or null (default) if none.
     * @throw \ErrorException If DSN is empty or not a string.
     */
    public function setConnection($dsn, $usr = false, $pwd = false, $opt = null)
    {
        $this->resetConnections();
        $this->addConnection($dsn, $usr, $pwd, $opt);
    }
    
    /**
     * Add a new connection to existing ones in Idiorm & Paris.
     * Every connection is identified by a name given in parameters to the
     * method. If no name is given then it configures the default connection
     * without deleting those already existing.
     * @param string $dsn Connection string to the data source.
     * @param string $usr Username to use to connect to the data source or false (default) if there is no identification needed.
     * @param string $pwd Password to use to connect to the data source or false (default) if there is no identification needed.
     * @param string $opt Additional connection options or null (default) if none.
     * @param string $name Name of the connection or false (default) to configure the default one.
     * @throw \ErrorException If DSN is empty or not a string.
     */
    public function addConnection($dsn, $usr = false, $pwd = false, $opt = null, $name = false)
    {
        $dsn = (is_string($dsn) ? trim($dsn) : false);
        $name = (is_string($name) && !ctype_space($name) ? trim($name) : self::DEFAULT_CNX);
        if (!StringUtils::emptyOrSpaces($dsn))
        {
            self::configure(self::DSN, $dsn, $name);
            $usr = (!StringUtils::emptyOrSpaces($usr) ? trim($usr) : false);
            if ($usr !== false) self::configure(self::USR, $usr, $name);
            $pwd = (!StringUtils::emptyOrSpaces($pwd) ? $pwd : false);
            if ($pwd !== false) self::configure(self::PWD, $pwd, $name);
            $opt = (!StringUtils::emptyOrSpaces($opt) ? trim($opt) : false);
            if (!empty($opt)) self::configure(self::OPT, $opt, $name);
        }
        else
        {
            throw new ErrorException("Connection string cannot be empty and must be a string !");
        }
    }
    
    /**
     * Create an instance of a given model class using the given named connection.
     * @param string $model Fully qualified class name of the model.
     * @param string $connection Name used to register the connection in Paris ORM.
     * @return \BenGee\Slim\Db\SlimModel An instance of the requested model class.
     */
    public function model($model, $connection = self::DEFAULT_CNX)
    {
        return \Model::factory($model, $connection);
    }
    
    /**
     * Create an Idiorm query on a given table using the given named connection.
     * @param string $table Table name to query.
     * @param string $connection Name used to register the connection in Idiorm / Paris ORM.
     * @return An Idiorm query on the requested table using the requested connection.
     */
    public function table($table, $connection = self::DEFAULT_CNX)
    {
        return \ORM::for_table($table, $connection);
    }
    
    /**
     * Shortcut to internal ORM configuration method.
     * @param string $key Parameter's name.
     * @param mixed $value Parameter's value.
     * @param string $connection_name Name of the connection parameter applies to.
     */
    public static function configure($key, $value = null, $connection_name = self::DEFAULT_CNX)
    {
        \ORM::configure($key, $value, $connection_name);
    }
}
