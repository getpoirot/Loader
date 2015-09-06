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
     * @param string   $resource
     * @param callable $watch
     *
     * @return false|mixed
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

        $resolved = [];

        // search for class library file:
        foreach($matched as $namespace) {
            ## $namespace    = 'Poirot\Loader'
            ## $class        = 'Poirot\Loader\ClassMapAutoloader'
            ## $maskOffClass = '\ClassMapAutoloader'
            $maskOffClass = substr($resource, strlen($namespace), strlen($resource));

            foreach($this->__pathStacks[$namespace] as $dir) {
                $resolvedFile =
                    $this->__normalizeDir($dir)
                    . $this->__normalizeResourceName($maskOffClass);

                ($watch === null) ?: $resolvedFile = $watch($resolvedFile);
                if ($resolvedFile === false)
                    return false;

                $resolved[] = $resolvedFile;
            }
        }

        return (empty($resolved)) ? false : array_reverse($resolved);
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
     * @param string $namespace
     * @param string $dir
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setStack($namespace, $dir)
    {
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
