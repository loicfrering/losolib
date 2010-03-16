<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL;

use Doctrine\Common\EventManager;

/**
 * A ReadWriteConnection splits read and write operations to separate driver connections.
 * 
 * The read or write connection used during a request is obtained randomly from a
 * pool of given read or write connection parameters. A ReadWriteConnection uses only
 * 1 READ and 1 WRITE connection per request. Both connections are established lazily,
 * so if no write operations take place, then no write connection will be established.
 * Similarly for read connections.
 * 
 * Transactions always take effect on the WRITE connection. That is, beginTransaction(),
 * commit() and rollback() always operate on the WRITE connection.
 * 
 * All connections must use the same driver. That is, you can not have connections to different
 * types of databases (e.g. one to mysql and one to postgres).
 * 
 * @author Roman Borschel <roman@code-factory.org>
 * @internal The driver connection used by the parent class Connection ($_conn) is used
 *           as the WRITE connection.
 * @since 2.0
 * @todo Automatic failover (try next connection, ...)
 * @todo If the resolved read or write connection is the same as the already existing read or
 *       write connection, just use it.
 */
class ReadWriteConnection extends Connection
{
    const READ = 'read';
    const WRITE = 'write';
    
    /** @var array All the connection parameters for READ connections. */
    private $_readParams;
    
    /** @var array All the connection parameters for WRITE connections. */
    private $_writeParams;
    
    /** @var array Which connections are already open. */
    private $_connected = array();
    
    /** @var Connection The read connection. */
    private $_readConn;
    
    /** @var string The name of the currently used READ connection. */
    private $_readConnName;
    
    /** @var string The name of the currently used WRITE connection. */
    private $_writeConnName;
    
    /** @var array The connection states of the READ and WRITE connection. */
    private $_isConnected = array('read' => false, 'write' => false);
    
    /** @var ReadWriteConnectionResolver The resolver to use for picking connections, if any. */
    private $_connectionResolver;
    
    public function __construct(array $readParams, array $writeParams, Driver $driver,
            Configuration $config = null, EventManager $eventManager = null)
    {
        $this->_driver = $driver;
        $this->_readParams = $readParams;
        $this->_writeParams = $writeParams;
        
        foreach ($readParams as $name => $read) {
            if (isset($read['pdo'])) {
                if ($this->_isConnected['read']) {
                    throw new DBALException("Only one read connection can already be open.");
                }
                $this->_readConn = $read['pdo'];
                $this->_readConnName = $name;
                $this->_isConnected['read'] = true;
            }
        }
        
        foreach ($writeParams as $name => $write) {
            if (isset($write['pdo'])) {
                if ($this->_isConnected['read']) {
                    throw new DBALException("Only one write connection can already be open.");
                }
                $this->_conn = $write['pdo'];
                $this->_writeConnName = $name;
                $this->_isConnected['write'] = true;
            }
        }
        
        // Create default config and event manager if none given
        if ( ! $config) {
            $config = new Configuration();
        }
        if ( ! $eventManager) {
            $eventManager = new EventManager();
        }
        
        $this->_config = $config;
        $this->_eventManager = $eventManager;
        
        if ( ! isset($params['platform'])) {
            $this->_platform = $driver->getDatabasePlatform();
        } else if ($params['platform'] instanceof Platforms\AbstractPlatform) {
            $this->_platform = $params['platform'];
        } else {
            throw DBALException::invalidPlatformSpecified();
        }
        $this->_transactionIsolationLevel = $this->_platform->getDefaultTransactionIsolationLevel();
        
        /*
        $readParams = array('slave1' => array(
            'driverOptions' => array(),
            'user' => 'dev',
            'password' => 'dev',
            'host' => 'localhost',
            'dbname' => 'slave1',
            'port' => 3306
        ));
        */   
    }
    
    /** @override */
    public function prepare($statement)
    {
        // if statement is SELECT, grab READ connection, else grab WRITE connection
        if (stripos($statement, 'SELECT') === 0) {
            $this->connect(self::READ);
            return $this->_readConn->prepare($statement);
        } else {
            $this->connect(self::WRITE);
            return $this->_conn->prepare($statement);
        }
    }
    
    /**
     * Always starts transactions on the selected MASTER.
     * 
     * @override
     */
    public function beginTransaction()
    {
        $this->connect();
        
        if ($this->_transactionNestingLevel == 0) {
            $this->_conn->beginTransaction();
        }
        
        ++$this->_transactionNestingLevel;
    }
    
    /**
     * Closes all connections.
     *
     * @return void
     * @override
     */
    public function close()
    {
        unset($this->_readConn);
        unset($this->_conn);
        $this->_isConnected[self::READ] = false;
        $this->_isConnected[self::WRITE] = false;
    }
    
