<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
	<title>Dynatree - Example</title>
	<link href="/resources/css/style.css" rel="stylesheet" type="text/css" /> 
	<script src="/resources/jquery/jquery.js" type="text/javascript"></script>
	<script src="/resources/jquery/jquery-ui.custom.js" type="text/javascript"></script>
	<script src="/resources/jquery/jquery.cookie.js" type="text/javascript"></script>

	<link href="/resources/src/skin/ui.dynatree.css" rel="stylesheet" type="text/css" id="skinSheet">
	<script src="/resources/src/jquery.dynatree.js" type="text/javascript"></script>

<script type="text/javascript">
	$(function(){
		$("#tree").height(screen.height/3);
		$("#btnCollapseAll").click(function(){
		  $("#tree").dynatree("getRoot").visit(function(node){
			node.expand(false);
		  });
		  return false;
		});
		$("#btnExpandAll").click(function(){
		  $("#tree").dynatree("getRoot").visit(function(node){
			node.expand(true);
		  });
		  return false;
		});
		// --- Initialize sample trees
		var treeData ="";
		var omitidos=[];
		omitidos[0]=null;
                //VARIABLES PASADAS COMO PARAMETRO
                var idarbol = <?php echo $idarbol;?>;
                var nivel = <?php echo $nivel;?>;
                var omitidos = <?php echo json_encode($omitidos);?>;
                var seleccionados = parent.document.getElementById("<?php echo $id;?>").value.split(',');
                var seleccionables = <?php echo json_encode($seleccionables);?>;
                console.log(seleccionables);
                console.log(omitidos);
                console.log(seleccionados);
		$.ajax({
			type: "GET",
			data: {
				'idarbol':idarbol,
				'nivel':nivel,
				'seleccionados': seleccionados,
                                'omitidos' : omitidos,
                                'seleccionables' : seleccionables,
				'seleccionable': (jQuery.inArray(nivel,seleccionables)==-1 ? false : true)},
			//(count($omitidos) > 0) ? explode(',',$omitidos) : 'null';
			url: '/<?php echo DIR_SIIGS.'/raiz/getTreeBlock';?>',
			})
			.done(function(dato)
			{
				treeData=jQuery.parseJSON(dato);
				$("#tree").dynatree({
				<?php if($tipo!="none"){?>
				checkbox: true,
				<?php } ?>
				<?php if($tipo=="radio"){?>
				classNames: {checkbox: "dynatree-radio"}, // cambia check por radio
				<?php } ?>
				selectMode: <?php echo $seleccion;?>,// 3 seleccion multiple parcial 2 multiselect 1 select unico
				children: treeData,
				onSelect: function(select, node) 
				{
					// Display list of selected nodes
					var selNodes = node.tree.getSelectedNodes();
					// convert to title/key array
					var selValor = $.map(selNodes, function(node)
					{
						   return "[id=" + node.data.key + ":-Texto=" + node.data.title + "]";
					});
					var selKeys = $.map(selNodes, function(node)
					{
						   return node.data.key;
					});
					var selTitle = $.map(selNodes, function(node)
					{
						   return node.data.title;
					});
					$("#echoSelection").text(selValor.join(", "));
					parent.document.getElementById("<?php echo $id;?>").value=selKeys.join(", ");
					parent.document.getElementById("<?php echo $text;?>").value=selTitle.join(", ");
					parent.document.getElementById("<?php echo $text;?>").click();
				},
				
				onClick: function(node, event) 
				{
					// We should not toggle, if target was "checkbox", because this
					// would result in double-toggle (i.e. no toggle)
					if( node.getEventTargetType(event) == "title" )
						node.toggleSelect();
				},
				
				onKeydown: function(node, event) 
				{
					if( event.which == 32 ) {
						node.toggleSelect();
						return false;
					}
				},
				
				onPostInit: function(isReloading, isError) {
					/*var node = $("#tree").dynatree("getTree").getNodeByKey(folder);

					node.visitParents( function (node) {
					   node.toggleExpand();
					},true);/
					$("#tree").dynatree("getRoot").visitParents(function(node){
						node.toggleExpand(true);
					});  */
					var tree = $('#tree').dynatree('getTree');
					var selKeys = $.map(tree.getSelectedNodes(), function(node){
						node.makeVisible();
					});
					var selNodes = $("#tree").dynatree("getSelectedNodes");
					var selValor = $.map(selNodes, function(node)
					{
						   return "[id=" + node.data.key + ":-Texto=" + node.data.title + "]";
					});
					$("#echoSelection").text(selValor.join(", "));
					  
				},
                                onLazyRead: function(node){
                                    nivel = node.data.tooltip/1+1;
                                    console.log(nivel);
                                    var cambio = false;
                                    while(jQuery.inArray(nivel,omitidos)>-1)
                                    {
                                        cambio = true;
                                        nivel +=1;
                                    }
                                    console.log(nivel);
                                node.appendAjax({url: '/<?php echo DIR_SIIGS.'/raiz/getTreeBlock';?>',
                                data: { "nivel" : ((cambio == true) ? nivel : 0),
                                        "idarbol" : idarbol,
                                        "elegido" : node.data.key,
                                        'seleccionable': (jQuery.inArray(nivel,seleccionables)==-1 ? false : true),
                                       "mode": "all"
                                       },
                                // (Optional) use JSONP to allow cross-site-requests
                                // (must be supported by the server):
                                // dataType: "jsonp",
                                success: function(node) {
                                    // Called after nodes have been created and the waiting icon was removed.
                                    // 'this' is the options for this Ajax request
                                    },
                                error: function(node, XMLHttpRequest, textStatus, errorThrown) {
                                    // Called on error, after error icon was created.
                                    },
                                cache: false // Append random '_' argument to url to prevent caching.
                               });
                                 },
				// The following options are only required, if we have more than one tree on one page:
				//cookieId: "dynatree-Cb2",
				//idPrefix: "dynatree-Cb2-"
			});
		});
		<!-- End_Exclude -->
	});
	
</script>
</head>
<body >

<br>	
		<?php if($menu){?>
		<a href="#" id="btnSelectAll" class="add">Marcar</a> -
		<a href="#" id="btnDeselectAll" class="cancelar">Desmarcar</a> -
		<a href="#" id="btnToggleSelect" class="agregar">Alternar</a>
        &nbsp;
        <a href="#" id="btnCollapseAll" class="cancelar">Collapse All </a>
		<a href="#" id="btnExpandAll" class="add">Expand All </a>
		<?php } ?>
        <a href="#" onClick="parent.jQuery.fancybox.close();" class="guardar">Ok</a>
        <h1><?php echo htmlentities(urldecode($titulo)); ?></h1>
       	<div>Seleccionado: <span id="echoSelection">-</span></div>
		<div id="tree" style="overflow:inherit;"></div>
	
</body>
</html>
