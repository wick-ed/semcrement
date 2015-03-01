<?php

/**
 * \Wicked\Semcrement\Tests\Functional\StructureChangeTest
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

namespace Wicked\Semcrement\Functional;

use Wicked\Semcrement\Core;
use Wicked\Semcrement\Functional\Mocks\MockCache;
use Wicked\Semcrement\DefaultInspector;
use Herrera\Version\Dumper;

/**
 * todo
 *
 * @author    Bernhard Wick <wick.b@hotmail.de>
 * @copyright 2015 Bernhard Wick <wick.b@hotmail.de>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/wick-ed/semcrement
 */
class StructureChangeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Base path leading to our test files
     *
     * @var string $basePath
     */
    protected $basePath;

    /**
     *
     * @var \Wicked\Semcrement\Interfaces\CacheInterface $cache
     */
    protected $cache;

    /**
     *
     * @var \Wicked\Semcrement\Core $core
     */
    protected $core;

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->cache = new MockCache();

        $this->basePath = \realpath(
            __DIR__ . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '_files' .
            DIRECTORY_SEPARATOR . 'current'
            )
         . DIRECTORY_SEPARATOR;
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->core = new Core();
        $this->core->setCache($this->cache);
    }

    /**
     *
     */
    public function testAddedPublicMethodInClass()
    {
        $this->assertEquals(
            '1.1.0',
            Dumper::toString($this->core->runInspection($this->basePath . 'Classes' . DIRECTORY_SEPARATOR . 'AddedPublicMethod.php'))
            );
    }

    /**
     *
     */
    public function testDeletedPublicMethodInClass()
    {
        $this->assertEquals(
            '2.0.0',
            Dumper::toString($this->core->runInspection($this->basePath . 'Classes' . DIRECTORY_SEPARATOR . 'MissingPublicMethod.php'))
        );
    }

    /**
     *
     */
    public function testAddedPublicMethodInInterface()
    {
        $this->assertEquals(
            '2.0.0',
            Dumper::toString($this->core->runInspection($this->basePath . 'Interfaces' . DIRECTORY_SEPARATOR . 'AddedPublicMethod.php'))
        );
    }
}
