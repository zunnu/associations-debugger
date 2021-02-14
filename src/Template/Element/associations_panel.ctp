<?= $this->Html->script('AssociationsDebugger.../dagre-d3/d3.v5.js') ?>
<?= $this->Html->script('AssociationsDebugger.../dagre-d3/dagre-d3.js') ?>

<style type="text/css">
	.clusters rect {
	  fill: #00ffd0;
	  stroke: #999;
	  stroke-width: 1.5px;
	}

	.panel-content {
		height: 100vh;
	}

	text {
	  font-weight: 300;
	  font-family: "Helvetica Neue", Helvetica, Arial, sans-serf;
	  font-size: 14px;
	}

	.node rect {
	  stroke: #999;
	  fill: #fff;
	  stroke-width: 1.5px;
	}

	.edgePath path {
	  stroke: #333;
	  stroke-width: 1.5px;
	}
</style>

<!-- draw area -->
<svg id="canvas" width="100%" height="100%"></svg>

<?= $this->element('AssociationsDebugger.associationTree', ['associationCollections' => $associationCollections]) ?>