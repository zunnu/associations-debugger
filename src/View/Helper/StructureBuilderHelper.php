<?php
namespace AssociationsDebugger\View\Helper;

use Cake\View\Helper;
use Cake\View\View;

/**
 * StructureBuilder helper
 */
class StructureBuilderHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'showDeepChildren' => true,
    ];

    /**
     * Holder for structure that will be drawn
     * @var array
     */
    protected $structure = [
        1 => [
            'label' => 'Root',
            'type' => 'Root',
            'parent' => null,
            'style' => 'fill: dodgerblue !important; cursor: pointer;',
            'plugin' => '',
            'model' => '',
        ],
    ];

    /**
     * Core names that have been handled already. Basicly contains "App" and plugin names
     * @var array
     */
    protected $handledNames = [];

    /**
     * Running number to build unique identifiers and link to parents
     * @var integer
     */
    protected $count = 1;

    /**
     * Return the current stucture holder
     * @return array
     */
    public function getStructure() {
        return $this->structure;
    }

    /**
     * Main build entry. Handles formating the data
     * @param  array $associationCollections  Collection from the gate
     * @return array                          Formated data
     */
    public function build($associationCollections) {
        if(empty($associationCollections)) return $this->structure;

        $childs = [];
        foreach ($associationCollections as $pluginName => $plugin) {
            ++$this->count;
            $pluginNumber = $this->count;

            if(!in_array($pluginName, $this->handledNames)) {
                $this->handledNames[] = $pluginName;

                $this->structure[$pluginNumber] = [
                    'label' => $pluginName,
                    'type' => 'Plugin',
                    'parent' => 1,
                    'style' => 'fill: lightblue !important; cursor: pointer;',
                    'plugin' => $pluginName,
                    'model' => '',
                ];
            }

            foreach ($plugin as $modelName => $model) {
                ++$this->count;
                $pluginAndModelNumber = $this->count;
                $pluginAndModelName = $pluginName . '/' . $modelName;

                if(!in_array($pluginAndModelName, $this->handledNames)) {
                    $this->handledNames[] = $pluginAndModelName;

                    $this->structure[$pluginAndModelNumber] = [
                        'label' => $pluginAndModelName,
                        'type' => 'Model',
                        'parent' => $pluginNumber,
                        'style' => 'fill: #afa !important; cursor: pointer;',
                        'plugin' => $pluginName,
                        'model' => $modelName,
                    ];
                }

                foreach ($model as $associationType => $associations) {
                    ++$this->count;
                    $associationTypeNumber = $this->count;
                    foreach ($associations as $key => $association) {
                        ++$this->count;
                        $associationNumber = $this->count;
                        $associationName = $association['target']['alias'] . ' (' . $association['target']['table'] . ')';
                        $source = $association['source']['alias'] . ' (' . $association['source']['table'] . ')';
                        $target = $association['target'];
                        $associationBuildName = $associationName . '-' . $associationType . '-' . $source;

                        if(!in_array($associationBuildName, $this->handledNames)) {
                            $this->handledNames[] = $associationBuildName;

                            $this->structure[$associationNumber] = [
                                'id' => $associationTypeNumber,
                                'label' => $association['target']['location'] . '/' . $associationName,
                                'type' => 'Association',
                                'parent' => $pluginAndModelNumber,
                                'associationType' => $associationType,
                                'style' => 'cursor: pointer;',
                                'lineStyle' => '',
                                'plugin' => $pluginName,
                                'model' => $modelName,
                                'associationTarget' => $association['target']['model'],
                                'associationTargetPlugin' => $association['target']['location']
                            ];
                        }

                        if(!empty($target['childs'])) {
                            $childs[$associationNumber] = $target['childs'];
                        }
                    }
                }
            }
        }
        
        // add childs
        if(!empty($childs) && empty($hideChildren)) {
            foreach($childs as $parentNumber => $child) {
                $this->structure = $this->structure + $this->_parseChilds($child, $parentNumber);
            }
        }

        return $this->structure;
    }

    /**
     * Handle the children of the collections
     * @param  array $childs
     * @param  integer $parentNumber The parent number that the childs will be attached to
     * @return array                Data with children
     */
    protected function _parseChilds($childs, $parentNumber) {
        $structure = [];

        foreach($childs as $typeName => $associations) {
            foreach($associations as $key => $association) {
                $associationName = $association['target']['alias'] . ' (' . $association['target']['table'] . ')';
                $source = $association['source']['alias'] . ' (' . $association['source']['table'] . ')';
                $target = $association['target'];
                $structure[++$this->count] = [
                    'id' => $this->count,
                    'label' => $target['location'] . '/' . $associationName,
                    'type' => 'Association',
                    'parent' => $parentNumber,
                    'associationType' => $typeName,
                    'style' => '',
                    'lineStyle' => '',
                    'plugin' => $association['source']['location'],
                    'model' => $association['source']['model'],
                    'associationTarget' => $association['target']['model'],
                    'associationTargetPlugin' => $association['target']['location']
                ];

                if($this->getConfig('showDeepChildren')) {
                    if(!empty($target['childs'])) {
                        $structure = $structure + $this->_parseChilds($target['childs'], $this->count);
                    }
                }
            }
        }

        return $structure;
    }
}
