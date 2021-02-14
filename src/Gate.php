<?php
namespace AssociationsDebugger;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Filesystem\Folder;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Cache\Cache;

class Gate {
    // prefixes
    protected $prefixes = [];

    // routes
    protected $routes = [];

    // plugins
    protected $plugins = [];

    protected $config = [];

    public $associations = [];

    public $associationTypes = [
        'oneToMany' => 'hasMany',
        'oneToOne' => 'hasOne',
        'manyToOne' => 'belongsTo',
        'manyToMany' => 'belongsToMany',
    ];

    public function __construct() {
        $this->setRoutes();
    }

    public function setPlugins() {
        $pluginConfig = $this->getConfig('plugins');
        $plugins = Plugin::loaded();
        $plugins[] = 'App';

        if(in_array('handleAll', $pluginConfig)) {
            $this->plugins = $plugins;
        } else {
            if(empty($pluginConfig)) {
                $this->plugins = [];
            } else {
                foreach ($plugins as $key => $plugin) {
                    if(in_array($plugin, $pluginConfig)) {
                        unset($plugins[$key]);
                    }
                }
            }
        }

        $this->plugins = $plugins;
    }

    // get routes
    public function setRoutes() {
        $this->routes = Router::routes();
    }

    public function associations($conditions = []) {
        $this->setConfig($conditions);
        $this->setPlugins();

        $this->associations = $this->getAssociations();
        return $this;
    }

    // return data as json
    public function json() {
        return json_encode($this->associations);
    }

    // return data as array
    public function array() {
        return $this->associations;
    }

    // private function buildAssociations() {
    //     $associationsArray = [];
    //     dd($this->getAssociations());

    //     foreach ($this->getAssociations() as $pluginName => $associations) {
    //         foreach ($associations as $key => $association) {
    //             dd($associations->get('Users'));

    //         }
    //     }
    // }

    // get associations
    private function getAssociations() {
        $associationsArray = [];
        $plugins = $this->getConfig('plugins');

        foreach ($this->getModels() as $pluginName => $models) {
            if(strtolower($pluginName) == 'debugkit' || !in_array('handleAll', $plugins) && !in_array($pluginName, $plugins)) {
                continue;
            }

            foreach ($models as $key => $model) {
                // clean file name
                $model = str_replace('.php', '', $model);
                $model = str_replace('Table', '', $model);
                $associationsArray[$pluginName][$model] = $this->_associations($model, $pluginName);
            }
        }

        return $associationsArray;
    }

    // return list of associations
    private function _associations($model, $plugin = null) {
        $associationTypes = $this->getConfig('associationTypes');

        if(empty($plugin) || strtolower($plugin == 'app')) {
            $setModel = TableRegistry::get($model);
        } else {
            $setModel = TableRegistry::get($plugin . '.' . $model);
        }

        $associationsArray = [];
        $activePluginsLower = array_map('strtolower', $this->getPlugins());

        if($associations = $setModel->associations()) {
            foreach ($associations->normalizeKeys($associations->getIterator()) as $key => $association) {
                $source = $association->source();
                $target = $association->target();
                $sourceRegistery = 'App';
                $targetRegistery = 'App';

                if(strpos($source->registryAlias(), '.') !== false) {
                    $sourceRegistery = strtok($source->registryAlias(), '.');

                    if($sourceRegistery !== 'App' && !in_array(strtolower($sourceRegistery), $activePluginsLower)) {
                        $sourceRegistery = 'App';
                    }
                }

                if(strpos($source->registryAlias(), '.') !== false) {
                    $targetRegistery = strtok($target->registryAlias(), '.');

                    if($targetRegistery !== 'App' && !in_array(strtolower($targetRegistery), $activePluginsLower)) {
                        $targetRegistery = 'App';
                    }
                }
                
                if(!empty($associationTypes) && !in_array($association->type(), $associationTypes)) {
                    continue;
                }

                $type = $association->type();

                if(!empty($this->associationTypes[$type])) {
                    $type .= ' (' . $this->associationTypes[$type] . ')';
                }

                $associationsArray[$type][] = [
                    'source' => [
                        'table' => $source->table(),
                        'alias' => $source->alias(),
                        'connectionName' => $source->connection()->configName(),
                        'location' => $sourceRegistery,
                    ],
                    'target' => [
                        'table' => $target->table(),
                        'alias' => $target->alias(),
                        'connectionName' => $target->connection()->configName(),
                        'location' => $targetRegistery,
                    ]
                ];
            }

            return $associationsArray;
            // return $associations->normalizeKeys($associations->getIterator());
        }

        return false;
    }
 
    // return the path to the model dir
    private function getPath($plugin) {
        if (!$plugin || $plugin == 'App') {
            $path = App::path('Model/Table');
        } else {
            $path = App::path('Model/Table', $plugin);
        }

        return $path;
    }

    private function getModels() {
        // get app models
        $holder['App'] = $this->_models();
        if(!empty($this->plugins)) {
            foreach ($this->plugins as $key => $plugin) {
                $holder[$plugin] = $this->_models($plugin);
            }
        }

        return $holder;
    }

    private function _models($plugin = null) {
        // find the models
        $path = $this->getPath($plugin);
        $dir = new Folder($path[0]);
        $models = $dir->find('.*Table\.php');
        return $models;
    }

    private function setConfig($conditions) {
        if(!empty($conditions['associationTypes'])) {
            $this->config['associationTypes'] = $conditions['associationTypes'];
        } else {
            $this->config['associationTypes'] = [];
        }

        if(isset($conditions['plugins'])) {
            if(!empty($conditions['plugins'])) {
                $this->config['plugins'] = $conditions['plugins'];
            } else {
                $this->config['plugins'] = [];
            }
        } else {
            $this->config['plugins'] = ['handleAll' => 'handleAll'];
        }
    }

    private function getConfig($arrayName = null) {
        if(empty($arrayName)) {
            return $this->config;
        } else {
            return $this->config[$arrayName];
        }
    }

    public function getAssociationTypes() {
        return $this->associationTypes;
    }

    public function getPlugins() {
        $plugins = Plugin::loaded();
        $loadedPlugins = [];

        foreach ($plugins as $key => $plugin) {
            $loadedPlugins[$plugin] = $plugin;
        }

        return $loadedPlugins;
    }
}