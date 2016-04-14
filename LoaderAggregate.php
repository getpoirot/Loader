<?php
namespace Poirot\Loader;

require_once __DIR__.'/aLoader.php';

if (version_compare(phpversion(), '5.4.0') < 0) {
    ## php version not support traits
    require_once __DIR__.'/fixes/LoaderAggregate.php';
    return;
}


require_once __DIR__.'/Traits/tLoaderAggregate.php';
use Poirot\Loader\Traits\tLoaderAggregate;

class LoaderAggregate
    extends aLoader
{
    use tLoaderAggregate;


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
