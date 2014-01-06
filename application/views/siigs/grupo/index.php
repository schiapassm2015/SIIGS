<script type="text/javascript">
$(document).ready(function(){
    $('#paginador a').click(function(e){
        e.preventDefault();
        pag = $(this).attr('href');
        $('#form_filter_bitacora').attr('action', pag);
        $('#form_filter_bitacora').submit();
    });

    $('#btnFiltrar').click(function(e){
        // Eliminar la pagina de la url del action
        action = $('#form_filter_bitacora').attr('action');
        action = action.replace(/\d+(\/)*$/,'');

        $('#form_filter_bitacora').attr('action',action);
        $('#form_filter_bitacora').submit();
    });
    
});
</script>
<?php 
$opcion_insert = Menubuilder::isGranted(DIR_SIIGS.'::grupo::insert');
$permiso_index = Menubuilder::isGranted(DIR_SIIGS.'::permiso::index');
$opcion_view = Menubuilder::isGranted(DIR_SIIGS.'::grupo::view');
$opcion_update = Menubuilder::isGranted(DIR_SIIGS.'::grupo::update');
$opcion_delete = Menubuilder::isGranted(DIR_SIIGS.'::grupo::delete');
?>
<h2><?php echo $title ?></h2>
<?php if(!empty($msgResult))
        echo '<div class="'.($clsResult ? $clsResult : 'info').'">'.$msgResult.'</div>'; ?>
<fieldset style="width: 50%">
<?php echo form_open(DIR_SIIGS.'/grupo/index/'.$pag, array('name'=>'form_filter_bitacora', 'id'=>'form_filter_bitacora')); ?>
Buscar grupo
<input type="text" name="busqueda" value="<?php echo set_value('busqueda', ''); ?>" /> 
<input type="submit" name="btnFiltrar" id="btnFiltrar" value="Buscar" class="btn btn-primary" />
</form>
</fieldset>
<?php if($opcion_insert) { ?><a href="/<?php echo DIR_SIIGS?>/grupo/insert" class="btn btn-primary">Crear nuevo</a><?php } ?>
<div class="table table-striped">
<table>
<thead>
		<?php if($permiso_index) { ?><th><h2></h2>&nbsp;</th><?php } ?>
		<th><h2>Nombre</h2></th>
		<th><h2>Descripción</h2></th>
		<?php if($opcion_view) { ?><th><h2></h2></th> <?php } ?>
		<?php if($opcion_update) { ?><th><h2></h2></th> <?php } ?>
		<?php if($opcion_delete) { ?><th><h2></h2></th> <?php } ?>
</thead>
<tbody>
	<?php if (isset($groups)) foreach ($groups as $group_item): ?>
	<tr>
		<?php if($permiso_index) { ?><td><a href="/<?php echo DIR_SIIGS?>/grupo/<?php echo $group_item->id ?>/permiso" class="btn btn-primary">Permisos</a></td><?php } ?>
		<td><?php echo $group_item->nombre ?></td>
		<td><?php echo $group_item->descripcion ?></td>
		<?php if($opcion_view) { ?><td><a href="/<?php echo DIR_SIIGS?>/grupo/view/<?php echo $group_item->id ?>" class="btn btn-primary">Ver detalles</a></td><?php } ?>
		<?php if($opcion_update) { ?><td><a href="/<?php echo DIR_SIIGS?>/grupo/update/<?php echo $group_item->id ?>" class="btn btn-primary">Modificar</a></td><?php } ?>
		<?php if($opcion_delete) { ?><td><a href="/<?php echo DIR_SIIGS?>/grupo/delete/<?php echo $group_item->id ?>" class="btn btn-primary" onclick="if (confirm('Realmente desea eliminar este grupo?')) { return true; } else {return false;}">Eliminar</a></td><?php } ?>
			</tr>
	<?php endforeach ?>
</tbody>
    <tfoot>
        <tr><td colspan="7">
            <div id="paginador" align="center"><?php echo $this->pagination->create_links(); ?></div>
        </td></tr>
    </tfoot>
</table>
</div>