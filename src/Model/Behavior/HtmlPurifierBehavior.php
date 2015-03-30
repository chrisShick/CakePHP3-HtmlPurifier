<?php
namespace chrisShick\CakePHP3HtmlPurifier\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Table;
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
        'fields' => [],
        'overwrite' => false,
        'affix' => '_clean',
        'affix_position' => 'suffix',
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

    public function initialize(array $config)
    {
        parent::initialize($config);
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

    public function beforeMarshal(Event $event, \ArrayObject $data, \ArrayObject $options)
    {
        $fields = $this->config('fields');
        $purify = function(&$value,$key, $fields) use(&$purify){
            if(is_array($value)){
                if(array_key_exists($key,$fields)){
                    array_walk($value, $purify, $fields[$key]);
                }
            } else if(in_array($key,$fields)){
                $value = $this->purifier->purify($value);
            }
        };
        array_walk($data,$purify, $fields);
    }

}
