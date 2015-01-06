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

namespace Wicked\Semcrement\Entities;

/**
 * Wicked\Semcrement\Entities\Reason
 *
 * TODO
 *
 * @category  Service
 * @package   Semcrement
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */
class Reason
{
    protected $structureName; 
    
    protected $methodName; 
    
    protected $reasonIdentifier;
    
    /**
     * 
     * @param unknown $structureName
     * @param unknown $methodName
     * @param unknown $reasonIdentifier
     */
    public function __construct($structureName, $methodName, $reasonIdentifier)
    {
        $this->structureName = $structureName;
        $this->methodName = $methodName;
        $this->reasonIdentifier = $reasonIdentifier;
    }
    
}
