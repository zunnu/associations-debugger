<script type="text/javascript">
    // init dagre
    var g = new dagreD3.graphlib.Graph()
      .setGraph({rankdir: 'LR'})
      .setDefaultEdgeLabel(function() { return {}; });
</script>

<?php
if (!isset($showDeepChildren)) {
    $showDeepChildren = true;
}
    $this->StructureBuilder = $this->loadHelper('AssociationsDebugger.StructureBuilder', ['showDeepChildren' => $showDeepChildren]);
    $structure = $this->StructureBuilder->build($associationCollections);
?>

<script type="text/javascript">
    if (typeof structure === 'undefined' || structure === null) {
        var structure = <?= json_encode($structure) ?>;
    } else {
        structure = <?= json_encode($structure) ?>;
    }

    function getNodeInfo(id) {
        for (var key of Object.keys(structure)) {
            if(key == id) {
                return structure[key]
            }
        }
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

    function nodeDetailsRequest(plugin, currentModel, targetModel, targetPlugin) {
        var csrfToken = <?= json_encode($this->request->getAttribute('csrfToken')) ?>;

        return $.ajax({
            type: "POST",
            url: '<?= $this->Url->build([
                'plugin' => 'AssociationsDebugger',
                'controller' => 'Associations',
                'action' => 'details',
            ]); ?>' + window.location.search,
            data: {plugin: plugin, currentModel: currentModel, targetModel: targetModel, targetPlugin: targetPlugin},
            headers: {
                'X-CSRF-Token': csrfToken
            },
        });
    }

    svg.selectAll("g.node").on("click", function(id) {
        var nodeInfo = getNodeInfo(id);
        var request = nodeDetailsRequest(nodeInfo.plugin, nodeInfo.model, nodeInfo.associationTarget, nodeInfo.associationTargetPlugin);

        request.done(function (data) {
            // clear content from grid and add new content
            $(document).find('#canvas').empty();
            $(document).find('#canvas').html(data);

            // check if under association-debug index and update url
            if (window.location.href.indexOf('/associations-debugger') > -1) {
                var searchParam = {plugin: nodeInfo.plugin, currentModel: nodeInfo.model, targetModel: nodeInfo.associationTarget, targetPlugin: nodeInfo.associationTargetPlugin}
                let buildParam = encodeURIComponent(JSON.stringify(searchParam))
                var url = window.updateQueryStringParameter(window.location.href, 'search', buildParam);
                history.replaceState(null, null, url);

                var plugin = nodeInfo.associationTargetPlugin;
                if(!plugin) {
                    plugin = nodeInfo.plugin;
                }

                var model = nodeInfo.associationTarget;
                if(!model) {
                    model = nodeInfo.model;
                }

                $('#general-search').removeAttr("checked");
                $('#general-search').multiselect('deselectAll');

                if(!plugin) {
                    $('#general-search').multiselect('select', 'Root', true);
                } else {
                    $('#general-search').multiselect('select', plugin + (model ? '-' + model : ''), true);
                }
            }
        })
    });

    //svg.attr("height", g.graph().height + 50);
</script>
