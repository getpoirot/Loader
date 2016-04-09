<?php
namespace Poirot\Loader\Traits;

trait tLoaderPathStack
{
    /**
     * @var array Registered Namespaces
     */
    protected $_t_loader_pathstack_Namespaces = [
        # 'path/stack' => ['path/dir/', 'other/path/dir'],
    ];

    protected $_t_loader_pathstack_cache_SortNamespaces = false;
    protected $_t_loader_pathstack_cache_Normalized  = [];
    protected $_t_loader_pathstack_cache_Matched     = [];

    /**
     * Build Object With Provided Options
     * > Setup Aggregate Loader
     *
     * @param array $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @throws \Exception
     * @return $this
     */
    function with(array $options, $throwException = false)
    {
        # previous registered keys not replaced
        $this->_t_loader_pathstack_Namespaces = array_merge($this->_t_loader_pathstack_Namespaces, $options);
        return $this;
    }

    /**
     * Load Build Options From Given Resource
     *
     * - usually it used in cases that we have to support
     *   more than once configure situation
     *   [code:]
     *     Configurable->with(Configurable::withOf(path\to\file.conf))
     *   [code]
     *
     *
     * @param array|string $optionsRes array or file path
     *
     * @throws \InvalidArgumentException if resource not supported
     * @return array
     */
    static function withOf($optionsRes)
    {
        if (is_string($optionsRes)) {
            if (!file_exists($optionsRes))
                throw new \InvalidArgumentException(sprintf(
                    'Map file "%s" provided does not exist.',
                    $optionsRes
                ));

            $optionsRes = include $optionsRes;
        }

        if (!is_array($optionsRes))
            throw new \InvalidArgumentException(sprintf(
                'Resource must be an array, given: (%s).'
                , \Poirot\Std\flatten($optionsRes)
            ));

        return $optionsRes;
    }

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
        if ($resource === '' || empty($this->_t_loader_pathstack_Namespaces))
            return false;

        if (isset($this->_t_loader_pathstack_Namespaces[$resource])) {
            ## whole resource match exists in stack and resolved
            foreach($this->_t_loader_pathstack_Namespaces[$resource] as $resolved) {
                if ($return = $this->_t_loader_pathstack_watchResolve($resolved, $watch))
                    return $return;
            }
        }

        foreach($this->_t_loader_pathstack_cache_Matched as $namespace)
            if (strpos($resource, $namespace) === 0) {
                if ($return = $this->_t_loader_pathstack_attainFromNamespace($resource, $namespace, $watch))
                    return $return;
            }

        $matched = $this->_t_loader_pathstack_getMatchedFromStack($resource);
        $this->_t_loader_pathstack_cache_Matched = array_merge($matched, $this->_t_loader_pathstack_cache_Matched);

        // push wildcard star '*' namespace to matched if exists
        if (array_key_exists('*', $this->_t_loader_pathstack_Namespaces))
            array_push($matched, '*');


        // search for class library file:
        foreach($matched as $namespace) {
            if ($return = $this->_t_loader_pathstack_attainFromNamespace($resource, $namespace, $watch))
                return $return;
        }

        return false;
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


        if (!array_key_exists($namespace, $this->_t_loader_pathstack_Namespaces))
            $this->_t_loader_pathstack_Namespaces[$namespace] = [];

        # each registered namespace can spliced on multiple directory
        $this->_t_loader_pathstack_Namespaces[$namespace][] = $dir;

        return $this;
    }


    // ...

    protected function _t_loader_pathstack_attainFromNamespace($resource, $namespace, $watch)
    {
        ## $namespace    = 'Poirot\Loader'
        ## $resource     = 'Poirot\Loader\ClassMapAutoloader'
        ## $maskOffClass = '\ClassMapAutoloader'
        $maskOffClass = ($namespace == '*')
            ? $resource
            : substr($resource, strlen($namespace), strlen($resource));

        if (!is_array($this->_t_loader_pathstack_Namespaces[$namespace]))
            ## Allow Loader Config Defined as:
            ## 'Poirot' => __DIR__, instead of 'Poirot' => [__DIR__, ..],
            $this->_t_loader_pathstack_Namespaces[$namespace] = [$this->_t_loader_pathstack_Namespaces[$namespace]];

        foreach ($this->_t_loader_pathstack_Namespaces[$namespace] as $path) {
            $resolved =
                $this->_t_loader_pathstack_normalizeDir($path)
                . $this->_t_loader_pathstack_normalizeResourceName($maskOffClass);

            if ($return = $this->_t_loader_pathstack_watchResolve($resolved, $watch))
                return $return;
        }

        return false;
    }

    /**
     * Binary search for matching with requested resource namespace
     * @param $resource
     * @return array
     */
    protected function _t_loader_pathstack_getMatchedFromStack($resource, $rec_pathstack = null)
    {
        $matched = [];
        if (empty($rec_pathstack) && $rec_pathstack !== null)
            ## list is empty
            return $matched;

        if ($this->_t_loader_pathstack_cache_SortNamespaces !== $this->_t_loader_pathstack_Namespaces) {
            ksort($this->_t_loader_pathstack_Namespaces);
            $this->_t_loader_pathstack_cache_SortNamespaces = $this->_t_loader_pathstack_Namespaces;
        }

        // find best namespace match and list in queue:
        ## it will reduce filesystem actions to find class

        $rec_pathstack = ($rec_pathstack === null)
            ? $this->_t_loader_pathstack_Namespaces
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
            return $this->_t_loader_pathstack_getMatchedFromStack($resource, array_splice($rec_pathstack, 0, $midKey));

        // if ($term < 0)
        return $this->_t_loader_pathstack_getMatchedFromStack($resource, array_splice($rec_pathstack, $midKey, count($keys)-1));
    }

    /**
     * Speed up search by looking for whole namespace match-
     * and resolve to it
     *
     * @param $resource
     * @param $watch
     * @return false|string
     */
    protected function _t_loader_pathstack_watchResolve($resource, $watch)
    {
        ($watch !== null) ?: $watch = $this->_t_loader_pathstack_watch();
        $return   = $watch($resource);

        return $return;
    }

    /**
     * Default Watch Resolver
     * - we can manipulate the final resolvedFile by reference
     * @return callable
     */
    protected function _t_loader_pathstack_watch() {
        return function($resolvedFile) {
            ## if true resolve return $resolvedFile as result
            return !file_exists($resolvedFile) ?: $resolvedFile;
        };
    }

    /**
     * Normalize Directory Path
     *
     * @param string $dir
     *
     * @return string
     */
    protected function _t_loader_pathstack_normalizeDir($dir)
    {
        if (isset($this->_t_loader_pathstack_cache_Normalized[$dir]))
            return $this->_t_loader_pathstack_cache_Normalized[$dir];

        $dir = rtrim(strtr($dir, '\\', '/'), '/');
        $this->_t_loader_pathstack_cache_Normalized[$dir] = $dir;

        return $dir;
    }

    /**
     * Convert Class Namespace Trailing To Path
     *
     * @param string $maskOffClass
     *
     * @return string
     */
    protected function _t_loader_pathstack_normalizeResourceName($maskOffClass)
    {
        $maskOffClass = ltrim($maskOffClass, '\\');

        return '/'. $this->_t_loader_pathstack_normalizeDir($maskOffClass);
    }
}
