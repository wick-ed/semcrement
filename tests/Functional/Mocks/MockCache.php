<?php

/**
 * \Wicked\Semcrement\Tests\Functional\Mocks\MockCache
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
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */

namespace Wicked\Semcrement\Functional\Mocks;

use Wicked\Semcrement\Cache;
use TokenReflection\Broker;
use TokenReflection\Broker\Backend\Memory;
use TokenReflection\IReflection;

/**
 * Mocked cache class which allows loading of classes directly from the TokenReflection broker and omits storing.
 * Used for testing purposes
 *
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */
class MockCache extends Cache
{

    /**
     * The relative path to our cache directory
     * 
     * @var string CACHE_PATH
     */
    const CACHE_PATH = '/../../_files/former';
    
    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->broker = new Broker(new Memory());
        $this->broker->processDirectory(realpath(__DIR__ . self::CACHE_PATH));
    }
    
    /**
     * Will load "cached" files from our defined test files folder "former"
     * 
     * @param string $structureName Name of the structure to load
     * 
     * @return \TokenReflection\IReflection
     * 
     * @throws \Exception
     */
    public function load($structureName)
    {
        $classes = $this->broker->getClasses();
        
        if (isset($classes[$structureName])) {
            // if we will find the structure we will return it
            return $classes[$structureName];
            
        } else {
            // throw an exception if we find nothing
            throw new \Exception(\printf('Cannot find the structure %s.', $structureName));
        }
    }
    
    /**
     * Do nothing, this is just for tests
     *
     * @param IReflection $structureReflection
     */
    public function store(IReflection $structureReflection)
    {
    }
}
