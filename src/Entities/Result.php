<?php

/**
 * /Wicked\Semcrement\Entities\Result
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

/**
 * The result for any semcrement version check
 *
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */
class Result
{

    /**
     * In which way the version has to be incremeted
     *
     * @var string $incrementVersion
     */
    protected $incrementVersion;

    /**
     * Collection of reasons found for incrementing the version the way we do
     *
     * @var array $reasons
     */
    protected $reasons;

    /**
     *
     */
    public function __construct()
    {
        $this->reasons = array();
    }

    /**
     * Adds a reason to our reason collection
     *
     * @param Reason $reason The reason instance to add
     *
     * @return null
     *
     * @throws \Exception
     */
    public function addReason(Reason $reason)
    {
        $this->reasons[$reason->getSeverity()][] = $reason;

        // we have to keep the final version to increment up to date
        $this->updateVersion();
    }

    /**
     * Getter for the $incrementVersion property
     *
     * @return string
     */
    public function getIncrementVersion()
    {
        return $this->incrementVersion;
    }

    /**
     * Will update the increment version based on the current reasons
     *
     * @return null
     */
    protected function updateVersion()
    {
        // we only have to act if aren't at major version already
        if ($this->incrementVersion === Reason::MAJOR) {
            return;
        }

        // check which reasons we have
        if (isset($this->reasons[Reason::PATCH])) {
            $this->incrementVersion = Reason::PATCH;
        }
        if (isset($this->reasons[Reason::MINOR])) {
            $this->incrementVersion = Reason::MINOR;
        }
        if (isset($this->reasons[Reason::MAJOR])) {
            $this->incrementVersion = Reason::MAJOR;
        }
    }
}
