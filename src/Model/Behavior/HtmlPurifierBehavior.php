<?php
namespace chrisShick\CakePHP3HtmlPurifier\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior;
use Cake\Event\Event;
use HTMLPurifier;
use HTMLPurifier_Config;
/**
 * HtmlPurifier behavior
 */
class HtmlPurifierBehavior extends Behavior
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'implementedFinders' => [],
        'implementedMethods' => [
            'timestamp' => 'timestamp',
            'touch' => 'touch'
        ],
        'events' => [
            'Model.beforeSave' => false,
            'Model.beforeMarshal' => true,
        ],
        'fields' => [],
        'config' => [
            'HTML' => [
                'DefinitionID' => 'purifiable',
                'DefinitionRev' => 1,
                'TidyLevel' => 'heavy',
                'Doctype' => 'XHTML 1.0 Transitional'
            ],
            'Core' => [
                'Encoding' => 'UTF-8'
            ],
            'AutoFormat' => [
                'RemoveSpansWithoutAttributes' => true,
                'RemoveEmpty' => true
            ],
        ],
        'customFilters' => []
    ];

    private $purifier;

    /**
     * Initialize hook
     *
     * If events are specified - do *not* merge them with existing events,
     * overwrite the events to listen on
     *
     * @param array $config The config for this behavior.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        if (isset($config['events'])) {
            $this->config('events', $config['events'], false);
        }

        $purifier_config = HTMLPurifier_Config::createDefault();
        foreach ($this->config('config') as $namespace => $values) {
            foreach ($values as $key => $value) {
                $purifier_config->set("{$namespace}.{$key}", $value);
            }
        }
        $customFilters = $this->config('customFilters');
        if (!empty($customFilters)) {
            $filters = array();
            foreach ($customFilters as $customFilter) {
                $filters[] = new $customFilter;
            }
            $purifier_config->set('Filter.Custom', $filters);
        }
        $this->purifier = new HTMLPurifier($purifier_config);
    }

    /**
     * There is only one event handler, it can be configured to be called for any event
     *
     * @param \Cake\Event\Event $event Event instance.
     * @param \Cake\Datasource\EntityInterface $entity Entity instance.
     * @throws \UnexpectedValueException if a field's when value is misdefined
     * @return true (irrespective of the behavior logic, the save will not be prevented)
     * @throws \UnexpectedValueException When the value for an event is not 'always', 'new' or 'existing'
     */
    public function handleEvent(Event $event, EntityInterface $entity)
    {
        $eventName = $event->name();
        $events = $this->_config['events'];
        
        if($events[$eventName] === true) {
            $this->_purify($entity);
        }

        return true;
    }
    /**
     * implementedEvents
     *
     * The implemented events of this behavior depend on configuration
     *
     * @return array
     */
    public function implementedEvents()
    {
        return array_fill_keys(array_keys($this->_config['events']), 'handleEvent');
    }


    /**
     * _purify
     *
     * The private method to purify the entity with the Html Purifier Library
     *
     * @param $entity
     *
     */
    private function _purify($entity)
    {
        $fields = $this->config('fields');
        $purify = function($value, $key, $entity) {
            $return_value = $this->purifier->purify($entity->$value);
            $entity->set($value,$return_value);
        };
        array_walk($fields,$purify, $entity);
    }
}
