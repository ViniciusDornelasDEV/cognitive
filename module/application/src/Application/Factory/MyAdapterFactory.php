<?php

namespace Application\Factory;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Platform\Mysql as MysqlPlatform;
use Zend\Db\Adapter\Driver\Pdo;
use Zend\Session\Container;
/**
 * MyAdapterFactory class.
 *
 * @author  Vinicius Silva
 * @version 1.0
 */
class MyAdapterFactory
{
    /**
     * Key in the config file.
     * 
     * @var mixed
     * @access protected
     */
    protected $configKey;
    
    /**
     * Service locator.
     * 
     * @var mixed
     * @access protected
     */
    protected $serviceLocator;
    
    /**
     * __construct function.
     * 
     * @access public
     * @param ServiceLocatorInterface $serviceLocator
     * @return void
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
    
    /**
     * setConfigKey function.
     * 
     * @access public
     * @param mixed $key
     * @return void
     */
    public function setConfigKey()
    {   
        $this->configKey = 'db';
    }

    /**
     * getConfigKey function.
     * 
     * @access public
     * @return void
     */
    public function getConfigKey()
    {
        return $this->configKey;
    }
    
    /**
     * Not really sure what this does...
     * 
     * @access public
     * @return void
     */
    public function getPlatform()
    {    
        $config = $this->serviceLocator->get('Config');
        $connectionParameters = $config[$this->getConfigKey()];
        $connection = new Pdo\Connection($connectionParameters);
        $driver = new Pdo\Pdo($connection);
        return new MysqlPlatform($driver);
    }
    
    /**
     * Factory method for creating the service class.
     * 
     * @access public
     * @return void
     */
    public function createService()
    {
        $config = $this->serviceLocator->get('Config');
        
        //procurar na sessão os dados de conexão
        $container = new Container();
        if(isset($container->database)){
            $config['db']['dsn'] = 'mysql:dbname='.$container->database.';host=localhost';
        }
        
        return new Adapter($config[$this->getConfigKey()]);
    }

}