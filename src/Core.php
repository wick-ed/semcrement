<?php

/**
 * \Wicked\Semcrement\Core
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

use TokenReflection\Broker;
use TokenReflection\IReflection;
use TokenReflection\Broker\Backend\Memory;
use Herrera\Version\Dumper;
use Herrera\Version\Parser;
use Wicked\Semcrement\Interfaces\CacheInterface;
use Wicked\Semcrement\Interfaces\InspectorInterface;
use Wicked\Semcrement\Annotations\ApiAnnotation;

/**
 * Core class which does everything right now
 *
 * @author Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link https://github.com/wick-ed/semcrement
 */
class Core
{

    /**
     * Base version to start incrementing from
     *
     * @var \Herrera\Version\Builder $baseVersion
     */
    protected $baseVersion;

    /**
     * The inspector to use
     *
     * @var \Wicked\Semcrement\Interfaces\Inspector $inspector
     */
    protected $inspector;

    /**
     * The cache used to store our former inspection data
     *
     * @var \Wicked\Semcrement\Interfaces\CacheInterface $cache
     */
    protected $cache;

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Will init the instance of our core class
     *
     * @return null
     */
    protected function init()
    {
        $this->baseVersion = Parser::toBuilder('1.0.0');
        $this->inspector = new DefaultInspector();
        $this->cache = new Cache();
    }

    /**
     * Getter for the base version
     *
     * @return \Herrera\Version\Builder
     */
    public function getBaseVersion()
    {
        return $this->baseVersion;
    }

    /**
     * Will set the base version
     *
     * @param \Herrera\Version\Builder $baseVersion Our base version to set
     *
     * @return null
     */
    public function setBaseVersion($baseVersion)
    {
        $this->baseVersion = $baseVersion;
    }

    /**
     * Getter for the cache instance
     *
     * @return \Wicked\Semcrement\Interfaces\CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Setter for the cache instance
     *
     * @param \Wicked\Semcrement\Interfaces\CacheInterface $cache The cache to be used
     *
     * @return null
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Getter for our inspector instance
     *
     * @return \Wicked\Semcrement\Interfaces\Inspector
     */
    public function getInspector()
    {
        return $this->inspector;
    }

    /**
     * Setter for our inspector instance
     *
     * @param \Wicked\Semcrement\Interfaces\InspectorInterface $inspector The inspector to be used
     *
     * @return null
     */
    public function setInspector(InspectorInterface $inspector)
    {
        $this->inspector = $inspector;
    }

    /**
     * Will run an inspection for a certain file/directory and will return a version instance
     * resulting from the changes compared to former inspections
     *
     * @param string $srcPath Path to the file/directory to inspect
     *
     * @return \Herrera\Version\Builder
     *
     * @throws \Exception
     */
    public function runInspection($srcPath)
    {

        // check if we got something we can work with
        if (! is_readable($srcPath)) {
            throw new \Exception(sprintf('Cannot read from source path %s', $srcPath));
        }

        $broker = new Broker(new Memory());
        if (is_dir($srcPath)) {
            $broker->processDirectory($srcPath);
        } else {
            $broker->processFile($srcPath);
        }

        // iterate all php files and check for an API annotation
        $inspector = $this->getInspector();
        foreach ($broker->getClasses() as $classReflection) {
            // we can continue iteration if we did not get any valuable information from this file
            if (! $classReflection instanceof \TokenReflection\ReflectionClass) {
                continue;
            }

            // if we got the API annotation we have to work with it
            if ($classReflection->hasAnnotation(ApiAnnotation::ANNOTATION)) {
                $formerReflection = $this->cache->load($classReflection->getName());

                // check for possible changes
                $inspector->inspect($classReflection, $formerReflection);

                // save the current reflection object as a base for later comparison
                $this->cache->store($classReflection);
            }
        }

        // get the result and increment the version accordingly
        $result = $inspector->getResult();
        $incrementationMethod = 'increment' . ucfirst(strtolower($result->getIncrementVersion()));

        $version = $this->getBaseVersion();
        if (method_exists($version, $incrementationMethod)) {
            $version->$incrementationMethod();
        }

        return $version;
    }
}
