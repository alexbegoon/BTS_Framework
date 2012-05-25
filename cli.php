<?php

date_default_timezone_set('Europe/London');

if (php_uname("n") == "dan-laptop") {
    define("APPLICATION_ENV", "development");
}
elseif (strpos(__FILE__, "/home/admin") !== false) {
    define("APPLICATION_ENV", "staging");
}
else {
    define("APPLICATION_ENV", "production");
}

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors',true);

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/library'),
    get_include_path(),
)));

require_once('Zend/Loader/Autoloader.php');
Zend_Loader_Autoloader::getInstance()->registerNamespace("App_");
Zend_Loader_Autoloader::getInstance()->registerNamespace("BTS_");
Zend_Loader_Autoloader::getInstance()->registerNamespace("Zend_");

try {
    $opts = new Zend_Console_Getopt(
        array(
            'help|h' => 'Displays usage information.',
            'action|a=s' => 'Action to perform in format of module.controller.action',
            'verbose|v' => 'Verbose messages will be dumped to the default output.',
            'development|d' => 'Enables development mode.',
            'params|p=s' => 'Parameters to controller.',
        )
    );
    $opts->parse();
}
catch (Zend_Console_Getopt_Exception $e) {
    exit($e->getMessage() ."\n\n". $e->getUsageMessage());
}

if (isset($opts->h)) {
    echo $opts->getUsageMessage();
    exit;
}

if(isset($opts->a)) {
    $reqRoute = array_reverse(explode('.', $opts->a));
    list($action, $controller, $module) = $reqRoute;
    
    $request = new Zend_Controller_Request_Simple($action, $controller, $module);
    if ($opts->getOption("p")) {
        $params = array();
        foreach (explode(",", $opts->p) as $param) {
            $param = trim($param);
            list($key, $value) = explode("=", $param);
            $params[$key] = $value;
        }
        $request->setParams($params);
    }
    
    $front = Zend_Controller_Front::getInstance();
    $front->setParam('disableOutputBuffering', true);
    
    $front->setRequest($request);
    $front->setRouter(new BTS_Controller_Router_Cli());

    $front->setResponse(new Zend_Controller_Response_Cli());
    
    $front->throwExceptions(true);
    $front->addModuleDirectory(dirname(__FILE__) . '/application/modules/');

    $front->dispatch();
}
