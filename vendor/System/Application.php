<?php 

namespace System;

class Application {
    
    /**
    * Container
    *
    * @var array
    */
    
    private $container = [];
    
    
    
    /*
    * Application object
    *
    * @var System/application
    */
    
    private static $instance;
    
    /*
    * Constructor
    *
    * @param System/file $file
    */
    
    private function __construct (File $file)
    {
      
        $this-> share('file', $file);
        
        $this->registerClases();
        
        static::$instance = $this;
        
        $this->loadHelpers();  
    }
    
    /*
    * Get application Instance
    * 
    * @param /system/ file $file
    * @return \system\application
    */
    
    public static function getInstance($file = null) //??????
    {
        if (is_null(static::$instance))
        {
            static::$instance = new static($file);
        }
        
        return static::$instance;
    }
    
    /*
    * share the given key through the Applicatoin
    *
    * @ param string key
    * @ param value mixed
    * @ return mixed
    */
    
    public function share($key, $value) 
    {
        $this ->container[$key] = $value;
    }
    
    
    
    /*
    * Get shared value dynamically
    *
    * @param string $key
    * @return mixed
    */

    public function __get($key)
    {
        return $this->get($key);
    }
    
    
    
    /*
    * Register Clases in spl autoload register
    *
    * @ return void
    */
    
    private function registerClases() 
    {
        spl_autoload_register([$this, 'load']);
    }
    
        
    /*
    * load Class through autoloading
    *
    * @ param string $class
    * @ return void
    */

    public function load($class)
    {

        if (strpos($class, 'App') === 0) 
        {
            
            $file = $class . '.php';
        } 
        else 
        {
            // get the class from vendor
            
            $file = "vendor/" . $class . '.php';
        }
        
        if ($this ->file -> exists($file))
        {
            $this ->file -> call($file);
        }
            
       
    }
    
    
    
    /*
    * Core classes
    * Get all core classes with there aliases
    *
    * @return array 
    */
        
    private function coreClasses()
    {
        return [
            'request'      => 'System\\Http\\Request',
            'response'     => 'System\\Http\\Response',
            'session'      => 'System\\Session',
            'route'        => 'System\\Route',
            'cookie'       => 'System\\Cookie',
            'load'         => 'System\\Loader',
            'html'         => 'System\\Html',
            'url'         => 'System\\url',
            'db'           => 'System\\Database',
            'view'         => 'System\\View\\ViewFactory',

        ];
    }
    
    
    
    
    /*
    * Get shared Value
    *
    * @param string $key
    * @return mixed
    */

    public function get($key)
    {
        if (! $this->isSharing($key))
        {
            if($this->isCoreAlias($key))
            {
                $this->share($key, $this->createNewCoreObject($key));
            }
        }
        return  $this->container[$key]; 
    }
    
    /*
    * Determine if the given key is shared through Application
    *
    * @param string $key
    * @return bool
    */
    
    public function isSharing($key)
    {
        return isset($this->container[$key]);
    } 
    
    
    
    /*
    * Determine if the given key is an alias to core class 
    *
    * @param string $alias
    * @return bool
    */
    
    private function isCoreAlias($alias)
    {
        
        $coreClasses = $this->coreClasses();
        
        return isset($coreClasses[$alias]);
    }
    
    
    
    /*
    * Create new object for the cor class based on the given alias
    *
    * @param string $alias
    * @return object
    */
    
    private function createNewCoreObject($alias)
    {
        $coreClasses = $this->coreClasses();
        
        $object = $coreClasses[$alias];
        
        return new $object($this);
    }
    
    
    /*
    * Run the application
    *
    * @return void
    */
    
    public function run() 
    {
        $this->session->start();
        
        $this->request->prepareUrl();
        
        $this->file->call('App/index.php');
        
        list($controller, $method, $arguments) = $this->route->getProperRoute();
        
        $output =(string) $this->load->action($controller, $method, $arguments);
        
        $this->response->setOutput($output);
        
        $this->response->send(); 
    } 
    
    /*
    * load helbers file
    *
    * @return void
    */
    
    private function loadHelpers() 
    {
        $this->file->call('vendor/helpers.php');
    }
}