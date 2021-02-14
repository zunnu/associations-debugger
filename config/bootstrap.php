<?php
use Cake\Core\Configure;

if (Configure::read('debug')) {
    Configure::write('DebugKit.panels', ['AssociationsDebugger.Associations']);
}