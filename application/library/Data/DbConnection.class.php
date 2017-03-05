<?php
class DbConnection
{
	/**
	 *
	 * @since 3.1.7
	 */

	private $_dsn='';
	private $_username='';
	private $_password='';
	private $_charset='';
	private $_attributes=array();
	private $_active=false;
	private $_pdo=null;
	private $_transaction=null;

	/**
	 * Constructor.
	 * Note, the DB connection is not established when this connection
	 * instance is created. Set {@link setActive Active} property to true
	 * to establish the connection.
	 * Since 3.1.2, you can set the charset for MySql connection
	 *
	 * @param string The Data Source Name, or DSN, contains the information required to connect to the database.
	 * @param string The user name for the DSN string.
	 * @param string The password for the DSN string.
	 * @param string Charset used for DB Connection (MySql & pgsql only). If not set, will use the default charset of your database server
	 * @see http://www.php.net/manual/en/function.PDO-construct.php
	 */
	public function __construct($dsn='',$username='',$password='', $charset='UTF-8')
	{
		$this->_dsn=$dsn;
		$this->_username=$username;
		$this->_password=$password;
		$this->_charset=$charset;
	}

	/**
	 * Close the connection when serializing.
	 */
	public function __sleep()
	{
		$this->close();
		return array_keys(get_object_vars($this));
	}

	/**
	 * @return array list of available PDO drivers
	 * @see http://www.php.net/manual/en/function.PDO-getAvailableDrivers.php
	 */
	public static function getAvailableDrivers()
	{
		return PDO::getAvailableDrivers();
	}

	/**
	 * @return boolean whether the DB connection is established
	 */
	public function getActive()
	{
		return $this->_active;
	}

	/**
	 * Open or close the DB connection.
	 * @param boolean whether to open or close DB connection
	 * @throws DbException if connection fails
	 */
	public function setActive($value)
	{
		$value=TPropertyValue::ensureBoolean($value);
		if($value!==$this->_active)
		{
			if($value)
			$this->open();
			else
			$this->close();
		}
	}

	/**
	 * Opens DB connection if it is currently not
	 * @throws DbException if connection fails
	 */
	protected function open()
	{
		if($this->_pdo===null)
		{
			try
			{
				$this->_pdo=new PDO($this->getConnectionString(),$this->getUsername(), $this->getPassword(),$this->_attributes);
				// This attribute is only useful for PDO::MySql driver.
				// Ignore the warning if a driver doesn't understand this.
				@$this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
				$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->_active=true;
				$this->setConnectionCharset();
			}
			catch(PDOException $e)
			{
				throw new DbException('dbconnection_open_failed',$e->getMessage());
			}
		}
	}

	/**
	 * Closes the currently active DB connection.
	 * It does nothing if the connection is already closed.
	 */
	protected function close()
	{
		$this->_pdo=null;
		$this->_active=false;
	}

