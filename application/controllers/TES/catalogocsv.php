<?php
/**
 * Controlador Catalogo
 *
 * @author     Geovanni
 * @created    2013-10-07
 */
class CatalogoCsv extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

			try
		{
                        $this->load->helper('url');
			$this->load->model(DIR_TES.'/CatalogoCsv_model');
		}
		catch (Exception $e)
		{
			$this->template->write("content",$e->getMessage());
			$this->template->render();
		}
	}

	/**
	 *Acción por default del controlador, carga la lista
	 *de catálogos disponibles y una lista de opciones
	 *No recibe parámetros
	 *
	 *@return void
	 */
	public function index()
	{
		if (empty($this->CatalogoCsv_model))
			return false;
                if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url()))
		show_error('', 403, 'Acceso denegado');
		try
		{

			$data['title'] = 'Lista de catálogos disponibles';
			$data['catalogos'] = $this->CatalogoCsv_model->getAll();
			$data['msgResult'] = $this->session->flashdata('msgResult');
		}
		catch (Exception $e)
		{
			$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
		}

		$this->template->write_view('content',DIR_TES.'/catalogocsv/index', $data);
                
		$this->template->render();
	}

	/**
	 *Acción para visualizar de un catálogo específico, obtiene el objeto
	 *catalogo por medio del nombre proporcionado
	 *
	 * @param  string $nombre Este parametro no puede ser nulo
	 * @return void
	 */
	public function view($nombre)
	{
		if (empty($this->CatalogoCsv_model))
			return false;
                
                if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url()))
		show_error('', 403, 'Acceso denegado');
                
		try
		{
			$data['title'] = "Detalles del catálogo";
			$data['catalogo_item'] = $this->CatalogoCsv_model->getByName($nombre);
                        $data['datos_cat'] = $this->CatalogoCsv_model->getAllData($nombre);
		}
		catch (Exception $e)
		{
			$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
		}

		$this->template->write_view('content',DIR_TES.'/catalogocsv/view', $data);
		$this->template->render();
	}
	
        /**
	 *Acción para cargar datos desde un archivo CSV, recibe el stream desde las variables PHP
	 *compara los datos recibidos con los datos que contiene actualmente el catálogo, regresa como 
	 *resultado las filas nuevas y las filas a modificar 
	 *
	 * @return void
	 */
	public function loadupdate($nombrecat , $update = false)
	{
            //if (!Usuario_model::checkCredentials(DIR_SIIGS.'::'.__METHOD__, current_url()))
            //show_error('', 403, 'Acceso denegado');
            
		if (isset($_FILES["archivocsv"]) && is_uploaded_file($_FILES['archivocsv']['tmp_name']))
		//if (TRUE)
		{
                        $fp = fopen($_FILES['archivocsv']['tmp_name'], "r");
			//$fp = fopen('catalogos/metadatos/ACCIONES_NUTRICIONALES.csv', "r");
			 $cont = 0;
			 $columnas = array();
			 $resultado = array();
			 $rows = array();
			 $nuevos = 0; $modificados = 0; $iguales = 0;
		 
			 try 
			 {
			 	//array que contiene todos los registros llave
			 	$datallaves = array();
				//obtiene la estructura del catalogo
			 	$catalogo = $this->CatalogoCsv_model->getByName($nombrecat);
			 	//obtiene los nombres de los campos con su tipo de dato y otros valores
			 	$campostemp = explode('||', $catalogo->campos);
			 	$llavestemp = explode('||',$catalogo->llave);
			 	
			 	//array para hacer modificaciones por lotes
			 	$consultamodificar = array();
			 	//array para hacer inserciones por lotes
			 	$consultaagregar = array();
			 	
			 	//filtro solo los nombres de los campos
			 	$campos = array();
			 	$llaves = array();
			 	//obtiene el nombre de los campos y las llaves
			 	foreach($campostemp as $item)
			 		array_push($campos, explode('|',$item)[0]);
			 	foreach($llavestemp as $item)
			 	{
			 		$key = explode('|',$item)[0];
			 			array_push($llaves, $key);
			 	}
                                
                                //agrega las llaves como campos normales para obtener el numero total de columnas
                                $colstemp = $llaves;
                                foreach ($campos as $campo)
                                {
                                        array_push($colstemp, $campo);
                                }
                                $campos = $colstemp;
		 		//obtiene las llaves primarias del catalogo
                                $rowsllaves = array();
                                if (count($llaves)>0)
                                {
                                $rowsllaves = $this->db->query("select ".implode(",",$llaves)." from ".$nombrecat);
		 		$rowsllaves = $rowsllaves->result();
                                }
                                
                                                                //obtiene los datos excepto llaves
                                $rowscat = array();
                                if (count($campos)>0)
                                {
                                $rowscat = $this->db->query("select ".implode(",",$campos)." from ".$nombrecat);
		 		$rowscat = $rowscat->result_array();
                                }
			 } 
			 catch (Exception $e) 
			 {
			 	echo Errorlog_model::save($e->getMessage(), __METHOD__);
			 	die();
			 }
			 while (!feof($fp))
			 {
			  	$utf8_encode = function($val)
                                {
                                    return utf8_encode($val);
                                };
			  	$data  = array_map($utf8_encode,explode(",", fgets($fp)));
			  	$cont +=1;
				$data = preg_replace("!\r?\n!", "", $data);
			  	if ($cont == 1)
			  	{
			  		$errorcols = false;
			  		$columnas = $data;
			  		if (count($campos) != count($columnas))
			  		$errorcols = true;
			  		for ($i = 0; $i < count($campos); $i++) 
			  		{
			  			if (!isset($columnas[$i]) || !isset($campos[$i]) || $campos[$i] != $columnas[$i])
				  			$errorcols = true;
			  		}
			  		if ($errorcols)
			  		{
			  			echo json_encode(array("Error","Las columnas del CSV no coinciden con la estructura de la tabla"));
			  			die();
			  		}
			  		else 
			  		{
			  			$indicellaves = array();
			  			//Obtiene los indices de columnas que corresponden a las llaves en el CSV
			  			for ($i = 0; $i < count($columnas); $i++) 
			  			{
			  				if (in_array($columnas[$i],$llaves))
			  				{
			  					array_push($indicellaves,$i);
			  				}
			  			}
			  		}
			  	}
			  	else
			  	{
			  		if (count($columnas) == count($data))
			  		{	
			  			$datallave = array();
			  			foreach ($indicellaves as $i)
			  			{
			  				array_push($datallave, $data[$i]);
			  			}
			  			//agrega el registro llave a la lista de llaves
			  			array_push($datallaves,$datallave);
				  		//agrega las claves con nombres de campo
			  			$procesada = array_combine($columnas,$data);
			  			//si el registro existe en el catalogo
				  		if (in_array($procesada, $rowscat))
				  		{
				  			$iguales += 1;
				  		}
				  		else
				  		{
				  			//si la clave existe en el catalogo
				  			if (in_array((object)array_combine($llaves,$datallave), $rowsllaves))
				  			{
								$consultaupdate = 'update '.$nombrecat. ' set ';
								$consultaupdatewhere = ' where 1=1 ';
								foreach ($procesada as $key => $value) 
								{
									$contcampos = 0; $contllaves = 0;
									if (!in_array($key, $llaves))
									{
									$consultaupdate .= $key." = '".$value."',";
									}
									else
									{
										$consultaupdatewhere .= ' and '.$key." = '".$value."'";
									}
								}
								$consultaupdate = substr($consultaupdate,0, count($consultaupdate)-2);
								$consultaupdate .= $consultaupdatewhere;
								array_push($consultamodificar, $consultaupdate);
				  				$modificados += 1;
				  			}
				  			else
				  			{
				  				array_push($consultaagregar,"insert into ".$nombrecat. " (".implode(",", $campos).") values ('".implode("','", $data)."')");
				  				$nuevos += 1;
				  			}
				  		}
			  		}
			  	}
			 }
			 if (count($datallaves) != count($this->_array_unique_recursive($datallaves)))
			 {
			 	echo json_encode(array("Error","El archivo contiene llaves primarias duplicadas"));
			 	die();
			 }
			 else 
			 {
				 array_push($resultado,array('Numero de registros anteriores',count($rowscat)));
				 array_push($resultado,array('Numero de registros actuales',$cont-1));
				 array_push($resultado,array('Numero de registros a insertar',$nuevos));
				 array_push($resultado,array('Numero de registros a modificar',$modificados));
				 array_push($resultado,array('Numero de registros sin cambios',$iguales));
			 }
			 if ($update == false)
			 echo json_encode($resultado);
			 else 
			 {
			 	$this->db->trans_begin();
				foreach ($consultamodificar as $sql)
					$this->db->query($sql);
				foreach ($consultaagregar as $sql)
				$this->db->query($sql);{
				
				if ($this->db->trans_status() === FALSE)
				{
				    $this->db->trans_rollback();
				    echo json_encode(array("Error","Ha ocurrido un error al hacer el volcado, los datos no se modificaron."));
				}
				else
				{
				    $this->db->trans_commit();
				    echo json_encode(array("Ok","Los datos del catalogo se han modificado correctamente"));
                                    $this->db->query("update cns_tabla_catalogo set fecha_actualizacion = NOW() where descripcion='".$nombrecat."'");
				}
			 }
			}
		}
		else
		{
			 	echo json_encode(array("Error","El archivo no ha sido cargado correctamente."));
			 	die();
		}
	}
	
	/**
	 * _array_unique_recursive
	 * Revisa valores duplicados en arreglos que contienen arreglos
	 * 
	 * @param array $arr
	 */
	public function _array_unique_recursive($arr)
	{
		foreach($arr as $key=>$value)
			if(gettype($value)=='array')
			    $arr[$key]=$this->_array_unique_recursive($value);
			return array_unique($arr,SORT_REGULAR);
	}
	

	/**
	 *Acción para preparar la actualizacion de un catálogo ya existente,
	 *recibe un string para obtener los valores del catalogo y mostrarlos
	 *en la vista update , realiza la validacion del formulario del lado
	 *del cliente y servidor
	 *
	 * @param  string $nombre
	 * @return void
	 */
	public function update($nombre)
	{
		if (empty($this->CatalogoCsv_model))
			return false;
                if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url()))
		show_error('', 403, 'Acceso denegado');		
                
		try
		{
			$data['title'] = "Modificar datos del catálogo";
			$data['catalogo_item'] = $this->CatalogoCsv_model->getByName($nombre);
		}
		catch (Exception $e)
		{
			$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
		}

		$this->template->write_view('content',DIR_TES.'/catalogocsv/update', $data);
		$this->template->render();
		
	}

	/**
	 *
	 *Acción para revisar registros repetidos en las columnas designadas como primary key
	 *
	 * @param  string $campos
	 * @return void
	 */
	public function checkpk($campos)
	{
		try
		{
			if (empty($this->CatalogoCsv_model))
				return false;

			$this->load->helper('url');
			$result = $this->CatalogoCsv_model->checkPk($campos);
			if (count($result) == 0)
				echo "true";
			else
				echo "false";
			
		}
		catch(Exception $e)
		{
			Errorlog_model::save($e->getMessage(), __METHOD__);	
			echo "false";
		}
	}
}
