<?php
namespace Poirot\Loader\Traits;

trait tLoaderMapResource
{
    /**
     * @var array Registered Resource Maps
     */
    protected $_t_loader_map_resource_MapRes = [];

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
        $this->_t_loader_map_resource_MapRes = array_merge($options, $this->_t_loader_map_resource_MapRes);
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
}
