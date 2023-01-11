<?php
declare(strict_types=1);

namespace AssociationsDebugger;

use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;

class Gate
{
    /**
     * List of loaded plugins
     *
     * @var array
     */
    protected $plugins = [];

    /**
     * List of found
     *
     * @var array
     */
    protected $models = [];

    /**
     * Config of the gate
     * Options:
     *   associationTypes: List of associations types to filter by
     *
     * @var array
     */
    protected $config = [];

    /**
     * List of associations
     *
     * @var array
     */
    public $associations = [];

    /**
     * List of available types
     *
     * @var [type]
     */
    public $associationTypes = [
        'oneToMany' => 'hasMany',
        'oneToOne' => 'hasOne',
        'manyToOne' => 'belongsTo',
        'manyToMany' => 'belongsToMany',
    ];

    /**
     * Set the plugins to scope
     * This will also set "App" as a plugin
     */
    public function setPlugins()
    {
        $pluginConfig = $this->getConfig('plugins');
        $plugins = Plugin::loaded();
        $plugins[] = 'App';

        if (in_array('handleAll', $pluginConfig)) {
            $this->plugins = $plugins;
        } else {
            if (empty($pluginConfig)) {
                $this->plugins = [];
            } else {
                foreach ($plugins as $key => $plugin) {
                    if (in_array($plugin, $pluginConfig)) {
                        unset($plugins[$key]);
                    }
                }
            }
        }

        $this->plugins = $plugins;
    }

    /**
     * Return the list of found models in the whole app
     *
     * @return array
     */
    public function getLoadedModels()
    {
        return $this->models;
    }

    /**
     * Main associations fetch method
     *
     * @param  array  $conditions List of filter conditions
     * @return object             Current class object
     */
    public function associations($conditions = [])
    {
        $this->setConfig($conditions);
        $this->setPlugins();

        $this->associations = $this->getAssociations();

        return $this;
    }

    /**
     * Return the associations data as json by using chaining
     *
     * @return string
     */
    public function json()
    {
        return json_encode($this->associations);
    }

    /**
     * Return the associations data as array by using chaining
     *
     * @return array
     */
    public function array()
    {
        return $this->associations;
    }

    /**
     * Get the list of assocations
     *
     * @return array List of associations
     */
    private function getAssociations()
    {
        $associationsArray = [];
        $modelList = [];
        $plugins = $this->getConfig('plugins');

        foreach ($this->getModels() as $pluginName => $models) {
            if (strtolower($pluginName) == 'debugkit' || !in_array('handleAll', $plugins) && !in_array($pluginName, $plugins)) {
                continue;
            }

            foreach ($models as $key => $model) {
                // clean file name
                $model = str_replace('.php', '', $model);
                $model = str_replace('Table', '', $model);
                if ($model === '') {
                    continue;
                }
                $modelList[$model] = ['plugin' => $pluginName];
                $associationsArray[$pluginName][$model] = $this->_associations($model, $pluginName);
            }
        }

        $this->models = $modelList;
        $associationsArray = $this->_buildChildren($associationsArray, $modelList);

        return $associationsArray;
    }

    /**
     * Worker for getAssociations
     *
     * @param  string $model  Model name
     * @param  string $plugin Set only if model is under plugin
     * @return array         Specific model associations
     */
    private function _associations($model, $plugin = null)
    {
        $associationTypes = $this->getConfig('associationTypes');

        if (empty($plugin) || strtolower($plugin == 'app')) {
            $setModel = TableRegistry::get($model);
        } else {
            $setModel = TableRegistry::get($plugin . '.' . $model);
        }

        $associationsArray = [];
        $activePluginsLower = array_map('strtolower', $this->getPlugins());

        if ($associations = $setModel->associations()) {
            foreach ($associations->normalizeKeys($associations->getIterator()) as $key => $association) {
                $source = $association->getSource();
                $sourceRegistery = 'App';
                $targetRegistery = 'App';

                // error handling for association registeration
                try {
                    $target = $association->getTarget();
                } catch (\Exception $e) {
                    $association->getTarget()->setRegistryAlias(ucfirst($association->getProperty()) . $association->getName());
                    $association->getTarget()->setAlias(ucfirst($association->getProperty()) . $association->getName());
                    $association->setName(ucfirst($association->getProperty()) . $association->getName());
                    $target = $association->getTarget();
                }

                if (strpos($source->getRegistryAlias(), '.') !== false) {
                    $sourceRegistery = strtok($source->getRegistryAlias(), '.');

                    if ($sourceRegistery !== 'App' && !in_array(strtolower($sourceRegistery), $activePluginsLower)) {
                        $sourceRegistery = 'App';
                    }
                }

                if (strpos($source->getRegistryAlias(), '.') !== false) {
                    $targetRegistery = strtok($target->getRegistryAlias(), '.');

                    if ($targetRegistery !== 'App' && !in_array(strtolower($targetRegistery), $activePluginsLower)) {
                        $targetRegistery = 'App';
                    }
                }

                if (!empty($associationTypes) && !in_array($association->type(), $associationTypes)) {
                    continue;
                }

                $type = $association->type();

                if (!empty($this->associationTypes[$type])) {
                    $type .= ' (' . $this->associationTypes[$type] . ')';
                }

                $associationsArray[$type][] = [
                    'source' => [
                        'table' => $source->getTable(),
                        'alias' => $source->getAlias(),
                        'connectionName' => $source->getConnection()->configName(),
                        'location' => $sourceRegistery,
                        'model' => $model,
                    ],
                    'target' => [
                        'table' => $target->getTable(),
                        'alias' => $target->getAlias(),
                        'connectionName' => $target->getConnection()->configName(),
                        'location' => $targetRegistery,
                        'model' => $this->convertTableName($target->getRegistryAlias()),
                        // 'model' => $this->convertTableName($target->entityClass()),
                    ],
                ];
            }

            return $associationsArray;
            // return $associations->normalizeKeys($associations->getIterator());
        }

        return false;
    }

