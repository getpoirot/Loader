<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\Traits\tLoaderPathStack;

if (class_exists('Poirot\\Loader\\Autoloader\\LoaderAutoloadNamespace', false))
    return;

require_once __DIR__ . '/aLoaderAutoload.php';
require_once __DIR__ . '/../Traits/tLoaderPathStack.php';

class LoaderAutoloadNamespace extends aLoaderAutoload
{
    use tLoaderPathStack {
        tLoaderPathStack::resolve as protected __t_resolve;
    }

    /**
     * Construct
     *
     * @param array $namespaces
     */
    function __construct($namespaces = null)
    {
        if ($namespaces !== null)
            $this->with(self::withOf($namespaces));
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
