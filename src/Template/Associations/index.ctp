<?php
use Cake\View\ViewBuilder;
?>

<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        AssociationsDebugger
    </title>
    <?= $this->Html->meta('icon') ?>

	<?= $this->Html->script('AssociationsDebugger.jquery.min.js') ?>
	<?= $this->Html->script('AssociationsDebugger.popper.min.js') ?>
	<?= $this->Html->css('AssociationsDebugger.bootstrap.min.css') ?>
	<?= $this->Html->script('AssociationsDebugger.bootstrap.min.js') ?>
	<?= $this->Html->css('AssociationsDebugger.bootstrap-multiselect.css') ?>
	<?= $this->Html->script('AssociationsDebugger.bootstrap-multiselect.js') ?>

	<?= $this->Html->script('AssociationsDebugger.../dagre-d3/d3.v5.js') ?>
	<?= $this->Html->script('AssociationsDebugger.../dagre-d3/dagre-d3.js') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>

<style type="text/css">
	.clusters rect {
	  fill: #00ffd0;
	  stroke: #999;
	  stroke-width: 1.5px;
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

<body>
	<div class="container mb-4">
		<div class="row">
		    <?= $this->Form->control('associationTypes', ['label' => 'Association types', 'required' => false, 'options' => $associationTypes, 'multiple' => true, 'id' => 'associationTypes', 'class' => 'form-control', 'templates' => [
		        'inputContainer' => '<div class="form-group col-md-6 mt-4">{{content}}</div>'
		    ]]); ?>

<!-- 		    <?= $this->Form->control('plugins', ['label' => 'Plugins', 'required' => false, 'options' => $activePlugins, 'multiple' => true, 'class' => 'form-control', 'id' => 'plugins', 'templates' => [
		        'inputContainer' => '<div class="form-group col-md-6 mt-4">{{content}}</div>'
		    ]]); ?> -->
		</div>
	</div>

	<!-- draw area -->
	<svg id="canvas" width="100%" height="100%"></svg>
</body>

<script type="text/javascript">
    $(function () {
        $("#plugins").multiselect({
            // includeSelectAllOption: true
        });

         $("#associationTypes").multiselect({
            // includeSelectAllOption: true
        });

	    function updateGridRequest(requestUrl) {
	        var csrfToken = <?= json_encode($this->request->getParam('_csrfToken')) ?>;
	        return $.ajax({
	            type: "GET",
	            url: requestUrl,
	            headers: {
	                'X-CSRF-Token': csrfToken
	            },
	        });
	    }

		function updateQueryStringParameter(uri, key, value) {
			var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
			var separator = uri.indexOf('?') !== -1 ? "&" : "?";

			if (uri.match(re)) {
				return uri.replace(re, '$1' + key + "=" + value + '$2');
			} else {
				return uri + separator + key + "=" + value;
			}
		}

		$("#plugins").change(function () { 
			var params = {};
		    var str = "";
		    var url = "";     
            var select = $('#plugins');

            if (select.val() != '') {
                var selected = select.val();

                if (select.attr('multiple')) {
                    selected = selected.join(',');
                }

            }

	        // refresh grid
	        url = updateQueryStringParameter(window.location.href, 'plugins', selected);
	        window.history.pushState("", "", url)
	        var request = updateGridRequest(url);

	        request.done(function (data) {
	            // clear content from grid and add new content
	            $(document).find('#canvas').empty();
	            $(document).find('#canvas').html(data);
	        })
		});


		$("#associationTypes").change(function () { 
			var params = {};
		    var str = "";          
            var select = $('#associationTypes');

            if (select.val() != '') {
                var selected = select.val();

                if (select.attr('multiple')) {
                    selected = selected.join(',');
                }
            }

	        // refresh grid
	        url = updateQueryStringParameter(window.location.href, 'associationTypes', selected);
	        window.history.pushState("", "", url)
	        var request = updateGridRequest(url);

	        request.done(function (data) {
	            // clear content from grid and add new content
	            $(document).find('#canvas').empty();
	            $(document).find('#canvas').html(data);
	        })
		});
    });
</script>

<?= $this->element('associationTree', ['associationCollections' => $associationCollections]) ?>