<?php
namespace Poirot\Autoloader;

if (class_exists('Poirot\\Autoloader\\ClassMapAutoloader'))
    return;

require_once __DIR__.'/AbstractAutoloader.php';

class ClassMapAutoloader extends AbstractAutoloader
{
    /**
     * @var array Registered Class Maps
     */
    protected $__classMap = [];

    /**
     * Construct
     *
     * @param array|string $options
     */
    function __construct($options = null)
    {
        if ($options !== null)
            (is_string($options)) ? $this->setFromFile($options)
                : (!is_array($options) ?: $this->setClassMap($options));
    }

    /**
     * Set Class Path Pair Map
     *
     * @param array $maps Associative array of class=>path
     *
     * @return $this
     */
    function setClassMap(array $maps)
    {
        # previous registered keys not replaced
        $this->__classMap = array_merge($maps, $this->__classMap);

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
    function setFromFile($file)
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

        $this->setClassMap($maps);

        return $this;
    }

    /**
     * Autoload Class Callable
     *
     * - must not throw exception
     *
     * @param string $class Class Name
     *
     * @return void
     */
    function attainClass($class)
    {
        (!isset($this->__classMap[$class])) ?: require_once $this->__classMap[$class];
    }
}
