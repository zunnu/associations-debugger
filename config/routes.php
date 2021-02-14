<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin(
    'AssociationsDebugger',
    ['path' => '/associations-debugger'],
    function (RouteBuilder $routes) {
    	$routes->connect('/', ['controller' => 'Associations', 'action' => 'index']);
    	$routes->connect('/:action/*', ['controller' => 'Associations']);
	    // $routes->scope('/', function ($routes) {
	    //     $routes->connect('/:action/*', ['controller' => 'Associations']);
	    // });

        $routes->fallbacks(DashedRoute::class);
    }
);
