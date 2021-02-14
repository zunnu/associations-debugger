<script type="text/javascript">
	// init dagre
	var g = new dagreD3.graphlib.Graph()
	  .setGraph({rankdir: 'LR'})
	  .setDefaultEdgeLabel(function() { return {}; });
</script>

<?php
	// build structure array
	$structure = [];

	foreach ($associationCollections as $pluginName => $plugin) {
		if(!in_array($pluginName, $structure)) {
			$structure[$pluginName] = [
				'label' => $pluginName,
				'type' => 'Plugin',
				'parent' => null,
				'style' => 'fill: lightblue !important;',
			];
		}
		foreach ($plugin as $modelName => $model) {
			$pluginAndModelName = $pluginName . '/' . $modelName;

			if(!in_array($pluginAndModelName, $structure)) {
				$structure[$pluginAndModelName] = [
					'label' => $pluginAndModelName,
					'type' => 'Model',
					'parent' => $pluginName,
					'style' => 'fill: #afa !important;',
				];
			}
			foreach ($model as $associationType => $associations) {
				// if(!in_array($associationType, $structure)) {
				// 	$structure[$associationType] = [
				// 		'label' => $associationType,
				// 		'type' => 'AssociationType',
				// 		'parent' => $pluginAndModelName,
				// 		'style' => '',
				// 	];
				// }

				foreach ($associations as $key => $association) {
					$associationName = $association['target']['alias'] . ' (' . $association['target']['table'] . ')';
					$source = $association['source']['alias'] . ' (' . $association['source']['table'] . ')';

					if(!in_array($associationName . '-' . $associationType . '-' . $source, $structure)) {
						$structure[$associationName . '-' . $associationType . '-' . $source] = [
							'label' => $association['target']['location'] . '/' . $associationName,
							'type' => 'Association',
							'parent' => $pluginAndModelName,
							'associationType' => $associationType,
							'style' => '',
							'lineStyle' => ''
						];
					}
				}
			}
		}
	}
	// dd($structure);
?>

<script type="text/javascript">
	if (typeof structure === 'undefined' || structure === null) {
		var structure = <?= json_encode($structure) ?>;
	} else {
		structure = <?= json_encode($structure) ?>;
	}

	// create nodes
	for (var key of Object.keys(structure)) {
		g.setNode(key,  {label: structure[key].label, style: structure[key].style});
	}

	// connect nodes
	for (var key of Object.keys(structure)) {
		if(structure[key].parent) {
			if(structure[key].associationType) {
				g.setEdge(structure[key].parent, key, {label: structure[key].associationType, style: structure[key].lineStyle});
			} else {
				g.setEdge(structure[key].parent, key);
			}
		}
	}

	// Create the renderer
	var render = new dagreD3.render();

	// Set up an SVG group so that we can translate the final graph.
	var svg = d3.select("svg"),
	    svgGroup = svg.append("g"),
	    zoom = d3.zoom().on("zoom", function() {
	      svgGroup.attr("transform", d3.event.transform);
	    });
	svg.call(zoom);

	// Run the renderer. This is what draws the final graph.
	render(svgGroup, g);

	//svg.attr("height", g.graph().height + 50);
</script>