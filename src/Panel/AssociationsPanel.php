<?php
declare(strict_types=1);

namespace AssociationsDebugger\Panel;

use AssociationsDebugger\Gate;
use DebugKit\DebugPanel;

class AssociationsPanel extends DebugPanel
{
    public string $plugin = 'AssociationsDebugger';

    /**
     * Get the panel data
     *
     * @return array
     */
    public function data(): array
    {
        $gate = new Gate();

        return [
            'associationCollections' => $gate->associations()->array(),
        ];
    }
}
