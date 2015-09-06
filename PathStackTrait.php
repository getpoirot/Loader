<?php
namespace Poirot\Loader;

trait PathStackTrait
{
    /**
     * @var array Registered Namespaces
     */
    protected $__pathStacks = [
        # 'path/stack' => ['path/dir/', 'other/path/dir'],
    ];

    /**
     * Resolve To Resource
     *
     * $watch
     * function(&$resolved) {
     *    $resolved .= '.php';
     *    ## to stop propagation, and return $resolved
     *    return true;
     * }
     *
     * @param string   $resource
     * @param callable $watch
     *
     * @return false|array|mixed
     */
    function resolve($resource, \Closure $watch = null)
    {
        // find best namespace match and list in queue:
        ## it will reduce filesystem actions to find class
        $matched = []; $nearest = '';
        foreach(array_keys($this->__pathStacks) as $namespace) {
            if (strpos($resource, $namespace) === false)
                continue;

            if (strlen($namespace) > strlen($nearest)) {
                array_unshift($matched, $namespace);
                $nearest = $namespace;
            } else {
                array_push($matched, $namespace);
            }
        }
        // push wildcard star '*' namespace to matched if exists
        if (array_key_exists('*', $this->__pathStacks))
            array_push($matched, '*');

        // search for class library file:
        foreach($matched as $namespace) {
            ## $namespace    = 'Poirot\Loader'
            ## $class        = 'Poirot\Loader\ClassMapAutoloader'
            ## $maskOffClass = '\ClassMapAutoloader'
            $maskOffClass = ($namespace == '*')
                ? $resource
                : substr($resource, strlen($namespace), strlen($resource));

            foreach($this->__pathStacks[$namespace] as $dir) {
                $resolvedFile =
                    $this->__normalizeDir($dir)
                    . $this->__normalizeResourceName($maskOffClass);

                if ($watch !== null) {
                    $wResult = $watch($resolvedFile);
                    if ($wResult === true)
                        ### return achieved library
                        return $resolvedFile;
                }
            }
        }

        return false;
    }

    /**
     * Set Stack Namespace Directory Pair
     *
     * ! Associative Array as [namespace => dir]
     *
     * @param array $namespaces
     *
     * @return $this
     */
    function setStackArray(array $namespaces)
    {
        foreach($namespaces as $namespace => $dir)
            if (is_string($namespace) && is_string($dir))
                $this->setStack($namespace, $dir);

        return $this;
    }

    /**
     * Set Stack Directory Pair
     *
     * - namespace can be '*' and checked after nearest
     *   namespace detection
     *
     * @param string $namespace
     * @param string $dir
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setStack($namespace, $dir)
    {
        if (!is_string($namespace) || empty($namespace))
            throw new \InvalidArgumentException(sprintf(
                'Namespace must be valid string but (%s) given.'
                , is_object($namespace) ? get_class($namespace) : gettype($namespace).'('.$namespace.')'
            ));

        if (!is_dir($dir))
            throw new \InvalidArgumentException(sprintf(
                'Directory "%s" not available.'
                , $dir
            ));

        $namespace = trim($namespace, '\\');
        if (!array_key_exists($namespace, $this->__pathStacks))
            $this->__pathStacks[$namespace] = [];

        # each registered namespace can spliced on multiple directory
        $this->__pathStacks[$namespace][] = $dir;

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
    protected function __normalizeResourceName($maskOffClass)
    {
        $maskOffClass = ltrim($maskOffClass, '\\');

        return '/'. $this->__normalizeDir($maskOffClass);
    }
}
