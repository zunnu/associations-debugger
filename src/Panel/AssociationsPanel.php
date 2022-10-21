<?php
declare(strict_types=1);

namespace AssociationsDebugger\Panel;
use DebugKit\DebugPanel;
use AssociationsDebugger\Gate;

class AssociationsPanel extends DebugPanel {
	public $plugin = 'AssociationsDebugger';

    /**
     * Get the panel data
     *
     * @return array
     */
    public function data()
    {
    	$gate = new Gate();

        return [
            'associationCollections' => $gate->associations()->array(),
        ];
    }
}