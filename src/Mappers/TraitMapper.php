<?php

/**
 * \Wicked\Semcrement\Mappers\InterfaceMapper
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

namespace Wicked\Semcrement\Mappers;

use Wicked\Semcrement\Entities\Reason;
use Wicked\Semcrement\Interfaces\MapperInterface;

/**
 * Mapper class which is used to map reasons for version number incrementation to the
 * severity the reason has for trait structures
 *
 * @author Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link https://github.com/wick-ed/semcrement
 */
class InterfaceMapper implements MapperInterface
{

    /**
     * Will initialize the reason instance based on the reason identifier
     *
     * @param \Wicked\Semcrement\Entities\Reason $reason The reason instance to initialize
     *
     * @return null
     */
    public function map(Reason $reason)
    {
        // map the reason identifier to a severity and explanation
        switch ($reason->getReasonIdentifier()) {
            case Reason::METHOD_ADDED:

                $reason->setExplanation(sprintf('Added formerly unknown public method %s to structure %s', $reason->getCurrentElement()->getName(), $reason->getCurrentElement()->getDeclaringClassName()));
                $reason->setSeverity(Reason::MINOR);
                break;

            case Reason::METHOD_REMOVED:

                $reason->setExplanation(sprintf('Removed formerly known public method %s from structure %s', $reason->getFormerElement()->getName(), $reason->getFormerElement()->getDeclaringClassName()));
                $reason->setSeverity(Reason::MAJOR);
                break;

            case Reason::PARAMETER_ADDED:

                $reason->setExplanation(sprintf('Added formerly unknown parameter %s of method %s', $reason->getCurrentElement()->getName(), $reason->getCurrentElement()->getDeclaringFunctionName()));
                $reason->setSeverity(Reason::MAJOR);
                break;

            case Reason::PARAMETER_REMOVED:

                $reason->setExplanation(sprintf('Removed formerly known parameter %s of method %s', $reason->getCurrentElement()->getName(), $reason->getCurrentElement()->getDeclaringFunctionName()));
                $reason->setSeverity(Reason::PATCH);
                break;

            case Reason::TYPEHINT_RESTRICTED:

                $reason->setExplanation(sprintf('Changed type of parameter %s of method %s from %s to %s', $reason->getCurrentElement()->getName(), $reason->getCurrentElement()->getDeclaringFunctionName(), $reason->getFormerElement()->getOriginalTypeHint(), $reason->getCurrentElement()->getOriginalTypeHint()));
                $reason->setSeverity(Reason::MAJOR);
                break;

            case Reason::VISIBILITY_OPENED:

                $reason->setExplanation(sprintf('Made formerly inaccessable method %s of structure %s public', $reason->getCurrentElement()->getName(), $reason->getCurrentElement()->getDeclaringClassName()));
                $reason->setSeverity(Reason::MINOR);
                break;

            case Reason::VISIBILITY_RESTRICTED:

                $reason->setExplanation(sprintf('Restricted access of formerly public method %s  of structure %s', $reason->getCurrentElement()->getName(), $reason->getCurrentElement()->getDeclaringClassName()));
                $reason->setSeverity(Reason::MAJOR);
                break;

            default:

                // only known identifiers here
                throw new \Exception(sprintf('Trying to instantiate reason with invalid identifier %s', $reasonIdentifier));
        }
    }
}
