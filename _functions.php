<?php
namespace Poirot\Loader;

    if (defined(__FILE__.'_included'))
        ## file is included once!!
        return;
    else
        define(__FILE__.'_included', true);
    
    
    const SEPARATOR_NAMESPACES = '\\';

    /**
     * Watch File Exists Within Given Resource
     * 
     * @param string $name     requested name to resolve
     * @param string $resource may find within this resource of match
     * @param string $match    match with this namespace
     * @param string $postfix  append to resource match usually file extension
     * 
     * @return string resolved path to file if exists
     */
    function funcWatchFileExists($name, $resource, $match, $postfix = null)
    {
        if (is_file($match))
            $pathToFile = $match;

        else {
            ## $match        = 'Poirot\Loader'
            ## $name         = 'Poirot\Loader\ClassMapAutoloader'
            ## $maskOffClass = '\ClassMapAutoloader'
            $maskOffClass = ($match == '*' || $match == '**')
                ? $name
                : substr($name, strlen($match), strlen($name));

            ## we suppose class mask must find within match
            ## so convert namespaces to directory slashes
            $pathToFile =
                _normalizeDir($resource)
                . _normalizeResourceName($maskOffClass);
            
            if ($postfix !== null) $pathToFile.=$postfix;
            if (! file_exists($pathToFile) )
                return false;
        }

        return $pathToFile;
    }

    /**
     * Normalize Directory Path
     *
     * @param string $dir
     *
     * @return string
     */
    function _normalizeDir($dir)
    {
        static $_c_Normalized;
        
        if (isset($_c_Normalized[$dir]))
            return $_c_Normalized[$dir];
    
        $dir = rtrim(strtr($dir, SEPARATOR_NAMESPACES, '/'), '/');
        $_c_Normalized[$dir] = $dir;
        return $dir;
    }

    /**
     * Convert Class Namespace Trailing To Path
     *
     * @param string $maskOffClass
     *
     * @return string
     */
    function _normalizeResourceName($maskOffClass)
    {
        $maskOffClass = ltrim($maskOffClass, SEPARATOR_NAMESPACES);
        return ($maskOffClass !== '') ? '/'. _normalizeDir($maskOffClass) : '';
    }
    