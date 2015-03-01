<?php

/**
 * \Wicked\Semcrement\Interfaces\InspectorInterface
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

namespace Wicked\Semcrement\Interfaces;

use TokenReflection\IReflection;

/**
 * Interface common to all inspectors
 *
 * @author Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link https://github.com/wick-ed/semcrement
 */
interface InspectorInterface
{

    /**
     * Getter for the $result property
     *
     * @return \Wicked\Semcrement\Result
     */
    public function getResult();

    /**
     * Will start the inspection for a specific structure
     *
     * @param \TokenReflection\IReflection $structureReflection The current reflection to inspect
     * @param \TokenReflection\IReflection $formerReflection    The former reflection to compare to
     *
     * @return null
     *
     * @throws \Exception Will throw exception on errors
     */
    public function inspect(IReflection $structureReflection, IReflection $formerReflection);
}
