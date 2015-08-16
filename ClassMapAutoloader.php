<?php
namespace Poirot\Autoloader;

if (class_exists('Poirot\\Autoloader\\ClassMapAutoloader'))
    return;

require_once __DIR__.'/AbstractAutoloader.php';

class ClassMapAutoloader extends AbstractAutoloader
{
    /**
     * Autoload Class Callable
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return void
     */
    function attainClass($class)
    {
        // TODO: Implement attainClass() method.
    }
}
