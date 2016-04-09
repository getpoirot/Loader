<?php
namespace Poirot\Loader;

use Poirot\Loader\Interfaces\iLoader;
use Poirot\Loader\Traits\tLoaderAggregate;
use Poirot\Std\Interfaces\Pact\ipConfigurable;

class LoaderAggregate
    implements iLoader
    , ipConfigurable
{
    use tLoaderAggregate;

    /**
     * Construct
     *
     * @param array $options
     */
    function __construct(array $options = null)
    {
        if ($options !== null)
            $this->with($options);
    }
}
