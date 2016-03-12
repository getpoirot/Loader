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

    protected $_c__sort_stack = false;
    protected $_c__normalize  = [];
    protected $_c__matched     = [];

    /**
     * Resolve To Resource
     *
     * $watch:
     * function(&$resolved) {
     *    $resolved .= '.php';
     *    ## to stop propagation, and return $resolved
     *    return true;
     * }
     *
     * @param string   $resource
     * @param \Closure $watch
     *
     * @return false|array|mixed
     */
    function resolve($resource, \Closure $watch = null)
    {
        $resource = (string) $resource;
        if ($resource === '' || empty($this->__pathStacks))
            return false;

        if (isset($this->__pathStacks[$resource])) {
            ## whole resource match exists in stack and resolved
            foreach($this->__pathStacks[$resource] as $resolved) {
                if ($return = $this->__watchResolve($resolved, $watch))
                    return $return;
            }
        }

        foreach($this->_c__matched as $namespace)
            if (strpos($resource, $namespace) === 0) {
                if ($return = $this->attainFromNamespace($resource, $namespace, $watch))
                    return $return;
            }

        $matched = $this->_getMatchedFromStack($resource);
        $this->_c__matched = array_merge($matched, $this->_c__matched);

        // push wildcard star '*' namespace to matched if exists
        if (array_key_exists('*', $this->__pathStacks))
            array_push($matched, '*');


        // search for class library file:
        foreach($matched as $namespace) {
            if ($return = $this->attainFromNamespace($resource, $namespace, $watch))
                return $return;
        }

        return false;
    }

    protected function attainFromNamespace($resource, $namespace, $watch)
    {
        ## $namespace    = 'Poirot\Loader'
        ## $resource     = 'Poirot\Loader\ClassMapAutoloader'
        ## $maskOffClass = '\ClassMapAutoloader'
        $maskOffClass = ($namespace == '*')
            ? $resource
            : substr($resource, strlen($namespace), strlen($resource));

        if (!is_array($this->__pathStacks[$namespace]))
            ## Allow Loader Config Defined as:
            ## 'Poirot' => __DIR__, instead of 'Poirot' => [__DIR__, ..],
            $this->__pathStacks[$namespace] = [$this->__pathStacks[$namespace]];

        foreach ($this->__pathStacks[$namespace] as $path) {
            $resolved =
                $this->__normalizeDir($path)
                . $this->__normalizeResourceName($maskOffClass);

            if ($return = $this->__watchResolve($resolved, $watch))
                return $return;
        }

        return false;
    }

    /**
     * Binary search for matching with requested resource namespace
     * @param $resource
     * @return array
     */
    protected function _getMatchedFromStack($resource, $rec_pathstack = null)
    {
        $matched = [];
        if (empty($rec_pathstack) && $rec_pathstack !== null)
            ## list is empty
            return $matched;

        if ($this->_c__sort_stack !== $this->__pathStacks) {
            ksort($this->__pathStacks);
            $this->_c__sort_stack = $this->__pathStacks;
        }

        // find best namespace match and list in queue:
        ## it will reduce filesystem actions to find class

        $rec_pathstack = ($rec_pathstack === null)
            ? $this->__pathStacks
            : $rec_pathstack;

        $keys = array_keys($rec_pathstack);

        ## grab the middle
        $midKey  = intval(count($keys) / 2);
        $current = $keys[$midKey];

        if ($current == '*')
            return $matched;

        $term = strncasecmp($current, $resource, strlen($current));
        if ($term === 0) {
            ## match resource in stack
            array_push($matched, $current);

            ## looking fore next and previous keys to match

            for($i = $midKey-1; $i >=0; $i--) {
                ### previous
                $current = $keys[$i];
                $term    = strncasecmp($current, $resource, strlen($current));
                if ($term !== 0)
                    break;

                ### only match for keys that contains resource name
                array_push($matched, $current);
            }

            for($i = $midKey+1; $i < count($keys); $i++) {
                ### next
                $current = $keys[$i];
                $term    = strncasecmp($current, $resource, strlen($current));
                if ($term !== 0)
                    break;

                ### only match for keys that contains resource name
                array_unshift($matched, $current); ### nearest to namespace
            }

            return $matched;
        }

        if ($term > 0)
            return $this->_getMatchedFromStack($resource, array_splice($rec_pathstack, 0, $midKey));

        // if ($term < 0)
        return $this->_getMatchedFromStack($resource, array_splice($rec_pathstack, $midKey, count($keys)-1));
    }

    /**
     * Speed up search by looking for whole namespace match-
     * and resolve to it
     *
     * @param $resource
     * @param $watch
     * @return false|string
     */
    protected function __watchResolve($resource, $watch)
    {
        ($watch !== null) ?: $watch = $this->__watch();
        $return   = $watch($resource);

        return $return;
    }

    /**
     * Default Watch Resolver
     * - we can manipulate the final resolvedFile by reference
     * @return callable
     */
    protected function __watch() {
        return function(&$resolvedFile) {
            ## if true resolve return $resolvedFile as result
            return !file_exists($resolvedFile) ?: $resolvedFile;
        };
    }


    /**
     * Set Stack Namespace Directory Pair
     *
     * ! Associative Array as [namespace => dir]
     *
     * @param array|string $resource
     *
     * @return $this
     */
    function from($resource)
    {
        if (is_string($resource))
            $this->fromFile($resource);
        elseif (is_array($resource))
            $this->fromArray($resource);
        else
            throw new \InvalidArgumentException;

        return $this;
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
    function fromArray(array $namespaces)
    {
        $this->__pathStacks = array_merge($this->__pathStacks, $namespaces);

        return $this;
    }

    /**
     * Set Stack Namespace Directory Pair From File
     *
     * ! File Return Associative Array as [namespace => dir]
     *
     * @param string $file
     *
     * @return $this
     */
    function fromFile($file)
    {
        if (!file_exists($file))
            return $this;

        $namespaces = include $file;
        $this->fromArray($namespaces);

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
        $namespace = trim($namespace, '\\');


        if (!array_key_exists($namespace, $this->__pathStacks))
            $this->__pathStacks[$namespace] = [];

        # each registered namespace can spliced on multiple directory
        $this->__pathStacks[$namespace][] = $dir;

        return $this;
    }


    // ...

    /**
     * Normalize Directory Path
     *
     * @param string $dir
     *
     * @return string
     */
    protected function __normalizeDir($dir)
    {
        if (isset($this->_c__normalize[$dir]))
            return $this->_c__normalize[$dir];

        $dir = rtrim(strtr($dir, '\\', '/'), '/');
        $this->_c__normalize[$dir] = $dir;

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
