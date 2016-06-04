<?php
namespace Poirot\Loader\Traits;

require_once __DIR__.'/../_functions.php';

use Closure;

trait tLoaderNamespaceStack
{
    ## @see fixes/LoaderNamespaceStack;
    ## Code Clone <begin> =================================================================
    /**
     * @var array Registered Namespaces
     */
    protected $_t_loader_namespacestack_Namespaces = array(
        # 'path/stack' => ['path/dir/', 'other/path/dir'],
    );

    protected $_t_loader_namespacestack_cache_SortNamespaces = false;
    protected $_t_loader_namespacestack_cache_Matched     = array(
        // 'P' => ['Poirot', 'Poirot\Loader'],
        // 'M' => ['mhndev', 'mhndev\package']
    );


    /**
     * Set Bunch Of Namespace Stack Resource/Directory Pair
     *
     * @param array $namespaces
     *
     * @return $this
     */
    function setResources(array $namespaces)
    {
        # previous registered keys not replaced
        $this->_t_loader_namespacestack_Namespaces = array_merge(
            $this->_t_loader_namespacestack_Namespaces
            , $namespaces
        );

        ## clear matched resource cache
        $this->_t_loader_namespacestack_cache_Matched = array();

        return $this;
    }

    /**
     * Add Namespace Stack Resource/Directory Pair
     *
     * - namespace can be '*'
     *   the star wildcard will check with watch-
     *   for any resource that not detect namespace match
     *
     * @param string $name
     * @param string $resource  Directory Path Or Any Resource Watched
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function addResource($name, $resource)
    {
        $name = trim($name, \Poirot\Loader\SEPARATOR_NAMESPACES);

        if (!array_key_exists($name, $this->_t_loader_namespacestack_Namespaces))
            $this->_t_loader_namespacestack_Namespaces[$name] = array();

        $this->_t_loader_namespacestack_fixResourceList($name);

        # each registered namespace can spliced on multiple directory
        $this->_t_loader_namespacestack_Namespaces[$name][] = $resource;

        ## clear matched resource cache
        $fc = strtoupper($name[0]);
        $this->_t_loader_namespacestack_cache_Matched[$fc] = array();

        return $this;
    }

    /**
     * Resolve To Resource
     *
     * $watch:
     * function($name, $resource, $match) {
     *    ## $match    = 'Poirot\Loader'
     *    ## $resource = '/var/www/html/vendor/Loader'
     *    ## $name     = 'Poirot\Loader\ClassMapAutoloader'
     * }
     *
     * @param string   $name
     * @param Closure  $watch
     *
     * @return false|mixed
     */
    function resolve($name, Closure $watch = null)
    {
        $name = trim((string) $name, \Poirot\Loader\SEPARATOR_NAMESPACES);
        if ($name === '' || empty($this->_t_loader_namespacestack_Namespaces))
            return false;

        if ($watch === null)
            $watch = $this->watch; // given from construct

        ## Match Whole Resource Name Exists In Stack --------------------------------------------------------------
        #- e.g with new \PathTo\ThisIsClassName() -
        #- 'PathTo\ThisIsClassName' => __DIR__.'/PathTo/ThisIsClassName.php',
        if (array_key_exists($name, $this->_t_loader_namespacestack_Namespaces)) {
            if (false !== $return = $this->_t_loader_namespacestack_watchAndResolve($name, $name, $watch))
                return $return;
            else {
                ## Continue it may find on other matches
            }
        }

        ## Check Request Name Against Registered and Save List As Cache  ------------------------------------------
        $fc = strtoupper($name[0]);
        if (isset($this->_t_loader_namespacestack_cache_Matched[$fc])) {
            ## given match from cache
            foreach($this->_t_loader_namespacestack_cache_Matched[$fc] as $registeredNames)
                if (strpos($name, $registeredNames) === 0) {
                    (isset($matched)) ?: $matched = array();
                    $matched[] = $registeredNames;
                }
        }

        // ..

        if (!isset($matched)) {
            ## not loaded from internal cache
            $matched = $this->_t_loader_namespacestack_getMatchedFromStack($name);
            $this->_t_loader_namespacestack_cache_Matched[$fc] = array_merge(
                $matched
                , isset($this->_t_loader_namespacestack_cache_Matched[$fc])
                ? $this->_t_loader_namespacestack_cache_Matched[$fc]
                : array()
            );
        }

        ### push wildcard star '*' namespace to matched if exists
        if (array_key_exists('*', $this->_t_loader_namespacestack_Namespaces))
            array_push($matched, '*');

        foreach($matched as $match) {
            $return = $this->_t_loader_namespacestack_watchAndResolve($name, $match, $watch);
            if ($return !== false) return $return;
        }

        return false;
    }


    // ...

