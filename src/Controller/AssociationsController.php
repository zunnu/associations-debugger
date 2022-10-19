<?php
namespace AssociationsDebugger\Controller;

use AssociationsDebugger\Controller\AppController;
use Cake\Log\Log;
use AssociationsDebugger\Gate;

/**
 * EnforcerGroupPermissions Controller
 *
 * @property \Enforcer\Model\Table\EnforcerGroupPermissionsTable $EnforcerGroupPermissions
 *
 * @method \Enforcer\Model\Entity\EnforcerGroupPermission[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AssociationsController extends AppController
{
    /**
     * initialize method
     *
     * @return void
     */
    public function initialize() {
        parent::initialize();
        $this->Gate = new Gate();
    }

    /**
     * Index method
     */
    public function index() {
        $this->viewBuilder()->setLayout(false);
        $conditions = [];
        $showDeepChildren = true;
        $search = [];
        $selectedTypes = [];

        // search
        if ($this->request->is('get')) {
            if(!empty($this->request->getQueryParams())) {
                $data = $this->request->getQueryParams();
                $search = !empty($data['search']) ? json_decode($data['search'], true) : [];
                if(!empty($data['deepChildren'])) $showDeepChildren = filter_var($data['deepChildren'], FILTER_VALIDATE_BOOLEAN);
                $conditions = $this->_parseConditions($data);

                if(!empty($data['associationTypes'])) $selectedTypes = explode(',', $data['associationTypes']);
                if(!empty($search)) $data = $data + $search;
            }
        }

        $associations = $this->Gate->associations($conditions)->array();
        $associationsCollection = $this->_parseSearch($associations, (!empty($data) ? $data : []));

        $this->set('associationCollections', $associationsCollection);
        $this->set('associationTypes', $this->Gate->associationTypes);
        $this->set('activePlugins', $this->Gate->getPlugins());

        $selectedNode = 'Root';
        if(!empty($data['targetPlugin'])) {
            if(!empty($data['targetModel'])) {
                $selectedNode = $data['targetPlugin'] . '-' . $data['targetModel'];
            } else {
                $selectedNode = $data['targetPlugin'];
            }
        }

        $assocationSearchSelect = ['Root' => 'Root'];
        foreach($associations as $plugin => $assocation) {
            $keys = array_keys($assocation);
            $first = true;

            foreach($keys as $k) {
                if($first) {
                    $first = false;
                    $assocationSearchSelect[$plugin][$plugin] = $plugin . ' (plugin)';
                }

                $assocationSearchSelect[$plugin][$plugin . '-' . $k] = $k;
            }
        }

        $this->set(compact('assocationSearchSelect', 'showDeepChildren', 'selectedTypes', 'selectedNode'));

        if($this->request->is('ajax')) {
            $this->render('Element/associationTree');
        }
    }

    /**
     * Details methdod
     * Will return more details about the selected association tree
     */
    public function details() {
        $this->viewBuilder()->setLayout(false);
        $data = [];
        $associationsCollection = [];
        $conditions = [];
        $showDeepChildren = true;

        if($this->request->is('ajax')) {
            $data = $this->request->getData();
            $query = $this->request->getQueryParams();
            $data = $data + $query;

            if(!empty($this->request->getQueryParams())) {
                $conditions = $this->_parseConditions($this->request->getQueryParams());
            }
        } elseif($this->request->is('get') && !empty($this->request->getQueryParams())) {
            $data = $this->request->getQueryParams();
        }

        if(!empty($data)) {
            $associations = $this->Gate->associations($conditions)->array();
            $associationsCollection = $this->_parseSearch($associations, (!empty($data) ? $data : []));
        }

        if(!empty($data['deepChildren'])) $showDeepChildren = filter_var($data['deepChildren'], FILTER_VALIDATE_BOOLEAN);

        $this->set('associationCollections', $associationsCollection);
        $this->set('associationTypes', $this->Gate->associationTypes);
        $this->set('activePlugins', $this->Gate->getPlugins());
        $this->set('showDeepChildren', $showDeepChildren);

        // if($this->request->is('ajax')) {
            $this->render('Element/associationTree');
        // }
    }

    /**
     * Filter associations with target model, target plugin etc
     * @param  array $associations List of associations
     * @param  array $data         Data from the ui, including target model, target plugin
     * @return array               Filtered data
     */
    private function _parseSearch($associations, $data) {
        $associationsCollection = [];

        if(!empty($data['targetPlugin']) && !empty($data['targetModel'])) {
            if(!empty($associations[$data['targetPlugin']][$data['targetModel']])) {
                $associationsCollection = [
                    $data['targetPlugin'] => [
                        $data['targetModel'] => $associations[$data['targetPlugin']][$data['targetModel']]
                    ]
                ];
            }
        } elseif(!empty($data['targetPlugin']) && empty($data['targetModel'])) {
            if(!empty($associations[$data['targetPlugin']])) {
                $associationsCollection = [
                    $data['targetPlugin'] => $associations[$data['targetPlugin']]
                ];
            }
        } elseif(!empty($data['plugin']) && !empty($data['currentModel'])) {
            if(!empty($associations[$data['plugin']][$data['currentModel']])) {
                $associationsCollection = [
                    $data['plugin'] => [
                        $data['currentModel'] => $associations[$data['plugin']][$data['currentModel']]
                    ]
                ];
            }
        } elseif(!empty($data['plugin']) && empty($data['currentModel'])) {
            if(!empty($associations[$data['plugin']])) {
                $associationsCollection = [
                    $data['plugin'] => $associations[$data['plugin']]
                ];
            }
        } else {
            $associationsCollection = $associations;
        }

        return $associationsCollection;
    }

    /**
     * Format conditions
     * @param  array $data Ui data to be formated
     * @return array       Formated version of conditions
     */
    private function _parseConditions($data) {
        $conditions = [];

        if(!empty($data['plugins']) && $data['plugins'] !== 'undefined') {
            $plugins = explode(',', $data['plugins']);
            $conditions['plugins'] = $plugins;
        }
        
        if(!empty($data['associationTypes']) && $data['associationTypes'] !== 'undefined') {
            $associationTypes = explode(',', $data['associationTypes']);
            $conditions['associationTypes'] = $associationTypes;
        }

        return $conditions;
    }
}
