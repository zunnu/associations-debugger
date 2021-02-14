<?= $this->Html->css('AssociationsDebugger.../treant-js/Treant.css') ?>
<?= $this->Html->script('AssociationsDebugger.../treant-js/vendor/raphael.js') ?>
<?= $this->Html->script('AssociationsDebugger.../treant-js/Treant.js') ?>

<style type="text/css">
	body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,form,fieldset,input,textarea,p,blockquote,th,td { margin:0; padding:0; }
	table { border-collapse:collapse; border-spacing:0; }
	fieldset,img { border:0; }
	address,caption,cite,code,dfn,em,strong,th,var { font-style:normal; font-weight:normal; }
	caption,th { text-align:left; }
	h1,h2,h3,h4,h5,h6 { font-size:100%; font-weight:normal; }
	q:before,q:after { content:''; }
	abbr,acronym { border:0; }

	body { background: #fff; }
	/* optional Container STYLES */
	.chart { height: 600px; margin: 5px; width: 900px; }
	.Treant > .node {  }
	.Treant > p { font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; font-weight: bold; font-size: 12px; }
	.node-name { font-weight: bold;}

	.nodeExample1 {
	    padding: 2px;
	    -webkit-border-radius: 3px;
	    -moz-border-radius: 3px;
	    border-radius: 3px;
	    background-color: #ffffff;
	    border: 1px solid #000;
	    width: 200px;
	    font-family: Tahoma;
	    font-size: 12px;
	}

	.nodeExample1 img {
	    margin-right:  10px;
	}
</style>

<div id="tree">
<script type="text/javascript">
	let hierarchy = [];
	let tmpDetails = [];
</script>

<?php foreach ($associationCollections as $pluginName => $plugin) : ?>
	<script type="text/javascript">
		let <?= $pluginName ?> = {
	        text: {
	            name: "<?= $pluginName ?>",
	        },
		}

		hierarchy.push(<?= $pluginName ?>);
	</script>

	<?php foreach ($plugin as $modelName => $model) : ?>
		<script type="text/javascript">
			let <?= $modelName ?> = {
		        parent: <?= $pluginName ?>,
		        text: {
		            name: "<?= $modelName ?>",
		        },
		        stackChildren: true,
			}

			hierarchy.push(<?= $pluginName ?>);
		</script>

		<?php foreach ($model as $associationType => $associations) : ?>
			<?php foreach ($associations as $key => $association) : ?>
				<script type="text/javascript">
					tmpDetails = {
				        parent: <?= $modelName ?>,
				        text: {
				            name: "<?= $association['target']['table'] ?>",
				        },
				        stackChildren: true,
					}

					hierarchy.push(tmpDetails);
				</script>
			<?php endforeach ?>
		<?php endforeach ?>
	<?php endforeach ?>
<?php endforeach ?>

<script type="text/javascript">
 	let details = [];
	let config = {
        container: "#tree",
        
        connectors: {
            type: 'step'
        },
        node: {
            HTMLclass: 'nodeExample1'
        }
    };

    chart_config = [
    	config,
    	hierarchy,
    ];
    console.log(chart_config)
	// for (let pluginName in associationCollections) {
	// 	for (let modelName in associationCollections[pluginName]) {
	// 		for (let associationType in associationCollections[pluginName][modelName]) {
	// 			for (let associations in associationCollections[pluginName][modelName][associationType]) {
	// 				for (let association in associationCollections[pluginName][modelName][associationType][associations]) {
	// 					details = associationCollections[pluginName][modelName][associationType][associations][association]

	// 				    let ceo = {
	// 				        text: {
	// 				            name: "Mark Hill",
	// 				            title: "Chief executive officer",
	// 				            contact: "Tel: 01 213 123 134",
	// 				        },
	// 				        image: "../headshots/2.jpg"
	// 				    };

	// 				}
	// 			}
	// 		}
	//     	// console.log(associationCollections[pluginName][modelName]);
	//   	}
	// }

	// get the values and iterate over them
	// Object.values(associationCollections).forEach((pluginName) => { 
	//   Object.keys(associationCollections.pluginName).forEach((val, index) => {
	//   	console.log(val)
	//     // rows[index] = rows[index] || [];
	//     // rows[index].push(val); 
	//   });
	// });

 	// Object.keys(associationCollections).forEach(function(plugin) {
	 // 	plugin.forEach(function(modelName, model) {
	 // 		console.log('modelName ' + modelName)
	 // 		console.log('model ' + model)
	 // 	})
 	// })


    ceo = {
        text: {
            name: "Mark Hill",
            title: "Chief executive officer",
            contact: "Tel: 01 213 123 134",
        },
        image: "../headshots/2.jpg"
    };

    cto = {
        parent: ceo,
        text:{
            name: "Joe Linux",
            title: "Chief Technology Officer",
        },
        stackChildren: true,
        image: "../headshots/1.jpg"
    };
    console.log(cto);
    // cbo = {
    //     parent: ceo,
    //     stackChildren: true,
    //     text:{
    //         name: "Linda May",
    //         title: "Chief Business Officer",
    //     },
    //     image: "../headshots/5.jpg"
    // },
    // cdo = {
    //     parent: ceo,
    //     text:{
    //         name: "John Green",
    //         title: "Chief accounting officer",
    //         contact: "Tel: 01 213 123 134",
    //     },
    //     image: "../headshots/6.jpg"
    // },
    // cio = {
    //     parent: cto,
    //     text:{
    //         name: "Ron Blomquist",
    //         title: "Chief Information Security Officer"
    //     },
    //     image: "../headshots/8.jpg"
    // },
    // ciso = {
    //     parent: cto,
    //     text:{
    //         name: "Michael Rubin",
    //         title: "Chief Innovation Officer",
    //         contact: {val: "we@aregreat.com", href: "mailto:we@aregreat.com"}
    //     },
    //     image: "../headshots/9.jpg"
    // },
    // cio2 = {
    //     parent: cdo,
    //     text:{
    //         name: "Erica Reel",
    //         title: "Chief Customer Officer"
    //     },
    //     link: {
    //         href: "http://www.google.com"
    //     },
    //     image: "../headshots/10.jpg"
    // },
    // ciso2 = {
    //     parent: cbo,
    //     text:{
    //         name: "Alice Lopez",
    //         title: "Chief Communications Officer"
    //     },
    //     image: "../headshots/7.jpg"
    // },
    // ciso3 = {
    //     parent: cbo,
    //     text:{
    //         name: "Mary Johnson",
    //         title: "Chief Brand Officer"
    //     },
    //     image: "../headshots/4.jpg"
    // },
    // ciso4 = {
    //     parent: cbo,
    //     text:{
    //         name: "Kirk Douglas",
    //         title: "Chief Business Development Officer"
    //     },
    //     image: "../headshots/11.jpg"
    // }

    // chart_config = [
    //     config,
    //     ceo,
    //     cto,
    //     cbo,
    //     cdo,
    //     cio,
    //     ciso,
    //     cio2,
    //     ciso2,
    //     ciso3,
    //     ciso4
    // ];

	// var chart_config = {
	//     chart: {
	//         container: "#tree",
	// 		connectors: {
	// 			type: 'step'
	// 		},
	// 		node: {
	// 			HTMLclass: 'nodeExample1'
	// 		}
	//         // callback : {
	//         //     onTreeLoaded: function () {

	//         //         var $oNodes = $( '.Treant .node' );

	//         //         $oNodes.on('click', function (oEvent) {

	//         //                     var $oNode = $(this);
	//         //                     var oMeta = $oNode.data('treenode');

	//         //                     // GET ORIGINAL VALUE OF AN ATTRIBUTE
	//         //                     console.log(oMeta.text.customAttributeType );

	//         //                     // SET NEW VALUE TO AN ATTRIBUTE
	//         //                     oMeta.text.customAttributeType = 'The new type value';

	//         //                     // AFTER THIS THE NEW VALUE IS IN DOM
	//         //                     console.log(oMeta.text.customAttributeType );

	//         //             }
	//         //         );

	//         //     }
	//         // }
	//     },                
	//     nodeStructure: {
	//         innerHTML : '<p>The main node</p>',
	//         children: hierarchy
	//     }
	// };

	new Treant(chart_config);
</script>