	/*
	 * Set the database connection charset.
	* Only MySql databases are supported for now.
	* @since 3.1.2
	*/
	protected function setConnectionCharset()
	{
		if ($this->_charset === '' || $this->_active === false)
		return;
		switch ($this->_pdo->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case 'mysql':
			case 'sqlite':
				$stmt = $this->_pdo->prepare('SET NAMES ?');
				break;
			case 'pgsql':
				$stmt = $this->_pdo->prepare('SET client_encoding TO ?');
				break;
			default:
				throw new DbException('dbconnection_unsupported_driver_charset', $driver);
		}
		try{
			$stmt->execute(array($this->_charset));
		}catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	/**
	 * @return string The Data Source Name, or DSN, contains the information required to connect to the database.
	 */
	public function getConnectionString()
	{
		return $this->_dsn;
	}

	/**
	 * @param string The Data Source Name, or DSN, contains the information required to connect to the database.
	 * @see http://www.php.net/manual/en/function.PDO-construct.php
	 */
	public function setConnectionString($value)
	{
		$this->_dsn=$value;
	}

	/**
	 * @return string the username for establishing DB connection. Defaults to empty string.
	 */
	public function getUsername()
	{
		return $this->_username;
	}

	/**
	 * @param string the username for establishing DB connection
	 */
	public function setUsername($value)
	{
		$this->_username=$value;
	}

	/**
	 * @return string the password for establishing DB connection. Defaults to empty string.
	 */
	public function getPassword()
	{
		return $this->_password;
	}

	/**
	 * @param string the password for establishing DB connection
	 */
	public function setPassword($value)
	{
		$this->_password=$value;
	}

	/**
	 * @return string the charset used for database connection. Defaults to emtpy string.
	 */
	public function getCharset ()
	{
		return $this->_charset;
	}

	/**
	 * @param string the charset used for database connection
	 */
	public function setCharset ($value)
	{
		$this->_charset=$value;
		$this->setConnectionCharset();
	}

	/**
	 * @return PDO the PDO instance, null if the connection is not established yet
	 */
	public function getPdoInstance()
	{
		return $this->_pdo;
	}

	/**
	 * Creates a command for execution.
	 * @param string SQL statement associated with the new command.
	 * @return DbCommand the DB command
	 * @throws DbException if the connection is not active
	 */
	public function createCommand($sql)
	{
		if(!$this->getActive())
		$this->setActive(true);
		return new DbCommand($this,$sql);
	}

	/**
	 * @return TDbTransaction the currently active transaction. Null if no active transaction.
	 */
	public function getTransaction()
	{
		return $this->_transaction;
	}

	/**
	 * Starts a transaction.
	 * @throws DbException if the connection is not active
	 */
	public function beginTransaction()
	{
		if(!$this->getActive())
		$this->setActive(true);
		$this->_pdo->beginTransaction();
		$this->_transaction = true;
	}

	/**
	 * Returns the ID of the last inserted row or sequence value.
	 * @param string name of the sequence object (required by some DBMS)
	 * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
	 * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
	 */
	public function getLastInsertID($sequenceName='')
	{
		if(!$this->getActive())
		$this->setActive(true);
		return $this->_pdo->lastInsertId($sequenceName);
	}

	/**
	 * Quotes a string for use in a query.
	 * @param string string to be quoted
	 * @return string the properly quoted string
	 * @see http://www.php.net/manual/en/function.PDO-quote.php
	 */
	public function quoteString($str)
	{
		if($this->getActive())
		return $this->_pdo->quote($str);
		else
		throw new DbException('dbconnection_connection_inactive');
	}

	/**
	 * @return TDbColumnCaseMode the case of the column names
	 */
	public function getColumnCase()
	{
		switch($this->getAttribute(PDO::ATTR_CASE))
		{
			case PDO::CASE_NATURAL:
				return TDbColumnCaseMode::Preserved;
			case PDO::CASE_LOWER:
				return TDbColumnCaseMode::LowerCase;
			case PDO::CASE_UPPER:
				return TDbColumnCaseMode::UpperCase;
		}
	}

	/**
	 * @param TDbColumnCaseMode the case of the column names
	 */
	public function setColumnCase($value)
	{
		switch(TPropertyValue::ensureEnum($value,'TDbColumnCaseMode'))
		{
			case TDbColumnCaseMode::Preserved:
				$value=PDO::CASE_NATURAL;
				break;
			case TDbColumnCaseMode::LowerCase:
				$value=PDO::CASE_LOWER;
				break;
			case TDbColumnCaseMode::UpperCase:
				$value=PDO::CASE_UPPER;
				break;
		}
		$this->setAttribute(PDO::ATTR_CASE,$value);
	}

	/**
	 * @return TDbNullConversionMode how the null and empty strings are converted
	 */
	public function getNullConversion()
	{
		switch($this->getAttribute(PDO::ATTR_ORACLE_NULLS))
		{
			case PDO::NULL_NATURAL:
				return TDbNullConversionMode::Preserved;
			case PDO::NULL_EMPTY_STRING:
				return TDbNullConversionMode::EmptyStringToNull;
			case PDO::NULL_TO_STRING:
				return TDbNullConversionMode::NullToEmptyString;
		}
	}

	/**
	 * @param TDbNullConversionMode how the null and empty strings are converted
	 */
	public function setNullConversion($value)
	{
		switch(TPropertyValue::ensureEnum($value,'TDbNullConversionMode'))
		{
			case TDbNullConversionMode::Preserved:
				$value=PDO::NULL_NATURAL;
				break;
			case TDbNullConversionMode::EmptyStringToNull:
				$value=PDO::NULL_EMPTY_STRING;
				break;
			case TDbNullConversionMode::NullToEmptyString:
				$value=PDO::NULL_TO_STRING;
				break;
		}
		$this->setAttribute(PDO::ATTR_ORACLE_NULLS,$value);
	}

	/**
	 * @return boolean whether creating or updating a DB record will be automatically committed.
	 * Some DBMS (such as sqlite) may not support this feature.
	 */
	public function getAutoCommit()
	{
		return $this->getAttribute(PDO::ATTR_AUTOCOMMIT);
	}

	/**
	 * @param boolean whether creating or updating a DB record will be automatically committed.
	 * Some DBMS (such as sqlite) may not support this feature.
	 */
	public function setAutoCommit($value)
	{
		$this->setAttribute(PDO::ATTR_AUTOCOMMIT,TPropertyValue::ensureBoolean($value));
	}

	/**
	 * @return boolean whether the connection is persistent or not
	 * Some DBMS (such as sqlite) may not support this feature.
	 */
	public function getPersistent()
	{
		return $this->getAttribute(PDO::ATTR_PERSISTENT);
	}

	/**
	 * @param boolean whether the connection is persistent or not
	 * Some DBMS (such as sqlite) may not support this feature.
	 */
	public function setPersistent($value)
	{
		return $this->setAttribute(PDO::ATTR_PERSISTENT,TPropertyValue::ensureBoolean($value));
	}

	/**
	 * @return string name of the DB driver
	 */
	public function getDriverName()
	{
		return $this->getAttribute(PDO::ATTR_DRIVER_NAME);
	}

	/**
	 * @return string the version information of the DB driver
	 */
	public function getClientVersion()
	{
		return $this->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}

	/**
	 * @return string the status of the connection
	 * Some DBMS (such as sqlite) may not support this feature.
	 */
	public function getConnectionStatus()
	{
		return $this->getAttribute(PDO::ATTR_CONNECTION_STATUS);
	}

	/**
	 * @return boolean whether the connection performs data prefetching
	 */
	public function getPrefetch()
	{
		return $this->getAttribute(PDO::ATTR_PREFETCH);
	}

	/**
	 * @return string the information of DBMS server
	 */
	public function getServerInfo()
	{
		return $this->getAttribute(PDO::ATTR_SERVER_INFO);
	}

	/**
	 * @return string the version information of DBMS server
	 */
	public function getServerVersion()
	{
		return $this->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	/**
	 * @return int timeout settings for the connection
	 */
	public function getTimeout()
	{
		return $this->getAttribute(PDO::ATTR_TIMEOUT);
	}

	/**
	 * Obtains a specific DB connection attribute information.
	 * @param int the attribute to be queried
	 * @return mixed the corresponding attribute information
	 * @see http://www.php.net/manual/en/function.PDO-getAttribute.php
	 */
	public function getAttribute($name)
	{
		if(!$this->getActive())
		$this->setActive(true);
		else
		return $this->_pdo->getAttribute($name);
	}

	/**
	 * Sets an attribute on the database connection.
	 * @param int the attribute to be set
	 * @param mixed the attribute value
	 * @see http://www.php.net/manual/en/function.PDO-setAttribute.php
	 */
	public function setAttribute($name,$value)
	{
		if($this->_pdo instanceof PDO)
		$this->_pdo->setAttribute($name,$value);
		else
		$this->_attributes[$name]=$value;
	}

	/**
	 * commit the transaction.
	 * @throws DbException if the transaction is not active
	 */
	public function commit(){
		if($this->_transaction == null)
		throw new DbException('dbconnection_transaction_not_active');
		else
		$this->_pdo->commit();
	}
	
	/**
	* rollback the transaction.
	* @throws DbException if the transaction is not active
	*/
	public function rollBack(){
		if($this->_transaction == null)
		throw new DbException('dbconnection_transaction_not_active');
		else
		$this->_pdo->rollBack();
	}
}


