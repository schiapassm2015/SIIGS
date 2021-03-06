<link href="/resources/themes/jquery.ui.all.css" rel="stylesheet" type="text/css" />
<script src="/resources/ui/jquery-ui-1.8.17.custom.js" type="text/javascript"></script>	
<script type="text/javascript">
DIR_SIIGS = '<?php echo DIR_SIIGS; ?>';

$(document).ready(function(){
    $('#paginador a').click(function(e){
        e.preventDefault();
        pag = $(this).attr('href');
        $('#form_filter_errorlog').attr('action', pag);
        $('#form_filter_errorlog').submit();
    });

    $('#btnFiltrar').click(function(e){
        // Eliminar la pagina de la url del action
        action = $('#form_filter_errorlog').attr('action');
        action = action.replace(/\d+(\/)*$/,'');

        $('#form_filter_errorlog').attr('action',action);
        $('#form_filter_errorlog').submit();
    });
	$("a#detalles").fancybox({
		'width'             : '50%',
		'height'            : '60%',				
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'type'			: 'iframe',									
	}); 
    $('select[name="entorno"]').change(function(e){
        $.ajax({
            type: 'POST',
            url:  '/'+DIR_SIIGS+'/controlador',
            data: 'id_entorno='+$(this).val(),
            dataType: 'json'
        }).done(function(controladores){
            $('select[name="controlador"] > :not(option[value=0])').remove();
            
            $.each(controladores, function(index) {
                option = $('<option />');
                option.val(controladores[index].id);
                option.text(controladores[index].nombre);

                $('select[name="controlador"]').append(option);
            });
        });
    });
    
    $("#fechaIni").datepicker(optionsFecha);
    $("#fechaFin").datepicker(optionsFecha);
    
    $("#limpiaFecha").click(function(){
        $("#fechaIni").val('');
        $("#fechaFin").val('');
    });

});
</script>

<h2><?=$title;?></h2>

    <?php echo form_open(site_url().DIR_SIIGS.'/errorlog/index/'.$pag, array('name'=>'form_filter_errorlog', 'id'=>'form_filter_errorlog')); ?>
        <p><input type="hidden" name="filtrar" value="true" />
        Usuario:
        <?php if(isset($usuarios)) echo form_dropdown('usuario', $usuarios); ?>
        Desde: <input type="text" name="fechaIni" id="fechaIni" value="<?php echo isset($fechaIni) ? $fechaIni: ''; ?>" style="width: 90px;" />
        Hasta: <input type="text" name="fechaFin" id="fechaFin" value="<?php echo isset($fechaFin) ? $fechaFin: ''; ?>" style="width: 90px;" /> 
               <input type="button" value="Limpiar Fechas" id="limpiaFecha" class="btn btn-mini btn-primary" />
        </p>
        <p>Entorno:
            <?php if(isset($entornos)) echo form_dropdown('entorno', $entornos); ?>
        Controlador:
            <?php if(isset($controladores)) echo form_dropdown('controlador', $controladores);?>
        Acción:
            <?php if(isset($acciones)) echo form_dropdown('accion', $acciones);?></p>
        <button type="button" name="btnFiltrar" id="btnFiltrar" class="btn btn-primary">Buscar <i class="icon-search"></i></button>
    </form>

<?php
    if(!empty($msgResult))
        echo '<div class="'.($clsResult ? $clsResult : 'info').'">'.$msgResult.'</div>';
?>
<br />

<div class="table table-striped">
<table>
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Nombre</th>
            <th>Fecha</th>
            <th>Descripción</th>
            <th>Entorno</th>
            <th>Controlador</th>
            <th>Acción</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if(!empty($registros)) {
            foreach ($registros as $fila) {
                echo '<tr id="'.$fila->id.'">
                    <td>'.$fila->usuario.'</td>
                    <td>'.$fila->nombre.' '.$fila->apellido_paterno.' '.$fila->apellido_materno.'</td>
                    <td>'.htmlentities($fila->fecha_hora).'</td>
                    <td>'.htmlentities($fila->descripcion).'</td>
                    <td>'.htmlentities($fila->entorno).'</td>
                    <td>'.htmlentities($fila->controlador).'</td>
                    <td>'.htmlentities($fila->accion).'</td>
                    <td><a id="detalles" href="'.site_url().DIR_SIIGS.'/errorlog/view/'.$fila->id.'" class="btn btn-small btn-primary btn-icon">Detalles <i class="icon-eye-open"></i></a></td>
                </tr>';
            }
        } else {
            echo '<tr><td colspan="11"><div align="center">No se encontraron registros en la busqueda</div></td></tr>';
        }
        ?>
    </tbody>
    <tfoot>
        <tr><td colspan="11">
            <div id="paginador" align="center"><?php echo $this->pagination->create_links(); ?></div>
        </td></tr>
    </tfoot>
</table>
</div>