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

namespace Wicked\Semcrement;

use TokenReflection\IReflection;
use Wicked\Semcrement\Entities\Result;
use Wicked\Semcrement\Entities\Reason;

/**
 * Wicked\Semcrement\Inspector
 *
 * Class used to do the inspection and comparison of APIs
 *
 * @category  Service
 * @package   Semcrement
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2014 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */
class Inspector
{
    const CACHE_PATH = '/../var/tmp/cache/';
    
    /**
     * 
     * @var unknown
     */
    protected $result;
    
    /**
     * 
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
    public function checkForChanges(IReflection $structureReflection)
    {
        // to check for changes we have to have information former interfaces
        $structureName = $structureReflection->getName();
        $cachePath = $this->calculateCachePath($structureName);
        if (!is_readable($cachePath)) {
    
            // cache it so the next run will be a success
            $this->cacheReflection($structureReflection);
            throw new \Exception(sprintf('Missing information about former interfaces of %s', $structureName));
        }
    
        // get the former reflection and check if we got the correct one
        $formerReflection = unserialize(file_get_contents($cachePath));
        if ($formerReflection->getName() !== $structureReflection->getName()) {
    
            throw new \Exception(sprintf(
                'Mismatch of former and current structure information. %s !== %s',
                $formerReflection->getName(),
                $structureReflection->getName()
            ));
        }
    
        // do the actual check
        foreach ($structureReflection->getMethods() as $currentMethod) {
            $methodName = $currentMethod->getName();
    
            // check if the method did even exist before
            $formerMethod = null;
            if ($formerReflection->hasMethod($methodName)) {
                
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
     *
     * @param string $structureName
     * @return type
     */
    protected function calculateCachePath($structureName)
    {
        return __DIR__ . self::CACHE_PATH . str_replace('\\', '_', $structureName);
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
