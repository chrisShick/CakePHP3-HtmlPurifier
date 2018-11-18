<?php
namespace ChrisShick\CakePHP3HtmlPurifier\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
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
        'implementedMethods' => [],
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

    /**
     * The purifier object.
     *
     * Created in initialize()
     *
     * @var HTMLPurifier
     */
    protected $purifier;

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
        $merge_configs = ['events', 'fields', 'config', 'customFilters'];

        foreach ($merge_configs as $field) {
            if (isset($config[$field])) {
                $this->setConfig($field, $config[$field], false);
            }
        }

        /* Ensure Definition ID is set for maybeGetRawHTMLDefinition() */
        $definitionId = $this->getConfig('config.HTML.DefinitionID') ?: 'purifiable';
        $this->setConfig('config.HTML.DefinitionID', $definitionId);

        /* Add custom HTML5 support, based on HTMLPurifier's HTML4 support */
        $html5 = ($this->getConfig('config.HTML.Doctype') === 'HTML 5');
        if ($html5) {
            $this->setConfig('config.HTML.Doctype', 'HTML 4.01 Transitional');
            $this->setConfig('config.HTML.DefinitionID', $definitionId . '-html5');
        }

        /* Create config and populate */
        $purifier_config = HTMLPurifier_Config::createDefault();
        foreach ($this->getConfig('config') as $namespace => $values) {
            foreach ($values as $key => $value) {
                $purifier_config->set("{$namespace}.{$key}", $value);
            }
        }
        $customFilters = $this->getConfig('customFilters');
        if (!empty($customFilters)) {
            $filters = [];
            foreach ($customFilters as $customFilter) {
                $filters[] = new $customFilter;
            }
            $purifier_config->set('Filter.Custom', $filters);
        }

        /* Edit/add custom definitions */
        $definition = $purifier_config->maybeGetRawHTMLDefinition();
        if ($definition) {
            $this->_editDefinition($definition, $html5);
        }

        /* Create purifier object */
        $this->purifier = new HTMLPurifier($purifier_config);
    }

    /**
     * There is only one event handler, it can be configured to be called for any event
     *
     * @param \Cake\Event\Event $event Event instance.
     * @param \Cake\Datasource\EntityInterface|\ArrayObject $entity Entity instance.
     * @return true (irrespective of the behavior logic, the save will not be prevented)
     */
    public function handleEvent(Event $event, $entity)
    {
        $eventName = $event->getName();
        $events = $this->_config['events'];

        if ($events[$eventName] === true) {
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
     * @param mixed $entity orm entity
     * @return void
     */
    protected function _purify($entity)
    {
        $fields = $this->getConfig('fields');
        $purify = function ($value, $key, $entity) {
            if (isset($entity[$value])) {
                $entity[$value] = $this->purifier->purify($entity[$value]);
            }
        };
        array_walk($fields, $purify, $entity);
    }

    /**
     * _editDefinition
     *
     * Change HTML definition.
     *
     * @param \HTMLPurifier_HTMLDefinition $def definition for html purifier
     * @param mixed $html5 bool Add HTML5 definition
     * See https://github.com/xemlock/htmlpurifier-html5
     * @return void
     */
    protected function _editDefinition(\HTMLPurifier_HTMLDefinition $def, $html5)
    {
        if (!$html5) {
            return;
        }

        // http://developers.whatwg.org/sections.html
        $def->addElement('section', 'Block', 'Flow', 'Common');
        $def->addElement('nav', 'Block', 'Flow', 'Common');
        $def->addElement('article', 'Block', 'Flow', 'Common');
        $def->addElement('aside', 'Block', 'Flow', 'Common');
        $def->addElement('header', 'Block', 'Flow', 'Common');
        $def->addElement('footer', 'Block', 'Flow', 'Common');
        $def->addElement('main', 'Block', 'Flow', 'Common');

        // Content model actually excludes several tags, not modelled here
        $def->addElement('address', 'Block', 'Flow', 'Common');
        $def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');

        // http://developers.whatwg.org/grouping-content.html
        $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
        $def->addElement('figcaption', 'Inline', 'Flow', 'Common');

        // http://developers.whatwg.org/the-video-element.html#the-video-element
        $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
            'src' => 'URI',
            'type' => 'Text',
            'width' => 'Length',
            'height' => 'Length',
            'poster' => 'URI',
            'preload' => 'Enum#auto,metadata,none',
            'controls' => 'Bool',
        ]);
        $def->addElement('source', 'Block', 'Empty', 'Common', ['src' => 'URI', 'type' => 'Text']);

        // http://developers.whatwg.org/text-level-semantics.html
        $def->addElement('s', 'Inline', 'Inline', 'Common');
        $def->addElement('var', 'Inline', 'Inline', 'Common');
        $def->addElement('sub', 'Inline', 'Inline', 'Common');
        $def->addElement('sup', 'Inline', 'Inline', 'Common');
        $def->addElement('mark', 'Inline', 'Inline', 'Common');
        $def->addElement('wbr', 'Inline', 'Empty', 'Core');

        // http://developers.whatwg.org/edits.html
        $def->addElement('ins', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'Text']);
        $def->addElement('del', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'Text']);

        // TIME
        $time = $def->addElement('time', 'Inline', 'Inline', 'Common', ['datetime' => 'Text', 'pubdate' => 'Bool']);
        $time->excludes = ['time' => true];

        // IMG
        $def->addAttribute('img', 'srcset', 'Text');

        // IFRAME
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
    }
}
