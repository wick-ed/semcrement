<?php

/**
 * \Wicked\Semcrement\Cache
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

namespace Wicked\Semcrement;

use Wicked\Semcrement\Interfaces\CacheInterface;
use TokenReflection\IReflection;

/**
 * Cache class to store and load former token refelction files
 *
 * @author Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link https://github.com/wick-ed/semcrement
 */
class Cache implements CacheInterface
{

    /**
     * Relative path to our cache directory
     *
     * @var string CACHE_PATH
     */
    const CACHE_PATH = '/../var/tmp/cache/';

    /**
     * Will calculate the absolute path to any stored structure
     *
     * @param string $structureName Fully qualified name of the structure
     *
     * @return string
     */
    protected function calculateCachePath($structureName)
    {
        return __DIR__ . self::CACHE_PATH . str_replace('\\', '_', $structureName);
    }

    /**
     * Load the reflection instance for the requested structure name
     *
     * @param string $structureName Fully qualified name of the structure
     *
     * @return \TokenReflection\IReflection
     *
     * @throws \Exception
     */
    public function load($structureName)
    {
        // to check for changes we have to have information former interfaces
        $cachePath = $this->calculateCachePath($structureName);
        if (! is_readable($cachePath)) {
            throw new \Exception(sprintf('Cannot load former definition of %s', $structureName));
        }

        // get the former reflection and check if we got the correct one
        return unserialize(file_get_contents($cachePath));
    }

    /**
     * Will store any given structure reflection
     *
     * @param IReflection $structureReflection Reflection instance to store in our cache
     *
     * @return null
     */
    public function store(IReflection $structureReflection)
    {
        $filePath = $this->calculateCachePath($structureReflection->getName());
        file_put_contents($filePath, serialize($structureReflection));
    }
}
