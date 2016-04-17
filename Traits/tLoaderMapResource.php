<?php
namespace Poirot\Loader\Traits;

trait tLoaderMapResource
{
    ## @see fixes/LoaderMapResource
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
}
