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

namespace Wicked\Semcrement;

use TokenReflection\IReflection;
use TokenReflection\IReflectionClass;
use Wicked\Semcrement\Entities\Result;
use Wicked\Semcrement\Entities\Reason;
use Wicked\Semcrement\Interfaces\InspectorInterface;

/**
 * Wicked\Semcrement\Inspector
 *
 * Class used to do the inspection and comparison of APIs
 *
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 * 
 * @todo less parameters for method
 * @todo closed of visibility
 * @todo removed public method
 * @todo more restricted typehint
 * @todo major incrementation of exposed
 */
class DefaultInspector implements InspectorInterface
{
    
    /**
     * The collected result of several inspections
     * 
     * @var \Wicked\Semcrement\Entities\Result $result
     */
    protected $result;
    
    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->result = new Result();
    }
    
    /**
     *
     * @param IReflection $structureReflection
     *
     * TODO make this check different based on the structure we got
     */
    public function inspect(IReflection $structureReflection)
    {
        
        if ($formerReflection->getName() !== $structureReflection->getName()) {
    
            throw new \Exception(sprintf(
                'Mismatch of former and current structure information. %s !== %s',
                $formerReflection->getName(),
                $structureReflection->getName()
            ));
        }

        // determine which type of reflection we have on our hands
        if ($structureReflection instanceof IReflectionClass) {
            // we got a class on our hands, so use class inspection
            $result = $this->inspectClass($structureReflection);
            
        } else {
            // nothing found, return FALSE
            $result = false;
        }
        
        return $result;
    }
    
    /**
     *
     * @param unknown $reflectionClass
     * @param unknown $formerReflection
     */
    protected function didMethodExistBefore(IReflectionClass $reflectionClass, IReflectionClass $formerReflection)
    {
        if ($formerReflection->hasMethod($methodName)) {
            return true;
            
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @param unknown $reflectionClass
     * @param unknown $formerReflection
     */
    protected function inspectClass(IReflectionClass $reflectionClass, IReflectionClass $formerReflection)
    {
        
        // do the actual check
        foreach ($reflectionClass->getMethods() as $currentMethod) {
            $methodName = $currentMethod->getName();
        
            // check if the method did even exist before
            $formerMethod = null;
            if ($this->didMethodExistBefore($reflectionClass, $formerReflection)) {
                $formerMethod = $formerReflection->getMethod($methodName);
                
            }
        
            // only proceed for public methods (but check if it was public before)
            if ($currentMethod->isPrivate() || $currentMethod->isProtected()) {
        
                if ($formerMethod && !$formerMethod->isPrivate() && !$formerMethod->isProtected()) {
        
                    $this->result->addReason(new Reason($structureName, $methodName, 1), Result::MAJOR);
                }
                continue;
            }
        
            // if there was no former method this is a MINOR version bump
            if ($formerMethod === null) {
        
                $this->result->addReason(new Reason($structureName, $methodName, 2), Result::MINOR);
                continue;
            }
        
            // check if the method has been made public recently
            if ($formerMethod->isPrivate() || $formerMethod->isProtected()) {
        
                $this->result->addReason(new Reason($structureName, $methodName, 3), Result::MINOR);
                continue;
            }
        
            // are there less parameters now than before?
            $currentParameters = $currentMethod->getParameters();
            $formerParameters = $formerMethod->getParameters();
            if (count($formerParameters) > count($currentParameters)) {
        
                $this->result->addReason(new Reason($structureName, $methodName, 4), Result::MAJOR);
                continue;
        
            } else {
                // we have to check if the new parameters are optional or the parameters changed types
        
                for ($i = 0; $i < count($currentParameters); $i ++) {
        
                    // if both methods have the parameter we compare types, otherwise we check for optionality
                    if (isset($formerParameters[$i])) {
        
                        if ($formerParameters[$i]->getOriginalTypeHint() !== $currentParameters[$i]->getOriginalTypeHint()) {
        
                            $this->result->addReason(new Reason($structureName, $methodName, 5), Result::MAJOR);
                        }
        
                    } else {
        
                        if (!$currentParameters[$i]->isDefaultValueAvailable()) {
        
                            $this->result->addReason(new Reason($structureName, $methodName, 6), Result::MAJOR);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Getter for the $result property
     *
     * @return \Wicked\Semcrement\Result
     */
    public function getResult()
    {
        return $this->result;
    }
}
