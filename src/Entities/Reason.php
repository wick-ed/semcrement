<?php

/**
 * \Wicked\Semcrement\Entities\Reason
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

namespace Wicked\Semcrement\Entities;

use TokenReflection\IReflection;
use Wicked\Semcrement\DefaultMapper;
use Wicked\Semcrement\Interfaces\MapperInterface;

/**
 * Reason entity which is used to determine the final version incrementation
 *
 * @author Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link https://github.com/wick-ed/semcrement
 */
class Reason
{

    /**
     * The keyword for a major severity
     *
     * @var string MAJOR
     */
    const MAJOR = 'MAJOR';

    /**
     * The keyword for a minor severity
     *
     * @var string MINOR
     */
    const MINOR = 'MINOR';

    /**
     * The keyword for a patch severity
     *
     * @var string PATCH
     */
    const PATCH = 'PATCH';

    /**
     * An accessible method has been removed from a structure
     *
     * @var string METHOD_REMOVED
     */
    const METHOD_REMOVED = 'METHOD_REMOVED';

    /**
     * The visibility of an element has been restricted
     *
     * @var string VISIBILITY_RESTRICTED
     */
    const VISIBILITY_RESTRICTED = 'VISIBILITY_RESTRICTED';

    /**
     * The visibility of an element has been opened
     *
     * @var string VISIBILITY_OPENED
     */
    const VISIBILITY_OPENED = 'VISIBILITY_OPENED';

    /**
     * An accessible parameter has been removed from a method signature
     *
     * @var string PARAMETER_REMOVED
     */
    const PARAMETER_REMOVED = 'PARAMETER_REMOVED';

    /**
     * An accessible property has been added to a structure
     *
     * @var string PROPERTY_ADDED
     */
    const PROPERTY_ADDED = 'PROPERTY_ADDED';

    /**
     * An accessible property has been removed from a structure
     *
     * @var string PROPERTY_REMOVED
     */
    const PROPERTY_REMOVED = 'PROPERTY_REMOVED';

    /**
     * An accessible parameter has been added to a method signature
     *
     * @var string PARAMETER_ADDED
     */
    const PARAMETER_ADDED = 'PARAMETER_ADDED';

    /**
     * The typehint of a parameter has been changed in a way which restricts the type
     *
     * @var string TYPEHINT_RESTRICTED
     */
    const TYPEHINT_RESTRICTED = 'TYPEHINT_RESTRICTED';

    /**
     * An accessible method has been added to a structure
     *
     * @var string METHOD_ADDED
     */
    const METHOD_ADDED = 'METHOD_ADDED';

    /**
     * The element currently under inspection
     *
     * @var \TokenReflection\IReflection $currentElement
     */
    protected $currentElement;

    /**
     * Explanation for this reason
     *
     * @var unknown
     */
    protected $explanation;

    /**
     * The former representation of the element currently under inspection
     *
     * @var \TokenReflection\IReflection $formerElement
     */
    protected $formerElement;

    /**
     * The mapper used for our reason instances
     *
     * @var \Wicked\Semcrement\Interfaces\MapperInterface $mapper
     */
    protected $mapper;

    /**
     * Identifier for the reason explanation and severity
     *
     * @var string $reasonIdentifier
     */
    protected $reasonIdentifier;

    /**
     * The severity of this reason which will be looked at for the final incrementation
     *
     * @var string $severity
     *
     * @Enum({"MAJOR", "MINOR", "PATCH"})
     */
    protected $severity;

    /**
     * Default constructor
     *
     * @param IReflection     $currentElement   The reflection element currently under inspection
     * @param IReflection     $formerElement    The former reflection element as a comparison
     * @param string          $reasonIdentifier Unique identifier for the reason
     * @param MapperInterface $mapper           The mapper instance to inject
     */
    public function __construct(
        IReflection $currentElement,
        IReflection $formerElement,
        $reasonIdentifier,
        MapperInterface $mapper
    ) {
        $this->currentElement = $currentElement;
        $this->formerElement = $formerElement;
        $this->reasonIdentifier = $reasonIdentifier;
        $this->mapper = $mapper;

        // initialize the reason instance
        $this->init();
    }

    /**
     * Getter for the explanation
     *
     * @return string
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Getter for the former element
     *
     * @return string
     */
    public function getFormerElement()
    {
        return $this->formerElement;
    }

    /**
     * Getter for the current element
     *
     * @return string
     */
    public function getCurrentElement()
    {
        return $this->currentElement;
    }

    /**
     * Getter for the reason identifier
     *
     * @return string
     */
    public function getReasonIdentifier()
    {
        return $this->reasonIdentifier;
    }

    /**
     * Getter for the severity
     *
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Will initialize the dependencies for our reason
     *
     * @return null
     */
    protected function init()
    {
        $this->mapper->map($this);
    }

    /**
     * Inject a reason mapper
     *
     * @param \Wicked\Semcrement\Interfaces\MapperInterface $mapper The mapper instance to inject
     *
     * @return null
     */
    protected function injectMapper(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Setter for the explanation
     *
     * @param string $explanation The explanation for this incrementation reason
     *
     * @return string
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;
    }

    /**
     * Setter for the severity
     *
     * @param string $severity The severity of this incrementation reason
     *
     * @return string
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }
}