    /**
     * Establishes the connection with the database.
     *
     * @param $mode Whether to establish a READ or WRITE connection.
     * @return boolean TRUE if the connection was successfully established, FALSE if
     *                 the connection is already open.
     * @override
     */
    public function connect($mode = self::WRITE)
    {
        if ($this->_isConnected[$mode]) return false;

        if ($mode == self::READ) {
            $this->_readConn = $this->_connect($this->_resolveReadConnection());
        } else {
            $this->_conn = $this->_connect($this->_resolveWriteConnection());
        }
        
        $this->_isConnected[$mode] = true;

        return true;
    }
    
    /**
     * Whether an actual connection to the database is established.
     *
     * @param $mode Whether to check the READ or WRITE connection.
     * @return boolean TRUE if a connection is established/open, FALSE otherwise.
     * @override
     */
    public function isConnected($mode = self::WRITE)
    {
        return $this->_isConnected[$mode];
    }
    
    /**
     * Prepares and executes an SQL query.
     *
     * @param string $query The SQL query to prepare and execute.
     * @param array $params The parameters, if any.
     * @return Statement The prepared and executed statement.
     * @override
     */
    public function execute($query, array $params = array())
    {
        $isRead = stripos($statement, 'SELECT') === 0;
        
        $this->connect($isRead ? self::READ : self::WRITE);
        
        $conn = $isRead ? $this->_readConn : $this->_conn;

        if ($this->_config->getSqlLogger()) {
            $this->_config->getSqlLogger()->logSql($query, $params);
        }
        
        if ( ! empty($params)) {
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
        } else {
            $stmt = $conn->query($query);
        }
        
        return $stmt;
    }
    
    /**
     * Gets the hostname of the currently used WRITE connection.
     * 
     * @return string
     */
    public function getHost()
    {
        $this->connect();
        return isset($this->_writeParams[$this->_writeConnName]['host']) ?
                $this->_writeParams[$this->_writeConnName]['host'] : null;
    }
    
    /**
     * Gets the port of the currently used WRITE connection.
     * 
     * @return mixed
     */
    public function getPort()
    {
        $this->connect();
        return isset($this->_writeParams[$this->_writeConnName]['port']) ?
                $this->_writeParams[$this->_writeConnName]['port'] : null;
    }
    
    /**
     * Gets the username of the currently used WRITE connection.
     * 
     * @return string
     */
    public function getUsername()
    {
        $this->connect();
        return isset($this->_writeParams[$this->_writeConnName]['username']) ?
                $this->_writeParams[$this->_writeConnName]['username'] : null;
    }
    
    /**
     * Gets the password of the currently used WRITE connection.
     * 
     * @return string
     */
    public function getPassword()
    {
        $this->connect();
        return isset($this->_writeParams[$this->_writeConnName]['password']) ?
                $this->_writeParams[$this->_writeConnName]['password'] : null;
    }
    
    /**
     * Gets the wrapped driver connection used for read operations.
     *
     * @return Doctrine\DBAL\Driver\Connection
     */
    public function getWrappedReadConnection()
    {
        $this->connect(self::READ);
        
        return $this->_readConn;
    }
    
    /**
     * Sets the ReadWriteConnectionResolver used by this ReadWriteConnection.
     * 
     * @param ReadWriteConnectionResolver The resolver to use by this ReadWriteConnection.
     */
    public function setConnectionResolver(ReadWriteConnectionResolver $resolver)
    {
        $this->_connectionResolver = $resolver;
    }
    
    /**
     * Gets the ReadWriteConnectionResolver used by this ReadWriteConnection.
     * 
     * @return ReadWriteConnectionResolver The resolver used by this ReadWriteConnection, or NULL.
     */
    public function getConnectionResolver()
    {
        return $this->_connectionResolver;
    }
    
    /**
     * Establishes a driver connection.
     * 
     * @param array $params The connection parameters.
     * @return Connection The driver connection.
     */
    private function _connect(array $params)
    {
        $driverOptions = isset($params['driverOptions']) ? $params['driverOptions'] : array();
        $user = isset($params['user']) ? $params['user'] : null;
        $password = isset($params['password']) ? $params['password'] : null;
                
        return $this->_driver->connect($params, $user, $password, $driverOptions);
    }
    
    /**
     * Resolves the parameters of the READ connection to use during the current request.
     * 
     * @return array The connection parameters of the connection to use.
     */
    private function _resolveReadConnection()
    {
        if ($this->_connectionResolver !== null) {
            $name = $this->_connectionResolver->resolveReadConnection($this->_readParams);
        } else {
            $name = array_rand($this->_readParams);
        }
        
        $this->_readConnName = $name;
        
        return $this->_readParams[$name];
    }
    
    /**
     * Resolves the parameters of the WRITE connection to use during the current request.
     * 
     * @return array The connection parameters of the connection to use.
     */
    private function _resolveWriteConnection()
    {
        if ($this->_connectionResolver !== null) {
            $name = $this->_connectionResolver->resolveWriteConnection($this->_writeParams);
        } else {
            $name = array_rand($this->_writeParams);
        }
        
        $this->_writeConnName = $name;
        
        return $this->_writeParams[$name];
    }
}