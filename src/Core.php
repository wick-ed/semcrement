<?php

/**
 * \Wicked\Semcrement\Core
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
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
use Wicked\Semcrement\Interfaces\CacheInterface;
use Wicked\Semcrement\Interfaces\InspectorInterface;

require '../vendor/autoload.php';

/**
 * Core class which does everything right now
 *
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
     * @var unknown
     */
    protected $baseVersion;
    
    /**
     * The inspector to use
     * 
     * @var \Wicked\Semcrement\Interfaces\Inspector $inspector
     */
    protected $inspector;
    
    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->init();
    }
    
    protected function init()
    {
        $this->baseVersion = Parser::toBuilder('1.1.1');
        $this->inspector = new DefaultInspector();
        $this->cache = new Cache();
    }
    
    /**
     * 
     * @return \Wicked\Semcrement\unknown
     */
    public function getBaseVersion()
    {
        return $this->baseVersion;
    }
    

    /**
     *
     * @return \Wicked\Semcrement\Interfaces\CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }
    
    /**
     *
     * @param \Wicked\Semcrement\Interfaces\CacheInterface $cache The cache to be used
     */
    public function setInspector(CacheInterface $cache)
    {
        $this->cache = $cache;
    }
    
    /**
     * 
     * @return \Wicked\Semcrement\Interfaces\Inspector
     */
    public function getInspector()
    {
        return $this->inspector;
    }
    
    /**
     *
     * @param \Wicked\Semcrement\Interfaces\InspectorInterface $inspector The inspector to be used
     */
    public function setInspector(InspectorInterface $inspector)
    {
        $this->inspector = $inspector;
    }
    
    /**
     * 
     * @param type $srcPath
     * @throws \Exception
     */
    public function doStuff($srcPath)
    {
        
        // check if we got something we can work with
        if (!is_readable($srcPath) || !is_dir($srcPath)) {

            throw new \Exception(sprintf('Cannot read from source path %s', $srcPath));
        }

        $broker = new Broker(new Memory());
        $broker->processDirectory($srcPath);

        // iterate all php files and check for an API annotation
        $inspector = $this->getInspector();
        foreach ($broker->getClasses() as $classReflection) {

            // we can continue iteration if we did not get any valuable information from this file
            if (!$classReflection instanceof \TokenReflection\ReflectionClass) {

                continue;
            }

            // if we got the API annotation we have to work with it
            if ($classReflection->hasAnnotation(ApiAnnotation::ANNOTATION)) {

                // check for possible changes
                $inspector->inspect($classReflection);

                // save the current reflection object as a base for later comparison
                $this->cacheReflection($classReflection);
            }
        }
        
        // get the result and increment the version accordingly
        $result = $inspector->getResult();
        $incrementationMethod = 'increment' . ucfirst(strtolower($result->getIncrementVersion()));
        
        $version = $this->getBaseVersion();
        if (method_exists($version, $incrementationMethod)) {
            
            $version->$incrementationMethod();
        }
        
        return $version;
    }
}


$core = new Core();
$core->setInspector(new DefaultInspector());
echo Dumper::toString($core->doStuff('/home/xyz/workspace/webserver/src'));