    /**
     * Binary search for matching with requested resource namespace
     *
     * note: Assume you register this namespaces stack:
     *         'Poirot\\' => ..,
     *         'Poirot\\Loader' => ..,
     *         'Poirot\\Loader\\LoaderNamespaceStack' => ..,
     *
     *       The result of this method for $name="Poirot\Loader" is:
     *         ['Poirot\Loader', 'Poirot']
     *         from most match case to lowest, it means resource may found
     *         in list.
     *
     *
     * @param $name
     *
     * @return array
     */
    protected function _t_loader_namespacestack_getMatchedFromStack($name, $recursivelyNamespaceStack = null)
    {
        $matched = array();
        if (empty($recursivelyNamespaceStack) && $recursivelyNamespaceStack !== null)
            ## list is empty
            return $matched;

        if ($this->_t_loader_namespacestack_cache_SortNamespaces !== $this->_t_loader_namespacestack_Namespaces) {
            ksort($this->_t_loader_namespacestack_Namespaces);
            $this->_t_loader_namespacestack_cache_SortNamespaces = $this->_t_loader_namespacestack_Namespaces;
        }

        // find best namespace match and list in queue:
        ## it will reduce filesystem actions to find class

        $recursivelyNamespaceStack = ($recursivelyNamespaceStack === null)
            ? $this->_t_loader_namespacestack_Namespaces
            : $recursivelyNamespaceStack;

        $keys = array_keys($recursivelyNamespaceStack);

        ## grab the middle
        $midKey  = intval(count($keys) / 2);
        $curRegisteredName = trim($keys[$midKey], \Poirot\Loader\SEPARATOR_NAMESPACES);

        if ($curRegisteredName == '*')
            return $matched;

        $term = strncasecmp($curRegisteredName, $name, strlen($curRegisteredName));
        if ($term === 0) {
            ## match resource in stack
            array_push($matched, $curRegisteredName);

            ## looking fore next and previous keys to match

            for($i = $midKey-1; $i >=0; $i--) {
                ### previous
                $curRegisteredName = trim($keys[$i], \Poirot\Loader\SEPARATOR_NAMESPACES);
                $term    = strncasecmp($curRegisteredName, $name, strlen($curRegisteredName));
                if ($term !== 0)
                    break;

                ### only match for keys that contains resource name
                array_push($matched, $curRegisteredName);
            }

            for($i = $midKey+1; $i < count($keys); $i++) {
                ### next
                $curRegisteredName = trim($keys[$i], \Poirot\Loader\SEPARATOR_NAMESPACES);
                $term    = strncasecmp($curRegisteredName, $name, strlen($curRegisteredName));
                if ($term !== 0)
                    break;

                ### only match for keys that contains resource name
                array_unshift($matched, $curRegisteredName); ### nearest to namespace
            }

            return $matched;
        }

        ## Its Less Than Current Name. e.g. Poirot\Auth < Poirot\Loader
        if ($term > 0)
            return $this->_t_loader_namespacestack_getMatchedFromStack(
                $name
                , array_splice($recursivelyNamespaceStack, 0, $midKey)
            );

        // if ($term < 0)
        ## Its Greater Than Current Name. e.g. Poirot\Std > Poirot\Loader
        $matched = $this->_t_loader_namespacestack_getMatchedFromStack(
            $name
            , array_splice($recursivelyNamespaceStack, $midKey, count($keys)-1)
        );

        if (!$matched) {
            ## looking from top of stack the namespace part may match
            /* In case of Poirot\Std that must match
             * 'Poirot' => __DIR__,  <--- Here -----
             * 'Poirot\\Loader' => __DIR__.'/Loader',  <--- we first pick this from list
             * .... Part 2
             * 'Poirot\\Loader\\LoaderNamespaceStack' => __DIR__.'/Loader/LoaderNamespaceStack.php',
             *
             * Std is greater So Looking in Part 2 but not match here
             * but maybe found in the top of the list
             */
            foreach($keys as $canMatch) {
                if(0 === strncasecmp($canMatch, $name, strlen($canMatch)))
                    array_push($matched, $canMatch);
                else
                    ## match can't be found because array is sorted array
                    break;
            }
        }

        return $matched;
    }

    /**
     * - usually watch closure implemented by extended classes
     *   it's just simple resolver filter
     *
     * @see LoaderNamespaceStack::resolve
     *
     * @param string  $match        Resource Match Name
     * @param Closure $resolveWatch Watch Resource To Resolve
     *
     * @return false|mixed
     */
    protected function _t_loader_namespacestack_watchAndResolve($name, $match, $resolveWatch)
    {
        ($resolveWatch !== null) ?: $resolveWatch = function($resource) {
            return ($resource) ? $resource : false;
        };


        $this->_t_loader_namespacestack_fixResourceList($match);

        $return = false;
        foreach($this->_t_loader_namespacestack_Namespaces[$match] as $resource) {
            $return = $resolveWatch($name, $resource, $match);
        }

        return $return;
    }

    /**
     * in case of that namespace exists but not defined as array
     * [ 'Path\To\NameSpace' => 'Path\To\Resource' ]
     *
     * @param string $name Name Of Resource
     */
    protected function _t_loader_namespacestack_fixResourceList($name)
    {
        if (!is_array($this->_t_loader_namespacestack_Namespaces[$name]))
            $this->_t_loader_namespacestack_Namespaces[$name] = array(
                $this->_t_loader_namespacestack_Namespaces[$name]
            );
    }
}
