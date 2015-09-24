<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\PathStackTrait;

if (class_exists('Poirot\\Loader\\Autoloader\\NamespaceAutoloader'))
    return;

require_once __DIR__ . '/AbstractAutoloader.php';
require_once __DIR__ . '/../PathStackTrait.php';

class NamespaceAutoloader extends AbstractAutoloader
{
    use PathStackTrait {
        PathStackTrait::resolve as protected __t_resolve;
    }

    /**
     * Construct
     *
     * @param array $namespaces
     */
    function __construct($namespaces = null)
    {
        if ($namespaces !== null)
            $this->from($namespaces);
    }

    /**
     * Autoload Class
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return mixed
     */
    function resolve($class)
    {
        return $this->__t_resolve($class, function(&$resolvedFile) {
            $file = $resolvedFile.'.php';
            if (!file_exists($file))
                return false;
            
            require_once $file;

            ## stop propagation
            return true;
        });
    }
}
