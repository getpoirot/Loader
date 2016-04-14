<?php
namespace Poirot\Loader;

if (class_exists('Poirot\Loader\aLoader', false))
    return;

require_once __DIR__.'/Interfaces/iLoader.php';

use Poirot\Loader\Interfaces\iLoader;

abstract class aLoader
    implements iLoader
    # , ipConfigurable // removed in case of dependency reduction
{
    /**
     * Construct
     *
     * @param array|string $options
     */
    function __construct($options = null)
    {
        if ($options !== null)
            $this->with(static::withOf($options));
    }


    // Implement ipConfigurable:

    abstract function with(array $options, $throwException = false);

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
     * @param array|mixed $optionsRes
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
}
