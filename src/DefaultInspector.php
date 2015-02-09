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
use TokenReflection\IReflectionMethod;

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
    public function inspect(IReflection $structureReflection, IReflection $formerReflection)
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
            $result = $this->inspectClass($structureReflection, $formerReflection);
            
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
    protected function didMethodExistBefore(IReflectionClass $formerReflection, $methodName)
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
    protected function didRemoveMethod(IReflection $structureReflection, IReflectionClass $formerReflection)
    {
        // get the public methods of both current and former definition
        $currentMethods = $structureReflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $formerMethods = $formerReflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        // if there aren't less methods now we don't have to do anything
        if (count($currentMethods) >= count($formerMethods)) {
            return false;
        }

        // as we reached this point there seems to be missing methods.
        // check which one is missing
        foreach ($formerMethods as $formerMethod) {
            // iterate all the current methods and check for a match
            $matchFound = false;
            foreach ($currentMethods as $currentMethod) {
                if ($formerMethod->getName() === $currentMethod->getName()) {
                    // we have a match, so no problem here
                    $matchFound = true;
                    continue;
                }
            }
            
            // did we find a match?
            if ($matchFound === false) {
                // report this public method as missing
                $this->result->addReason(new Reason($structureReflection->getName(), $formerMethod->getName(), Reason::METHOD_REMOVED), Result::MAJOR);
            }
        }
        
        // tell them at least one method is missing now
        return true;
    }
    
    /**
     *
     * @param unknown $reflectionClass
     * @param unknown $formerReflection
     */
    protected function didRestrictVisibility(IReflection $structureReflection, IReflectionMethod $currentMethod, IReflectionMethod $formerMethod)
    {
        if ($formerMethod && !$formerMethod->isPrivate() && !$formerMethod->isProtected()) {
            // the method has been public but isn't anymore
            $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::VISIBILITY_RESTRICTED), Result::MAJOR);
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
    protected function didOpenVisibility(IReflection $structureReflection, IReflectionMethod $currentMethod, IReflectionMethod $formerMethod)
    {
        if ($formerMethod && !$formerMethod->isPrivate() && !$formerMethod->isProtected()) {
            // the method has been public but isn't anymore
            $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::VISIBILITY_OPENED), Result::MAJOR);
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
    protected function didRemoveParameter(IReflection $structureReflection, IReflectionMethod $currentMethod, IReflectionMethod $formerMethod)
    {
        if (count($formerMethod->getParameters()) > count($currentMethod->getParameters())) {
            // there are less parameters now than before
            $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::PARAMETER_REMOVED), Result::MAJOR);
            return true;
    
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @param IReflection $structureReflection
     * @param IReflectionMethod $currentMethod
     * @param IReflectionMethod $formerMethod
     */
    protected function didParametersChangeType(IReflection $structureReflection, IReflectionMethod $currentMethod, IReflectionMethod $formerMethod)
    {
        $formerParameters = $formerMethod->getParameters();
        $currentParameters = $currentMethod->getParameters();
        
        for ($i = 0; $i < count($currentParameters); $i ++) {
        
            // if both methods have the parameter we compare types, otherwise we check for optionality
            if (isset($formerParameters[$i])) {
                // the parameter has been here before, but are the types consisten?
                if ($formerParameters[$i]->getOriginalTypeHint() !== $currentParameters[$i]->getOriginalTypeHint()) {
                    $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::PARAMETER_RETYPED), Result::MAJOR);
                }
        
            } else {
                // the parameter has not been here before, is it optional?
                if (!$currentParameters[$i]->isDefaultValueAvailable()) {
                    $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::PARAMETER_ADDED), Result::MAJOR);
                }
            }
        }
    }
    
    /**
     * 
     * @param unknown $reflectionClass
     * @param unknown $formerReflection
     */
    protected function inspectClass(IReflectionClass $reflectionClass, IReflectionClass $formerReflection)
    {

        // does the current class have less public methods
        $this->didRemoveMethod($reflectionClass, $formerReflection);
        
        // iterate all structure methods and check them
        foreach ($reflectionClass->getMethods() as $currentMethod) {
            // get the structure- and method name for faster access
            $methodName = $currentMethod->getName();
            $structureName = $reflectionClass->getName();

            // check if the method did even exist before
            $formerMethod = null;
            if ($this->didMethodExistBefore($formerReflection)) {
                $formerMethod = $formerReflection->getMethod($methodName);

            } else {
                // if there was no former method this is a MINOR version bump
                $this->result->addReason(new Reason($structureName, $methodName, Reason::METHOD_ADDED), Result::MINOR);
                continue;
            }

            // only proceed for public methods (but check if it was public before)
            if ($currentMethod->isPrivate() || $currentMethod->isProtected()) {
                $this->didRestrictVisibility($reflectionClass, $currentMethod, $formerMethod);
                continue;
            }

            // check if the method has been made public recently
            if ($this->didOpenVisibility($reflectionClass, $currentMethod, $formerMethod)) {
                continue;
            }

            // are there less parameters now than before?
            if ($this->didRemoveParameter($reflectionClass, $currentMethod, $formerMethod)) {
                continue;

            } else {
                // we have to check if the new parameters are optional or the parameters changed types
                $this->didParametersChangeType($reflectionClass, $currentMethod, $formerMethod);
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
