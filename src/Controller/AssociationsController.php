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
        $showDeepChildren = false;
        $search = [];
        $selectedTypes = [];

        // SEARCH
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
        $associationsCollection = $this->_parseSearch($associations, $data);

        $this->set('associationCollections', $associationsCollection);
        $this->set('associationTypes', $this->Gate->getAssociationTypes());
        $this->set('activePlugins', $this->Gate->getPlugins());
        $this->set('showDeepChildren', $showDeepChildren);
        $this->set('selectedTypes', $selectedTypes);

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
        $showDeepChildren = false;

        if($this->request->is('ajax')) {
            $data = $this->request->getData();

            if(!empty($this->request->getQueryParams())) {
                $conditions = $this->_parseConditions($this->request->getQueryParams());
            }
        } elseif($this->request->is('get') && !empty($this->request->getQueryParams())) {
            $data = $this->request->getQueryParams();
        }

        if(!empty($data)) {
            $associations = $this->Gate->associations($conditions)->array();
            $associationsCollection = $this->_parseSearch($associations, $data);
        }

        $this->set('associationCollections', $associationsCollection);
        $this->set('associationTypes', $this->Gate->getAssociationTypes());
        $this->set('activePlugins', $this->Gate->getPlugins());
        $this->set('showDeepChildren', $showDeepChildren);

        // if($this->request->is('ajax')) {
            $this->render('Element/associationTree');
        // }
    }

    private function _parseSearch($associations, $data) {
        if(!empty($data['targetPlugin']) && !empty($data['targetModel'])) {
            if(!empty($associations[$data['targetPlugin']][$data['targetModel']])) {
                $associationsCollection = [
                    $data['targetPlugin'] => [
                        $data['targetModel'] => $associations[$data['targetPlugin']][$data['targetModel']]
                    ]
                ];

                $showDeepChildren = true;
            }
        } elseif(!empty($data['plugin']) && !empty($data['currentModel'])) {
            if(!empty($associations[$data['plugin']][$data['currentModel']])) {
                $associationsCollection = [
                    $data['plugin'] => [
                        $data['currentModel'] => $associations[$data['plugin']][$data['currentModel']]
                    ]
                ];

                $showDeepChildren = true;
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
