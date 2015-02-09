<?php

/**
 * /Wicked\Semcrement\Entities\Reason
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

namespace Wicked\Semcrement\Entities;

use TokenReflection\IReflection;

/**
 * Reason entity which is used to determine the final version incrementation
 *
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
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
     * The element currently under inspection
     *
     * @var \TokenReflection\IReflection $currentElement
     */
    protected $currentElement;

    /**
     * Explaination for this reason
     *
     * @var unknown
     */
    protected $explaination;

    /**
     * The former representation of the element currently under inspection
     *
     * @var \TokenReflection\IReflection $formerElement
     */
    protected $formerElement;

    /**
     * Identifier used to determine explaination and severity
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
     * @param IReflection $currentElement   The reflection element currently under inspection
     * @param IReflection $formerElement    The former reflection element as a comparison
     * @param string      $reasonIdentifier Unique identifier for the reason
     */
    public function __construct(IReflection $currentElement, IReflection $formerElement, $reasonIdentifier)
    {
        $this->currentElement = $currentElement;
        $this->formerElement = $formerElement;
        $this->reasonIdentifier = $reasonIdentifier;

        // initialize the reason instance
        $this->init();
    }

    /**
     * Getter for the explaination
     *
     * @return string
     */
    public function getExplaination()
    {
        return $this->explaination;
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
     * Will initialize the reason instance based on the reason identifier
     *
     * @return null
     */
    protected function init()
    {
        // map the reason identifier to a severity and explaination
        switch ($this->reasonIdentifier)
        {
            case self::METHOD_ADDED:

                $this->explaination = sprintf(
                    'Added formerly unknown public method %s to structure %s',
                    $this->currentElement->getName(),
                    $this->currentElement->getDeclaringClassName()
                );
                $this->severity = self::MINOR;
                break;

            case self::METHOD_REMOVED:

                $this->explaination = sprintf(
                    'Removed formerly known public method %s from structure %s',
                    $this->formerElement->getName(),
                    $this->formerElement->getDeclaringClassName()
                );
                $this->severity = self::MAJOR;
                break;

            case self::PARAMETER_ADDED:

                $this->explaination = sprintf(
                    'Added formerly unknown parameter %s of method %s',
                    $this->currentElement->getName(),
                    $this->currentElement->getDeclaringFunctionName()
                );
                $this->severity = self::MAJOR;
                break;

            case self::PARAMETER_REMOVED:

                $this->explaination = sprintf(
                    'Removed formerly known parameter %s of method %s',
                    $this->currentElement->getName(),
                    $this->currentElement->getDeclaringFunctionName()
                );
                $this->severity = self::PATCH;
                break;

            case self::PARAMETER_RETYPED:

                $this->explaination = sprintf(
                    'Changed type of parameter %s of method %s from %s to %s',
                    $this->currentElement->getName(),
                    $this->currentElement->getDeclaringFunctionName(),
                    $this->formerElement->getOriginalTypeHint(),
                    $this->currentElement->getOriginalTypeHint()
                );
                $this->severity = self::MAJOR;
                break;

            case self::VISIBILITY_OPENED:

                $this->explaination = sprintf(
                    'Made formerly inaccessable method %s of structure %s public',
                    $this->currentElement->getName(),
                    $this->currentElement->getDeclaringClassName()
                );
                $this->severity = self::MINOR;
                break;

            case self::VISIBILITY_RESTRICTED:

                $this->explaination = sprintf(
                    'Restricted access of formerly public method %s  of structure %s',
                    $this->currentElement->getName(),
                    $this->currentElement->getDeclaringClassName()
                );
                $this->severity = self::MAJOR;
                break;

            default:

                // only known identifiers here
                throw new \Exception(sprintf('Trying to instantiate reason with invalid identifier %s', $this->reasonIdentifier));
        }
    }
}
