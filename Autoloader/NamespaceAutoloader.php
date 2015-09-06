<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\PathStackTrait;

if (class_exists('Poirot\\Loader\\Autoloader\\NamespaceAutoloader'))
    return;

require_once __DIR__ . '/AbstractAutoloader.php';

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
    function __construct(array $namespaces = [])
    {
        if ($namespaces)
            $this->setStackArray($namespaces);
    }

    /**
     * Autoload Class
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return void
     */
    function resolve($class)
    {
        $this->__t_resolve($class, function(&$resolvedFile) {
            if (file_exists($file = $resolvedFile.'.php')) {
                require_once $file;

                ## stop propagation
                return true;
            }

            return $resolvedFile;
        });
    }
}
