<?php

date_default_timezone_set('Europe/London');

define("ROOT_DIR", dirname($_SERVER['SCRIPT_FILENAME']));

if (file_exists(ROOT_DIR . "/public/ini.php")) {
    // allow php.ini settings to be overridden on an application level, and have the same settings
    // applied to the cli script.
    include ROOT_DIR . "/public/ini.php";
}

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors',true);

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(ROOT_DIR) . '/application');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(realpath(ROOT_DIR . '/library'), get_include_path())));

//require_once('Zend/Loader/Autoloader.php');
//// Should probably take this from the main config file...
//Zend_Loader_Autoloader::getInstance()->registerNamespace("App_");
//Zend_Loader_Autoloader::getInstance()->registerNamespace("BTS_");
//Zend_Loader_Autoloader::getInstance()->registerNamespace("Zend_");

try {
    include_once "Zend/Console/Getopt.php";
    $opts = new Zend_Console_Getopt(
        array(
            'help|h' => 'Displays usage information.',
            'action|a=s' => 'Action to perform in format of module.controller.action',
            'verbose|v' => 'Verbose messages will be dumped to the default output.',
            'env|e=s' => 'Set environment mode (defaults to "production").',
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

if (isset($opts->e)) {
    define("APPLICATION_ENV", $opts->e);
}
else {
    define("APPLICATION_ENV", "production");
}

if(isset($opts->a)) {
    /** Zend_Application */
    require_once 'Zend/Application.php';

    // Create application, bootstrap, and run
    $application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . '/configs/application.ini'
    );
    $application->bootstrap();
    
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
    if ($opts->getOption("v")) {
        $request->setParam("_debug", true);
    }
    
    $front = Zend_Controller_Front::getInstance();
    $front->setParam('disableOutputBuffering', true);
    
    $front->setRequest($request);
    $front->setRouter(new BTS_Controller_Router_Cli());

    $front->setResponse(new Zend_Controller_Response_Cli());
    
    $front->throwExceptions(true);
    $front->addModuleDirectory(dirname($_SERVER['SCRIPT_FILENAME']) . '/application/modules/');

    $front->dispatch();
}
