<?php
## ===================================================
## | This fix is Code Clone of LoaderAggregate
## | it will resolve when php not support Traits
## | @see LoaderAggregate

namespace Poirot\Loader;

use SplPriorityQueue;

use Poirot\Loader\Interfaces\iLoader;

class LoaderAggregate
    extends aLoader
{
    ## just determine that fixed class loaded in debugs
    protected $IS_FIX;

    // use tLoaderAggregate;

    ## @see tLoaderAggregate;
    ## Code Clone <begin> =================================================================
    /**
     * @var SplPriorityQueue
     */
    protected $_t_loader_aggregate_Queue;

    protected $_t_loader_aggregate_Names = array(
        # Used to get loader instances
        ## 'LoaderName_Or_ClassName' => iLoader
    );


    /**
     * Resolve To Resource
     *
     * @param mixed $resource
     *
     * @return mixed
     */
    function resolve($resource)
    {
        $resolve = false;
        /** @var iLoader $loader */
        foreach(clone $this->_t_loader_aggregate_getQueue() as $loader) {
            $resolve = call_user_func_array([$loader, 'resolve'], func_get_args());
            if ($resolve)
                break;
        }

        return $resolve;
    }

    /**
     * Attach (insert) Loader
     *
     * @param iLoader $loader
     * @param int     $priority
     *
     * @return $this
     */
    function attach(iLoader $loader, $priority = 0)
    {
        $this->_t_loader_aggregate_getQueue()->insert($loader, $priority);

        $loaderClass = get_class($loader);
        $this->_t_loader_aggregate_Names[$loaderClass] = $loader;

        return $this;
    }

    /**
     * Get Loader By Name
     *
     * @param string $name Loader Name, default is class name
     *
     * @throws \Exception Loader class not found
     * @return iLoader
     */
    function by($name)
    {
        if (!$this->hasAttached($name))
            throw new \Exception(sprintf(
                'Loader with name (%s) has not attached.'
                , $name
            ));

        return $this->_t_loader_aggregate_Names[$name];
    }

    /**
     * Has Loader With This Name Attached?
     *
     * @param string $name Loader Name, default is class name
     *
     * @return bool
     */
    function hasAttached($name)
    {
        return in_array($name, $this->listAttached());
    }

    /**
     * Get Attached loader List
     *
     * @return array Associate Array Of Name
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
    ## Code Clone <end> ===================================================================


    /**
     * Build Object With Provided Options
     * > Setup Aggregate Loader
     *   Options:
     *  [
     *    'attach' => [0 => iLoader, $priority => iLoader],
     *    'Registered\ClassLoader' => [
    // Options
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
                $attach = [$attach];

            foreach($attach as $pr => $loader)
                $this->attach($loader, $pr);

            unset($options['attach']);
        }

        # Set Loader Specific Config:
        foreach($options as $loader => $loaderOptions) {

            try{
                $loader = $this->by($loader);
            } catch (\Exception $e) {
                if ($throwException)
                    throw new \InvalidArgumentException(sprintf(
                        'Loader (%s) not attached.'
                        , $loader
                    ));
            }

            if (method_exists($loader, 'with'))
                /** @var \Poirot\Std\Interfaces\Pact\ipConfigurable $loader */
                $loader->with($loaderOptions);
        }

        return $this;
    }
}
