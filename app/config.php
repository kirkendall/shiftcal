<?php
$DBUSER = 'root';
$DBPASS = '';
$DBDB   = 'pp';
$DBHOST = '127.0.0.1';

date_default_timezone_set('America/Los_Angeles');

/**
 * Automatically includes classes
 * 
 * @throws Exception
 * 
 * @param  string $class_name  Name of the class to load
 * @return void
 */
function __autoload($class_name)
{
    // Customize this to your root Flourish directory
    $flourish_root = getcwd() . '/../vendor/flourish/';

    $file = $flourish_root . $class_name . '.php';

    if (file_exists($file)) {
        include $file;
        return;
    }
    
    throw new Exception('The class ' . $class_name . ' could not be loaded');
}
