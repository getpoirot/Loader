<?php
namespace Poirot\Loader;

trait ResourceMapTrait
{
    /**
     * @var array Registered Resource Maps
     */
    protected $__mapResources = [];

    /**
     * Set Map Resource
     *
     * @param array|string $map
     *
     * @return $this
     */
    function setMapResource($map)
    {
        (is_string($map))  ? $this->setMapFile($map)
        : (!is_array($map) ?: $this->setMapArray($map));

        return $this;
    }

    /**
     * Set Class Path Pair Map
     *
     * @param array $maps Associative array of class=>path
     *
     * @return $this
     */
    function setMapArray(array $maps)
    {
        # previous registered keys not replaced
        $this->__mapResources = \Poirot\Core\array_merge($maps, $this->__mapResources);

        return $this;
    }

    /**
     * Set From Class Map File
     *
     * @param string $file File Returning Map Array
     *
     * @throws \Exception
     * @return $this
     */
    function setMapFile($file)
    {
        if (!file_exists($file))
            throw new \InvalidArgumentException(sprintf(
                'Map file "%s" provided does not exist.',
                $file
            ));

        $maps = include_once $file;
        if (!is_array($maps))
            throw new \Exception(sprintf(
                'Map file "%s" must return array of "class=>path" pairs.',
                $file
            ));

        $this->setMapArray($maps);

        return $this;
    }

    /**
     * Resolve To Resource By Map
     *
     * @param string $resource
     *
     * @return VOID|false|mixed
     */
    function resolve($resource)
    {
        if (!isset($this->__mapResources[$resource]))
            return (defined('VOID')) ? VOID : false;

        return $this->__mapResources[$resource];
    }
}
