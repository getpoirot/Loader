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
     * @param array $resourceMap
     *
     * @return $this
     */
    function setResources(array $resourceMap)
    {
        # previous registered keys not replaced
        $this->_t_loader_map_resource_MapRes = array_merge($resourceMap, $this->_t_loader_map_resource_MapRes);
        return $this;
    }

    /**
     * Add Namespace Resource/Directory Pair
     *
     * @param string $namespace
     * @param string $resource Directory Path Or Any Resource Watched
     *
     * @throws \Exception
     * @return $this
     */
    function addResource($namespace, $resource)
    {
        $namespace = (string) $namespace;

        if (array_key_exists($namespace, $this->_t_loader_map_resource_MapRes))
            throw new \Exception(sprintf(
                'Resource Map (%s) already exists in map.'
                , $namespace
            ));

        $this->_t_loader_map_resource_MapRes[$namespace] = $resource;
        return $this;
    }

    /**
     * Resolve To Resource By Map
     *
     * @param string $resource
     *
     * @return false|mixed
     */
    function resolve($resource)
    {
        $resource = (string) $resource;
        if (!array_key_exists($resource, $this->_t_loader_map_resource_MapRes))
            return false;

        return $this->_t_loader_map_resource_MapRes[$resource];
    }
    ## Code Clone <end> ===================================================================
}
