<?php
namespace AssociationsDebugger\Test\TestCase\View\Helper;

use AssociationsDebugger\View\Helper\StructureBuilderHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * AssociationsDebugger\View\Helper\StructureBuilderHelper Test Case
 */
class StructureBuilderHelperTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \AssociationsDebugger\View\Helper\StructureBuilderHelper
     */
    public $StructureBuilder;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $view = new View();
        $this->StructureBuilder = new StructureBuilderHelper($view);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->StructureBuilder);

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
