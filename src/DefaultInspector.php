<?php

/**
 * \Wicked\Semcrement\DefaultInspector
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

use TokenReflection\IReflection;
use TokenReflection\IReflectionClass;
use Wicked\Semcrement\Entities\Result;
use Wicked\Semcrement\Entities\Reason;
use Wicked\Semcrement\Interfaces\InspectorInterface;
use TokenReflection\IReflectionMethod;
use Wicked\Semcrement\Interfaces\MapperInterface;
use Wicked\Semcrement\Mappers\ClassMapper;
use Wicked\Semcrement\Mappers\InterfaceMapper;

/**
 * Class used to do the inspection and comparison of APIs
 *
 * @author Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link https://github.com/wick-ed/semcrement
 *
 * @todo major incrementation of exposed
 */
class DefaultInspector implements InspectorInterface
{

    /**
     * The mapper used for our reason instances
     *
     * @var \Wicked\Semcrement\Interfaces\MapperInterface $mapper
     */
    protected $mapper;

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
        $this->mapper = new ClassMapper();
        $this->result = new Result();
    }

    /**
     * Will start the inspection for a specific structure
     *
     * @param \TokenReflection\IReflection $structureReflection The current reflection to inspect
     * @param \TokenReflection\IReflection $formerReflection    The former reflection to compare to
     *
     * @return null
     *
     * @throws \Exception Will throw exception on errors
     *
     * TODO make this check different based on the structure we got
     */
    public function inspect(IReflection $structureReflection, IReflection $formerReflection)
    {
        if ($formerReflection->getName() !== $structureReflection->getName()) {
            // we seem to have been passed a mismatching structure pair, fail here
            throw new \Exception(sprintf('Mismatch of former and current structure information. %s !== %s', $formerReflection->getName(), $structureReflection->getName()));
        }

        // determine which type of reflection we have on our hands
        if ($structureReflection instanceof IReflectionClass && !$structureReflection->isInterface()) {
            // we got a class on our hands, so use class inspection
            $result = $this->inspectClass($structureReflection, $formerReflection);

        } elseif ($structureReflection instanceof IReflectionClass && $structureReflection->isInterface()) {
            // we got an interface, inspect it
            $result = $this->inspectInterface($structureReflection, $formerReflection);

        } else {
            // no inspection method found, fail here
            throw new \Exceptions(sprintf('Could not find inspection method for unknown type %s', \get_class($structureReflection)));
        }

        return $result;
    }

    /**
     * Will test if a method did exist in the former definition of a structure
     *
     * @param \TokenReflection\IReflection $formerReflection The former reflection to check for the method
     * @param string                       $methodName       Name of the method to check for
     *
     * @return boolean
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
     * Will test if a method did get removed from a structure
     *
     * @param \TokenReflection\IReflection $structureReflection The current reflection to inspect
     * @param \TokenReflection\IReflection $formerReflection    The former inspection to compare to
     *
     * @return boolean
     */
    protected function didRemoveMethod(IReflection $structureReflection, IReflection $formerReflection)
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
                $this->result->addReason(new Reason($structureReflection, $formerMethod, Reason::METHOD_REMOVED, $this->mapper));
            }
        }

        // tell them at least one method is missing now
        return true;
    }

    /**
     * Will test if a method has been restricted in its visibility
     *
     * @param \TokenReflection\IReflection       $structureReflection The current structure reflection to inspect
     * @param \TokenReflection\IReflectionMethod $currentMethod       The current method reflection to inspect
     * @param \TokenReflection\IReflectionMethod $formerMethod        The former method reflection to compare to
     *
     * @return boolean
     */
    protected function didRestrictVisibility(IReflection $structureReflection, IReflectionMethod $currentMethod, IReflectionMethod $formerMethod)
    {
        if ($formerMethod && ! $formerMethod->isPrivate() && ! $formerMethod->isProtected()) {
            // the method has been public but isn't anymore
            $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::VISIBILITY_RESTRICTED, $this->mapper));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Will test if a method's visibility has been opened
     *
     * @param \TokenReflection\IReflection       $structureReflection The current structure reflection to inspect
     * @param \TokenReflection\IReflectionMethod $currentMethod       The current method reflection to inspect
     * @param \TokenReflection\IReflectionMethod $formerMethod        The former method reflection to compare to
     *
     * @return boolean
     */
    protected function didOpenVisibility(IReflection $structureReflection, IReflectionMethod $currentMethod, IReflectionMethod $formerMethod)
    {
        if ($formerMethod && ! $formerMethod->isPrivate() && ! $formerMethod->isProtected()) {
            // the method has been public but isn't anymore
            $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::VISIBILITY_OPENED, $this->mapper));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Will test if a parameter of a method has been removed
     *
     * @param \TokenReflection\IReflection       $structureReflection The current structure reflection to inspect
     * @param \TokenReflection\IReflectionMethod $currentMethod       The current method reflection to inspect
     * @param \TokenReflection\IReflectionMethod $formerMethod        The former method reflection to compare to
     *
     * @return boolean
     */
    protected function didRemoveParameter(IReflection $structureReflection, IReflectionMethod $currentMethod, IReflectionMethod $formerMethod)
    {
        if (count($formerMethod->getParameters()) > count($currentMethod->getParameters())) {
            // there are less parameters now than before
            $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::PARAMETER_REMOVED, $this->mapper));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Will test if the typehint of a parameter has been changed in a restrictive way and if a parameter has been added
     *
     * @param \TokenReflection\IReflection       $structureReflection The current structure reflection to inspect
     * @param \TokenReflection\IReflectionMethod $currentMethod       The current method reflection to inspect
     * @param \TokenReflection\IReflectionMethod $formerMethod        The former method reflection to compare to
     *
     * @return boolean
     */
    protected function didParametersChangeType(IReflection $structureReflection, IReflectionMethod $currentMethod, IReflectionMethod $formerMethod)
    {
        $formerParameters = $formerMethod->getParameters();
        $currentParameters = $currentMethod->getParameters();

        $parameterCount = count($currentParameters);
        for ($i = 0; $i < $parameterCount; $i ++) {
            // if both methods have the parameter we compare types, otherwise we check for optionality
            if (isset($formerParameters[$i])) {
                // the parameter has been here before, but are the types consisten?
                if ($formerParameters[$i]->getOriginalTypeHint() !== $currentParameters[$i]->getOriginalTypeHint()) {
                    $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::TYPEHINT_RESTRICTED, $this->mapper));
                }
            } else {
                // the parameter has not been here before, is it optional?
                if (! $currentParameters[$i]->isDefaultValueAvailable()) {
                    $this->result->addReason(new Reason($structureReflection->getName(), $currentMethod->getName(), Reason::PARAMETER_ADDED, $this->mapper));
                }
            }
        }
    }

    /**
     * Will start the inspection for a specific class
     *
     * @param \TokenReflection\IReflectionClass $reflectionClass  The current reflection to inspect
     * @param \TokenReflection\IReflectionClass $formerReflection The former inspection to compare to
     *
     * @return null
     */
    protected function inspectClass(IReflectionClass $reflectionClass, IReflectionClass $formerReflection)
    {

        // set the mapper instance we need
        $this->mapper = new ClassMapper();

        // does the current class have less public methods
        $this->didRemoveMethod($reflectionClass, $formerReflection);

        // iterate all structure methods and check them
        foreach ($reflectionClass->getMethods() as $currentMethod) {
            // get the structure- and method name for faster access
            $methodName = $currentMethod->getName();

            // check if the method did even exist before
            $formerMethod = null;
            if ($this->didMethodExistBefore($formerReflection, $methodName)) {
                $formerMethod = $formerReflection->getMethod($methodName);
            } else {
                // if there was no former method this is a reason for a version bump
                $this->result->addReason(new Reason($currentMethod, $formerReflection, Reason::METHOD_ADDED, $this->mapper));
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
     * Will start the inspection for a specific interface
     *
     * @param \TokenReflection\IReflectionClass $reflectionInterface The current reflection to inspect
     * @param \TokenReflection\IReflectionClass $formerReflection    The former inspection to compare to
     *
     * @return null
     */
    protected function inspectInterface(IReflectionClass $reflectionInterface, IReflectionClass $formerReflection)
    {

        // double check if the reflections are interfaces as we cannot make sure by type
        if (!$reflectionInterface->isInterface() || !$formerReflection->isInterface()) {
            throw new \Exception(
                sprintf(
                    'Both %s and %s must be interfaces, common classes found',
                    $reflectionInterface->getName(),
                    $formerReflection->getName()
                )
            );
        }

        // set the mapper instance we need
        $this->mapper = new InterfaceMapper();

        // does the current class have less public methods
        $this->didRemoveMethod($reflectionInterface, $formerReflection);

        // iterate all structure methods and check them
        foreach ($reflectionInterface->getMethods() as $currentMethod) {
            // get the structure- and method name for faster access
            $methodName = $currentMethod->getName();

            // check if the method did even exist before
            $formerMethod = null;
            if ($this->didMethodExistBefore($formerReflection, $methodName)) {
                $formerMethod = $formerReflection->getMethod($methodName);
            } else {
                // if there was no former method this is a reason for a version bump
                $this->result->addReason(new Reason($currentMethod, $formerReflection, Reason::METHOD_ADDED, $this->mapper));
                continue;
            }

            // only proceed for public methods (but check if it was public before)
            if ($currentMethod->isPrivate() || $currentMethod->isProtected()) {
                $this->didRestrictVisibility($reflectionInterface, $currentMethod, $formerMethod);
                continue;
            }

            // check if the method has been made public recently
            if ($this->didOpenVisibility($reflectionInterface, $currentMethod, $formerMethod)) {
                continue;
            }

            // are there less parameters now than before?
            if ($this->didRemoveParameter($reflectionInterface, $currentMethod, $formerMethod)) {
                continue;
            } else {
                // we have to check if the new parameters are optional or the parameters changed types
                $this->didParametersChangeType($reflectionInterface, $currentMethod, $formerMethod);
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
