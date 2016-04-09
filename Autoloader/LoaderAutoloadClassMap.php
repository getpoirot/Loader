<?php
namespace Poirot\Loader\Autoloader;

use Poirot\Loader\Traits\tLoaderMapResource;

if (class_exists('Poirot\\Loader\\Autoloader\\LoaderAutoloadClassMap', false))
    return;

require_once __DIR__ . '/aLoaderAutoload.php';

class LoaderAutoloadClassMap extends aLoaderAutoload
{
    use tLoaderMapResource {
        tLoaderMapResource::resolve as protected __t_resolve;
    }

    /**
     * Construct
     *
     * @param array|string $options
     */
    function __construct($options = null)
    {
        if ($options !== null)
            $this->with(self::withOf($options));
    }

    /**
     * Autoload Class Callable
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return mixed
     */
    function resolve($class)
    {
        $resolved = $this->__t_resolve($class);
        if ($resolved)
            require_once $resolved;

        return $resolved;
    }
}
