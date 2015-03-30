<?php
namespace chrisShick\CakePHP3HtmlPurifier\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use chrisShick\CakePHP3HtmlPurifier\Model\Behavior\HtmlPurifierBehavior;

/**
 * ChrisShick\CakePHP3-HtmlPurifier\Model\Behavior\HtmlPurifierBehavior Test Case
 */
class HtmlPurifierBehaviorTest extends TestCase
{

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->HtmlPurifier = new HtmlPurifierBehavior();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->HtmlPurifier);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
