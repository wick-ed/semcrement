<?php

/**
 * \Wicked\Semcrement\Interfaces\CacheInterface
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

namespace Wicked\Semcrement\Interfaces;

use TokenReflection\IReflection;
use Wicked\Semcrement\Entities\Result;
use Wicked\Semcrement\Entities\Reason;

/**
 * Interface for reflection instance caches
 *
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */
interface CacheInterface
{

    /**
     * Load the reflection instance for the requested structure name
     *
     * @param string $structureName Fully qualified name of the structure
     *
     * @return \TokenReflection\IReflection
     *
     * @throws \Exception
     */
    public function load($structureName);

    /**
     * Will store any given structure reflection
     *
     * @param IReflection $structureReflection Reflection instance to store in our cache
     *
     * @return null
     */
    public function store(IReflection $structureReflection);
}
