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

    public function index() {
        $this->viewBuilder()->setLayout(false);
        $conditions = [];

        // SEARCH
        if ($this->request->is('get')) {
            if(!empty($this->request->getQueryParams())) {
                $data = $this->request->getQueryParams();

                if(!empty($data['plugins']) && $data['plugins'] !== 'undefined') {
                    $plugins = explode(',', $data['plugins']);
                    $conditions['plugins'] = $plugins;
                }
                
                if(!empty($data['associationTypes']) && $data['associationTypes'] !== 'undefined') {
                    $associationTypes = explode(',', $data['associationTypes']);
                    $conditions['associationTypes'] = $associationTypes;
                }
            }
        }

        $this->set('associationCollections', $this->Gate->associations($conditions)->array());
        $this->set('associationTypes', $this->Gate->getAssociationTypes());
        $this->set('activePlugins', $this->Gate->getPlugins());

        if($this->request->is('ajax')) {
            $this->render('Element/associationTree');
        }
    }
}
