<script type="text/javascript">
    $(document).ready(function(){
        dir_siigs = "<?php echo DIR_SIIGS; ?>";
        $('#id_entorno').change(function(){ 
            var entorno_id = $('#id_entorno').val();
            if (entorno_id != -1)
            {
	            var grupo_id = $('#id_grupo').val();
	            var numAcciones = $('#numAcciones').val();
	            $.ajax({
	                type: "GET",
	                url: "/"+dir_siigs+"/controlador/getGroupPermissions/"+entorno_id+"/"+grupo_id,
	                dataType: "json",
	            })
	              .done(function(acciones)
	                {
		              if (acciones.length > 0)
		              {
		                  var numControladores = acciones.length/numAcciones;
		            	  var vHtml = '<div class=\'table table-striped\'><table><thead><tr><th><h2>CONTROLADOR</h2></th>';
		            	  var ctrAnterior = '';
		            	  for (i = 0; i < numAcciones; ++i) {
		            	      vHtml += '<th><h2>'+acciones[i].accion+'</h2></th>';
		            	  }
		            	  vHtml += '</tr></thead>';
		                  $.each(acciones,function(obj)
		                  {
		                      if (ctrAnterior = '' || acciones[obj].controlador != ctrAnterior)
		                	  	vHtml += '<tr><td>'+acciones[obj].controlador+'</td>';
		                	  vHtml += '<td><input type=\"checkbox\" id=\"'+acciones[obj].id+'\" name=\"permisos[]\" value=\"'+acciones[obj].id+'\"';
							  if (acciones[obj].id == 0)
								  vHtml += ' disabled=\"true\"';
							  if (acciones[obj].activo == 1)
								  vHtml += ' checked';
							  vHtml += '></td>';
		                      ctrAnterior = acciones[obj].controlador;
		                  });
		                  vHtml += '</tr></table></div>';
		            	  $('#acciones').html(vHtml);
		              }
		              else $('#acciones').html('');
	                })
	                .fail(function(e){
	                	console.log('fail');
	                	console.log(e);
		                });
            }         
            else $('#acciones').html('');    
        });
        $('#btnGuardar').click(function(){ 
        	if ($('select[name="id_entorno"]').val() == -1)
        	{
        		document.getElementById("alert");
				document.getElementById("alert").className="warning";
				document.getElementById("alert").innerHTML='<div>Debe seleccionar un entorno</div>';
            	event.preventDefault();
        	}
        });
	});
</script>
<h2><?php echo $title; ?></h2>
<?php if(!empty($msgResult))
        echo '<div class="'.($clsResult ? $clsResult : 'info').'">'.$msgResult.'</div>';
	if (isset($actions)) { 
	echo validation_errors(); 
	echo form_open(DIR_SIIGS.'/grupo/'.$id_grupo.'/permiso') ?>
<div id="alert"></div>   
<label for="id_entorno">Entorno</label>
<?php echo form_dropdown('id_entorno', $entornos, ($this->input->post('id_entorno')) ? $this->input->post('id_entorno') : '-1', 'id="id_entorno"'); ?>
<br />
<div id="acciones" style="overflow: auto"></div>
<input type="hidden" name="id_grupo" id="id_grupo" value="<?php echo $id_grupo;?>" />
<input type="hidden" name="numAcciones" id="numAcciones" value="<?php echo count($actions);?>" />
		<button type="submit" name="submit" id="guardar" class="btn btn-small btn-primary btn-icon" >Guardar <i class="icon-hdd"></i></button>
		<button type="button"  onclick="window.location.href='/<?php echo DIR_SIIGS?>/grupo/'" class="btn btn-small btn-primary btn-icon">Cancelar <i class="icon-arrow-left"></i></button>
</form>
<?php } else {?>
<table>
<thead>
<tr>
	<th>No se encontraron registros</th>
</tr>
</thead>
</table>
<?php } ?>