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
 * Wicked\Semcrement\Entities\Result
 *
 * todo
 *
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */
class Result
{
    /**
     * 
     * @var unknown
     */
    const MAJOR = 'MAJOR';
    
    /**
     * 
     * @var unknown
     */
    const MINOR = 'MINOR';
    
    /**
     * 
     * @var unknown
     */
    const PATCH = 'PATCH';
    
    /**
     * 
     * @var unknown
     */
    protected $incrementVersion;
    
    /**
     * 
     * @var unknown
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
     * 
     * @param Reason $reason
     * @param unknown $version
     * @throws \Exception
     */
    public function addReason(Reason $reason, $version)
    {
        // only three possible versions here
        if ($version !== self::MAJOR && $version !== self::MINOR && $version !== self::PATCH) {
            
            throw new \Exception(sprintf('Reason provided for invalid version bump %s', $version));
        }
        
        $this->reasons[$version][] = $reason;
        
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
     * 
     */
    protected function updateVersion()
    {
        // we only have to act if aren't at major version already
        if ($this->incrementVersion === self::MAJOR) {
            
            return;
        }
        
        // check which reasons we have
        if (isset($this->reasons[self::PATCH])) {
        
            $this->incrementVersion = self::PATCH;
        }
        if (isset($this->reasons[self::MINOR])) {
        
            $this->incrementVersion = self::MINOR;
        }
        if (isset($this->reasons[self::MAJOR])) {
            
            $this->incrementVersion = self::MAJOR;
        }
    }
}
