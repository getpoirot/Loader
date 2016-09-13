<?php
## ===================================================
## | DO_LEAST_PHPVER_SUPPORT
## | 
## | This fix is Code Clone of LoaderMapResource
## | it will resolve when php not support Traits
## | @see LoaderMapResource

namespace Poirot\Loader;

!class_exists('Poirot/Loader/aLoader', false)
    and require_once __DIR__.'/../aLoader.php';

class LoaderMapResource
    extends aLoader
{
    ## just determine that fixed class loaded in debugs
    protected $IS_FIX = true;

    // use tLoaderMapResource;

    ## @see tLoaderMapResource
    ## Code Clone <begin> =================================================================
    /**
     * @var array Registered Resource Maps
     */
    protected $_t_loader_map_resource_MapRes = array();

    /**
     * Set Bunch Of Name/Resource Pair
     *
     * @param array $mapResources
     *
     * @return $this
     */
    function setResources(array $mapResources)
    {
        # previous registered keys not replaced
        $this->_t_loader_map_resource_MapRes = array_merge($mapResources, $this->_t_loader_map_resource_MapRes);
        return $this;
    }

    /**
     * Add Namespace Resource/Directory Pair
     *
     * @param string $name
     * @param string $resource Directory Path Or Any Resource Watched
     *
     * @throws \Exception
     * @return $this
     */
    function addResource($name, $resource)
    {
        $name = (string) $name;

        if (array_key_exists($name, $this->_t_loader_map_resource_MapRes))
            throw new \Exception(sprintf(
                'Resource Map (%s) already exists in map.'
                , $name
            ));

        $this->_t_loader_map_resource_MapRes[$name] = $resource;
        return $this;
    }

    /**
     * Resolve To Resource By Map
     *
     * @param string $name
     *
     * @return false|mixed
     */
    function resolve($name)
    {
        $name = (string) $name;
        if (!array_key_exists($name, $this->_t_loader_map_resource_MapRes))
            return false;

        return $this->_t_loader_map_resource_MapRes[$name];
    }
    ## Code Clone <end> ===================================================================

    ## @see ../LoaderMapResource
    ## Code Clone <begin> =================================================================
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
        $this->setResources($options);
        return $this;
    }
    ## Code Clone <end> ===================================================================
}
