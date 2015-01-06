<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Service
 * @package   Semcrement
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */

namespace Wicked\Semcrement;

use TokenReflection\Broker;
use TokenReflection\IReflection;
use TokenReflection\Broker\Backend\Memory;
use Herrera\Version\Dumper;
use Herrera\Version\Parser;

require '../vendor/autoload.php';

/**
 * Wicked\Semcrement\Core
 *
 * Core class which does everything right now
 *
 * @category  Service
 * @package   Semcrement
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */
class Core
{

    const CACHE_PATH = '/../var/tmp/cache/';
    
    /**
     * 
     * @param type $srcPath
     * @throws \Exception
     */
    public function doStuff($srcPath)
    {

        // get a dummy version
        $version = Parser::toBuilder('1.1.1');
        
        // check if we got something we can work with
        if (!is_readable($srcPath) || !is_dir($srcPath)) {

            throw new \Exception(sprintf('Cannot read from source path %s', $srcPath));
        }

        $broker = new Broker(new Memory());
        $broker->processDirectory($srcPath);

        // iterate all php files and check for an API annotation
        $inspector = new Inspector();
        foreach ($broker->getClasses() as $classReflection) {

            // we can continue iteration if we did not get any valuable information from this file
            if (!$classReflection instanceof \TokenReflection\ReflectionClass) {
                
                continue;
            }
            
            // if we got the API annotation we have to work with it
            if ($classReflection->hasAnnotation(ApiAnnotation::ANNOTATION)) {

                // check for possible changes
                $inspector->checkForChanges($classReflection);

                // save the current reflection object as a base for later comparison
                $this->cacheReflection($classReflection);
            }
        }
        
        // get the result and increment the version accordingly
        $result = $inspector->getResult();
        $incrementationMethod = 'increment' . ucfirst(strtolower($result->getIncrementVersion()));
        
        if (method_exists($version, $incrementationMethod)) {
            
            $version->$incrementationMethod();
        }
        
        echo Dumper::toString($version);
    }
    
    /**
     * 
     * @param IReflection $structureReflection
     */
    protected function cacheReflection(IReflection $structureReflection)
    {
        // calculate the cache file's path
        $filePath = $this->calculateCachePath($structureReflection->getName());
        file_put_contents($filePath, serialize($structureReflection));
    }
    
    /**
     * 
     * @param string $structureName
     * @return type
     */
    protected function calculateCachePath($structureName)
    {
        return __DIR__ . self::CACHE_PATH . str_replace('\\', '_', $structureName);
    }
    
    
}

