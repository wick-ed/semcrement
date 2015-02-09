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
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */

namespace Wicked\Semcrement\Entities;

/**
 * Wicked\Semcrement\Entities\Reason
 *
 * todo
 *
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */
class Reason
{
    /**
     * Various constants to identify different reasons for version incremetation
     *
     * @var string
     */
    const METHOD_REMOVED = 'METHOD_REMOVED';
    const VISIBILITY_RESTRICTED = 'VISIBILITY_RESTRICTED';
    const VISIBILITY_OPENED = 'VISIBILITY_OPENED';
    const PARAMETER_REMOVED = 'PARAMETER_REMOVED';
    const PARAMETER_ADDED = 'PARAMETER_ADDED';
    const PARAMETER_RETYPED = 'PARAMETER_RETYPED';
    const METHOD_ADDED = 'METHOD_ADDED';
    
    /**
     * 
     * @var unknown
     */
    protected $message;
    
    /**
     *
     * @var unknown
     */
    protected $methodName;
    
    /**
     *
     * @var unknown
     */
    protected $reasonIdentifier;
    
    /**
     * 
     * @var unknown
     */
    protected $structureName; 
    
    /**
     * Default constructor
     * 
     * @param string $structureName    Name of the structure the reason was found in
     * @param string $methodName       Name of the method the reason was found in
     * @param string $reasonIdentifier Unique identifier for the reason
     */
    public function __construct($structureName, $methodName, $reasonIdentifier)
    {
        $this->structureName = $structureName;
        $this->methodName = $methodName;
        $this->reasonIdentifier = $reasonIdentifier;
    }
    
}
