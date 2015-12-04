<?php
namespace chrisShick\CakePHP3HtmlPurifier\Test\TestCase\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use ChrisShick\CakePHP3HtmlPurifier\Model\Behavior\HtmlPurifierBehavior;

/**
 * ChrisShick\CakePHP3-HtmlPurifier\Model\Behavior\HtmlPurifierBehavior Test Case
 */
class HtmlPurifierBehaviorTest extends TestCase
{

    /**
     * autoFixtures
     *
     * Don't load fixtures for all tests
     *
     * @var bool
     */
    public $autoFixtures = false;


    /**
     * Sanity check Implemented events
     *
     * @return void
     */
    public function testImplementedEventsDefault()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $this->Behavior = new HtmlPurifierBehavior($table);
        $expected = [
            'Model.beforeSave' => 'handleEvent',
            'Model.beforeMarshal' => 'handleEvent'
        ];
        $this->assertEquals($expected, $this->Behavior->implementedEvents());
    }
    /**
     * testImplementedEventsCustom
     *
     * The behavior allows for handling any event - test an example
     *
     * @return void
     */
    public function testImplementedEventsCustom()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $settings = ['events' => ['Something.special' => ['date_specialed' => 'always']]];
        $this->Behavior = new HtmlPurifierBehavior($table, $settings);
        $expected = [
            'Something.special' => 'handleEvent'
        ];
        $this->assertEquals($expected, $this->Behavior->implementedEvents());
    }

    public function testMarshalBasic()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $this->Behavior = new HtmlPurifierBehavior($table,['fields' => ['name','place']]);

        $event = new Event('Model.beforeMarshal');

        $entity = new Entity(['name' => 'Foo', 'place' => '<script>alert(Bar);</script>']);
        $return = $this->Behavior->handleEvent($event, $entity);

        $this->assertTrue($return, 'Handle Event is expected to always return true');

        $this->assertEquals(['name' => 'Foo', 'place' => ''], $entity->toArray());
    }

    public function testMarshalFalse()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $this->Behavior = new HtmlPurifierBehavior($table,[
            'events'=>[
                'Model.beforeMarshal' => false
            ],
            'fields' => ['name','place']
        ]);

        $event = new Event('Model.beforeMarshal');
        $expected = ['name' => 'Foo', 'place' => '<script>alert(Bar);</script>'];
        $entity = new Entity($expected);
        $return = $this->Behavior->handleEvent($event, $entity);

        $this->assertTrue($return, 'Handle Event is expected to always return true');

        $this->assertEquals($expected, $entity->toArray());
    }

    public function testMarshalConfigChange()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $this->Behavior = new HtmlPurifierBehavior($table,[
            'fields' => ['name','place'],
            'config' => [
                'AutoFormat' => [
                    'RemoveSpansWithoutAttributes' => false,
                    'RemoveEmpty' => true
                ]
            ]
        ]);

        $event = new Event('Model.beforeMarshal');
        $expected = ['name' => 'Foo', 'place' => '<span>Bar</span>'];
        $entity = new Entity($expected);
        $return = $this->Behavior->handleEvent($event, $entity);

        $this->assertTrue($return, 'Handle Event is expected to always return true');

        $this->assertEquals($expected, $entity->toArray());
    }

    public function testMarshalNoFields()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $this->Behavior = new HtmlPurifierBehavior($table);

        $event = new Event('Model.beforeMarshal');
        $expected = ['name' => 'Foo', 'place' => '<span>Bar</span>'];
        $entity = new Entity($expected);
        $return = $this->Behavior->handleEvent($event, $entity);

        $this->assertTrue($return, 'Handle Event is expected to always return true');

        $this->assertEquals($expected, $entity->toArray());
    }

    public function testSaveBasic()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $this->Behavior = new HtmlPurifierBehavior($table,['fields' => ['name','place']]);

        $event = new Event('Model.beforeSave');
        $expected = ['name' => 'Foo', 'place' => '<span>Bar</span>'];
        $entity = new Entity($expected);
        $return = $this->Behavior->handleEvent($event, $entity);

        $this->assertTrue($return, 'Handle Event is expected to always return true');

        $this->assertEquals($expected, $entity->toArray());
    }

    public function testSaveEnabled()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $this->Behavior = new HtmlPurifierBehavior($table,[
            'events' => [
                'Model.beforeSave' => true
            ],
            'fields' => ['name','place']
        ]);

        $event = new Event('Model.beforeSave');

        $entity = new Entity(['name' => 'Foo', 'place' => '<span>Bar</span>']);
        $return = $this->Behavior->handleEvent($event, $entity);

        $this->assertTrue($return, 'Handle Event is expected to always return true');

        $this->assertEquals(['name' => 'Foo', 'place' => 'Bar'], $entity->toArray());
    }

    public function testSaveConfigChange()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $this->Behavior = new HtmlPurifierBehavior($table,[
            'events' => [
                'Model.beforeSave' => true
            ],
            'fields' => ['name','place'],
            'config' => [
                'AutoFormat' => [
                    'RemoveSpansWithoutAttributes' => false,
                    'RemoveEmpty' => true
                ]
            ]
        ]);

        $event = new Event('Model.beforeSave');
        $expected = ['name' => 'Foo', 'place' => '<span>Bar</span>'];
        $entity = new Entity($expected);
        $return = $this->Behavior->handleEvent($event, $entity);

        $this->assertTrue($return, 'Handle Event is expected to always return true');

        $this->assertEquals($expected, $entity->toArray());
    }

    public function testSaveNoFields()
    {
        $table = $this->getMock('Cake\ORM\Table');
        $this->Behavior = new HtmlPurifierBehavior($table,[
            'events' => [
                'Model.beforeSave' => true
            ],
        ]);

        $event = new Event('Model.beforeSave');
        $expected = ['name' => 'Foo', 'place' => '<span>Bar</span>'];
        $entity = new Entity($expected);
        $return = $this->Behavior->handleEvent($event, $entity);

        $this->assertTrue($return, 'Handle Event is expected to always return true');

        $this->assertEquals($expected, $entity->toArray());
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Behavior);

        parent::tearDown();
    }
}
