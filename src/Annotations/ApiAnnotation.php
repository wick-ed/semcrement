<?php

/**
 * \Wicked\Semcrement\Annotations\ApiAnnotation
 *
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
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */

namespace Wicked\Semcrement\Annotations;

/**
 * Core class which does everything right now
 *
 * @category Service
 * @package Semcrement
 * @author Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link https://github.com/wick-ed/semcrement
 */
class ApiAnnotation
{

    /**
     * Annotation by which a part of the public API can be identified
     *
     * @var string ANNOTATION
     */
    const ANNOTATION = 'PublicApi';

    /**
     * Will return the full name of this annotation class
     *
     * @return string
     */
    public function __getClass()
    {
        return __CLASS__;
    }
}
