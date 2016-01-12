<?php
namespace Poirot\Loader;

trait ResourceMapTrait
{
    /**
     * @var array Registered Resource Maps
     */
    protected $__mapResources = [];

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
        if (!array_key_exists($resource, $this->__mapResources))
            return false;

        return $this->__mapResources[$resource];
    }


    /**
     * Set Map Resource
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
     * Set Class Path Pair Map
     *
     * @param array $maps Associative array of class=>path
     *
     * @return $this
     */
    function fromArray(array $maps)
    {
        # previous registered keys not replaced
        $this->__mapResources = array_merge($maps, $this->__mapResources);
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
    function fromFile($file)
    {
        if (!file_exists($file))
            throw new \InvalidArgumentException(sprintf(
                'Map file "%s" provided does not exist.',
                $file
            ));

        $maps = include_once $file;
        $this->fromArray($maps);

        return $this;
    }
}
