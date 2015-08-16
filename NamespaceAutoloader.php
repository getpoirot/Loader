<?php
namespace Poirot\Autoloader;

use Poirot\Autoloader\Interfaces\iSplAutoloader;

if (class_exists('Poirot\\Autoloader\\NamespaceAutoloader'))
    return;

require_once __DIR__.'/Interfaces/iSplAutoloader.php';

class NamespaceAutoloader implements iSplAutoloader
{
    /**
     * @var array Registered Namespaces
     */
    protected $__namespaces = [
        # 'namespace' => ['path/dir/', 'other/path/dir'],
    ];

    /**
     * Construct
     *
     * @param array $namespaces
     */
    function __construct(array $namespaces = [])
    {
        if ($namespaces)
            $this->setNamespaces($namespaces);
    }

    // Implement iSplAutoloader:

    /**
     * Register to spl autoloader
     *
     * <code>
     * spl_autoload_register(callable);
     * </code>
     *
     * @return void
     */
    function register()
    {
        spl_autoload_register([$this, 'attainClass']);
    }

    /**
     * Unregister from spl autoloader
     *
     * ! using same callable on register
     *
     * @return void
     */
    function unregister()
    {
        spl_autoload_unregister([$this, 'attainClass']);
    }

    // Implement StandardAutoloader Specific:

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
        // find best namespace match and list in queue:
        ## it will reduce filesystem actions to find class
        $matched = []; $nearest = '';
        foreach(array_keys($this->__namespaces) as $namespace) {
            if (strpos($class, $namespace) === false)
                continue;

            if (strlen($namespace) > strlen($nearest)) {
                array_unshift($matched, $namespace);
                $nearest = $namespace;
            } else {
                array_push($matched, $namespace);
            }
        }

        // search for class library file:
        foreach($matched as $namespace) {
            ## $namespace    = 'Poirot\Autoloader'
            ## $class        = 'Poirot\Autoloader\ClassMapAutoloader'
            ## $maskOffClass = '\ClassMapAutoloader'
            $maskOffClass = substr($class, strlen($namespace), strlen($class));

            foreach($this->__namespaces[$namespace] as $dir) {
                $resolvedFile =
                    $this->__normalizeDir($dir)
                    . $this->__classToFilePath($maskOffClass);

                if (file_exists($resolvedFile)) {
                    require $resolvedFile;

                    return; ## file resolved return from function
                }
            }
        }
    }

    /**
     * Set Namespaces Directory Pair
     *
     * @param array $namespaces Associative Array as namespace=>dir
     *
     * @return $this
     */
    function setNamespaces(array $namespaces)
    {
        foreach($namespaces as $namespace => $dir)
            if (is_string($namespace) && is_string($dir))
                $this->setNamespace($namespace, $dir);

        return $this;
    }

    /**
     * Set Namespace Directory Pair
     *
     * @param string $namespace
     * @param string $dir
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setNamespace($namespace, $dir)
    {
        if (!is_dir($dir))
            throw new \InvalidArgumentException(sprintf(
                'Directory "%s" not available.'
                , $dir
            ));

        $namespace = trim($namespace, '\\');
        if (!array_key_exists($namespace, $this->__namespaces))
            $this->__namespaces[$namespace] = [];

        # each registered namespace can spliced on multiple directory
        $this->__namespaces[$namespace][] = $dir;

        return $this;
    }

    /**
     * Normalize Directory Path
     *
     * @param string $dir
     *
     * @return string
     */
    protected function __normalizeDir($dir)
    {
        $dir = (strpos($dir, '\\') !== false) ? str_replace('\\', '/', $dir) : $dir;
        $dir = rtrim($dir, '/');

        return $dir;
    }

    /**
     * Convert Class Namespace Trailing To Path
     *
     * @param string $maskOffClass
     *
     * @return string
     */
    protected function __classToFilePath($maskOffClass)
    {
        $maskOffClass = ltrim($maskOffClass, '\\');

        return '/'. $this->__normalizeDir($maskOffClass).'.php';
    }
}
