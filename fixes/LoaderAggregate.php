<?php
## ===================================================
## | DO_LEAST_PHPVER_SUPPORT
## | 
## | This fix is Code Clone of LoaderAggregate
## | it will resolve when php not support Traits
## | @see LoaderAggregate

namespace Poirot\Loader;

use SplPriorityQueue;

!class_exists('Poirot/Loader/aLoader', false)
    and require_once __DIR__.'/../aLoader.php';

use Poirot\Loader\Interfaces\iLoader;

class LoaderAggregate
    extends aLoader
{
    ## just determine that fixed class loaded in debugs
    protected $IS_FIX = true;

    // use tLoaderAggregate;

    ## @see tLoaderAggregate;
    ## Code Clone <begin> =================================================================
    /** @var SplPriorityQueue */
    protected $_t_loader_aggregate_Queue;

    protected $_t_loader_aggregate_Names = array(
        # Used to get loader instances
        ## 'LoaderName_Or_ClassName' => iLoader
    );
    protected $_c__normalized = array();


    /**
     * Resolve To Resource
     *
     * @param string $name
     *
     * @return mixed
     */
    function resolve($name)
    {
        $resolve = false;
        /** @var iLoader $loader */
        foreach(clone $this->_t_loader_aggregate_getQueue() as $loader) {
            $resolve = call_user_func_array(array($loader, 'resolve'), func_get_args());
            if ($resolve)
                break;
        }

        return $resolve;
    }

    /**
     * Attach (insert) Loader
     *
     * - it will store loader can retrieved by ClassName
     *
     * @param iLoader     $loader
     * @param int         $priority
     *
     * @return $this
     */
    function attach(iLoader $loader, $priority = 0)
    {
        $this->_t_loader_aggregate_getQueue()->insert($loader, $priority);

        $loaderClass = get_class($loader);
        $loaderClass = $this->_normalizeLoaderName($loaderClass);
        $this->_t_loader_aggregate_Names[$loaderClass] = $loader;

        return $this;
    }

    /**
     * Get Loader By Name
     *
     * [code:]
     *  $aggregateLoader->loader(\Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class)
     *     ->with([..options])
     * [code]
     *
     * @param string $loaderName Loader Name, default is class name
     *
     * @throws \Exception Loader class not found
     * @return iLoader
     */
    function loader($loaderName)
    {
        $loaderName = $this->_normalizeLoaderName($loaderName);

        if (!$this->hasAttached($loaderName))
            throw new \Exception(sprintf(
                'Loader with name (%s) has not attached.'
                , $loaderName
            ));

        return $this->_t_loader_aggregate_Names[$loaderName];
    }

    /**
     * Has Loader With This Name Attached?
     *
     * [code:]
     *  $aggregateLoader->hasAttached(\Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class)
     * [code]
     *
     * @param string $loaderName Loader Name, default is class name
     *
     * @return bool
     */
    function hasAttached($loaderName)
    {
        $loaderName = $this->_normalizeLoaderName($loaderName);
        return in_array($loaderName, $this->listAttached());
    }

    /**
     * Get Attached loader List
     *
     * @return array Array Of Names
     */
    function listAttached()
    {
        return array_keys($this->_t_loader_aggregate_Names);
    }


    // ...

    protected function _t_loader_aggregate_getQueue()
    {
        if (!$this->_t_loader_aggregate_Queue)
            ## standard spl queue to avoid using extra libraries
            $this->_t_loader_aggregate_Queue = new SplPriorityQueue();

        return $this->_t_loader_aggregate_Queue;
    }

    protected function _normalizeLoaderName($loaderName)
    {
        $loaderName = (string) $loaderName;
        if (isset($this->_c__normalized[$loaderName]))
            return $this->_c__normalized[$loaderName];

        $normalized = ltrim($loaderName, '\\');
        return $this->_c__normalized[$loaderName] = $normalized;
    }
    ## Code Clone <end> ===================================================================


    ## @see ../LoaderAggregate;
    ## Code Clone <begin> =================================================================
    /**
     * Build Object With Provided Options
     * > Setup Aggregate Loader
     *   Options:
     *  [
     *    'attach' => [0 => iLoader, $priority => iLoader, ['loader' => iLoader, 'priority' => $pr] ],
     *    'Registered\ClassLoader' => [
     *       // Options
     *       'Poirot\AaResponder'  => [APP_DIR_VENDOR.'/poirot/action-responder/Poirot/AaResponder'],
     *       'Poirot\Application'  => [APP_DIR_VENDOR.'/poirot/application/Poirot/Application'],
     *    ]
     *  ]
     *
     * @param array $options       Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @throws \Exception
     * @return $this
     */
    function with(array $options, $throwException = false)
    {
        # Attach Loader:
        if (isset($options['attach'])) {
            $attach = $options['attach'];
            if(!is_array($attach))
                $attach = array($attach);

            foreach($attach as $pr => $loader) {
                if (is_array($loader)) {
                    if (!isset($loader['priority']) || !isset($loader['loader']))
                        throw new \InvalidArgumentException(sprintf(
                            'Invalid Option Provided (%s).'
                            , var_export($loader, true)
                        ));

                    $pr     = $loader['priority'];
                    $loader = $loader['loader'];
                }

                $this->attach($loader, $pr);
            }

            unset($options['attach']);
        }

        # Set Loader Specific Config:
        foreach($options as $loader => $loaderOptions)
        {
            try{
                $loader = $this->loader($loader);
            } catch (\Exception $e) {
                if ($throwException)
                    throw new \InvalidArgumentException(sprintf(
                        'Loader (%s) not attached.'
                        , $loader
                    ));
            }

            if (method_exists($loader, 'with')) {
                /** @var \Poirot\Std\Interfaces\Pact\ipConfigurable $loader */
                $loader->with($loader::withOf($loaderOptions));
            }
        }

        return $this;
    }
    ## Code Clone <end> ===================================================================
}