    /**
     * Get the path to the model directory
     *
     * @param  string $plugin Plugin name, if not a plugin the plugin name will be App
     * @return string         Path to model dir
     */
    private function getPath($plugin)
    {
        if (!$plugin || $plugin == 'App') {
            $path = App::classPath('Model/Table');
        } else {
            $path = App::classPath('Model/Table', $plugin);
        }

        return $path;
    }

    /**
     * Get the list of the models of the whole app
     *
     * @return array
     */
    private function getModels()
    {
        // get app models
        $holder['App'] = $this->_models();
        if (!empty($this->plugins)) {
            foreach ($this->plugins as $key => $plugin) {
                $holder[$plugin] = $this->_models($plugin);
            }
        }

        return $holder;
    }

    /**
     * Worker for getModels
     *
     * @param  string $plugin Plugin name where to search the models from
     * @return array          List of tables
     */
    private function _models($plugin = null)
    {
        // find the models
        $path = $this->getPath($plugin);
        $dir = new Folder($path[0]);
        $models = $dir->find('.*Table\.php');

        return $models;
    }

    /**
     * Set config for the gate
     *
     * @param array $conditions
     */
    public function setConfig($conditions)
    {
        if (!empty($conditions['associationTypes'])) {
            $this->config['associationTypes'] = $conditions['associationTypes'];
        } else {
            $this->config['associationTypes'] = [];
        }

        if (isset($conditions['plugins'])) {
            if (!empty($conditions['plugins'])) {
                $this->config['plugins'] = $conditions['plugins'];
            } else {
                $this->config['plugins'] = [];
            }
        } else {
            $this->config['plugins'] = ['handleAll' => 'handleAll'];
        }
    }

    /**
     * Get the gate config
     *
     * @param  string $arrayName Key of the config
     * @return array
     */
    public function getConfig($arrayName = null)
    {
        if (empty($arrayName)) {
            return $this->config;
        } else {
            return $this->config[$arrayName];
        }
    }

    /**
     * Get the list of loaded plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        $plugins = Plugin::loaded();
        $loadedPlugins = [];

        foreach ($plugins as $key => $plugin) {
            $loadedPlugins[$plugin] = $plugin;
        }

        return $loadedPlugins;
    }

    /**
     * Worker for getAssociations
     * adds the children to association list
     *
     * @param  array $plugins List of associations inside "plugins"
     * @param  array $models  List of models
     * @return array          Associations with children
     */
    private function _buildChildren($plugins, $models)
    {
        foreach ($plugins as $pluginName => $plugin) {
            foreach ($plugin as $modelName => $model) {
                foreach ($model as $associationType => $type) {
                    foreach ($type as $key => $association) {
                        if (!empty($association['target']) && !empty($models[$association['target']['model']])) {
                            $childTypes = $plugins[$models[$association['target']['model']]['plugin']][$association['target']['model']];

                            if (!empty($childTypes)) {
                                // if source is the same as the parent model do not show
                                foreach ($childTypes as $childTypeName => $childType) {
                                    foreach ($childType as $chK => $ch) {
                                        if ($ch['target']['model'] == $association['source']['model']) {
                                            unset($childTypes[$childTypeName][$chK]);
                                            continue;
                                        }
                                    }
                                }

                                $plugins[$pluginName][$modelName][$associationType][$key]['target']['childs'] = $childTypes;
                            }
                        }
                    }
                }
            }
        }

        return $plugins;
    }

    /**
     * Format the table name
     *
     * @param  string $modelName Name of the model
     * @return string              Formated name
     */
    private function convertTableName($modelName)
    {
        if (strpos($modelName, '.') !== false) {
            $modelName = substr($modelName, strrpos($modelName, '.') + 1);
        }

        if (substr($modelName, -1) !== 's') {
            $modelName = $modelName . 's';
        }

        return $modelName;
    }
}
