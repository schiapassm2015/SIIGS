<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Controller Usuario
 *
 * @package     TES
 * @subpackage  Controlador
 * @author     	Eliecer
 * @created     2013-12-17
 */
class Enrolamiento extends CI_Controller 
{

	public function __construct()
	{
		parent::__construct();
		try
		{
			$this->load->helper('url');
			$this->load->helper('date');
		}
		catch(Exception $e)
		{
	 		$this->template->write("content", $e->getMessage());
 			$this->template->render();
		}
	}
	
	/**
	 *Este es el metodo por default, obtiene el listado de las perosnas
	 *se recibe el parametro $pag de tipo int que representa la paginacion
	 *
	 */
	public function index($pag = 0)
	{
		try{
			if (empty($this->Usuario_model))
				return false;
			if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url()))
				show_error('', 403, 'Acceso denegado');
			$this->load->model(DIR_TES.'/Enrolamiento_model');
			$data['title'] = 'Lista Enrolados';
			$this->load->helper('form');
			$this->load->library('pagination');
			
			$data['pag'] = $pag;
			$data['msgResult'] = $this->session->flashdata('msgResult');
			
			// Configuración para el Paginador
			$configPag['base_url']   = '/'.DIR_TES.'/enrolamiento/index/';
			$configPag['first_link'] = 'Primero';
			$configPag['last_link']  = '&Uacute;ltimo';
			$configPag['uri_segment'] = '4';
			$configPag['total_rows'] = $this->Enrolamiento_model->getNumRows($this->input->post('busqueda'));
			$configPag['per_page']   = 20;
			$this->pagination->initialize($configPag);
			if ($this->input->post('busqueda'))
				$data['users'] = $this->Enrolamiento_model->getListEnrolamiento($this->input->post('busqueda'), $configPag['per_page'], $pag);
			else 
				$data['users'] = $this->Enrolamiento_model->getListEnrolamiento('', $configPag['per_page'], $pag);
		}
		catch(Exception $e){
			$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
		}
		//$this->load->view('usuario/index', $data);
 		$this->template->write_view('content',DIR_TES.'/enrolamiento/enrolamiento_list', $data);
 		$this->template->render();
	}
	
	/**
	 *Crea la pagina para ver la infromacion de la persona
	 *se recibe el parametro $id de tipo int que representa el identificador de la persona
	 *
	 */
	public function view($id)
	{
		try 
		{
			if (empty($this->Usuario_model))
				return false;
			if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url()))
				show_error('', 403, 'Acceso denegado');
			$this->load->model(DIR_TES.'/Enrolamiento_model');
			$data['title'] = 'Ver Paciente';
			$data['enrolado'] = $this->Enrolamiento_model->getById($id);
			$data['alergias'] = $this->Enrolamiento_model->getAlergia($id);
			$data['afiliaciones'] = $this->Enrolamiento_model->getAfiliaciones($id);
			
			$data['iras']=$this->Enrolamiento_model->get_catalog_view("ira",$id);
			$data['edas']=$this->Enrolamiento_model->get_catalog_view("eda",$id);
			$data['consultas']=$this->Enrolamiento_model->get_catalog_view("consulta",$id);
			$data['nutricionales']=$this->Enrolamiento_model->get_catalog_view("accion_nutricional",$id);
			
			$nutricion=$this->Enrolamiento_model->get_control_nutricional($id);
			
			$array=array();$i=0;
			foreach($nutricion as $x)
			{
				$fecha=strtotime($x->fecha);
				$dato = array("d1"=>"[".$fecha.",".$x->talla."]", "d2"=>"[".$fecha.",".$x->peso."]", "d3"=>"[".$fecha.",".$x->altura."]");
				$array[$i] = $dato;
				$i++;
			}
			$data['label']=json_encode(array("d1"=>"Talla","d2"=>"Peso","d3"=>"Altura"));
			$data['control_nutricional']=json_encode($array);
			
		}
		catch(Exception $e)
		{
			$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
		}
 		$this->template->write_view('content',DIR_TES.'/enrolamiento/enrolamiento_view', $data);
 		$this->template->render();
	}
	
	/**
	 *Crea el fromulario para editar la informacion de la persona
	 *se recibe el parametro $id de tipo int que representa el idientificador de la persona
	 *
	 */
	
	public function update($id)
	{
		try 
		{
			if (empty($this->Usuario_model))
				return false;
			if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url()))
				show_error('', 403, 'Acceso denegado');
			$this->load->model(DIR_TES.'/Enrolamiento_model');
			$data['id'] = $id;
			$data['title'] = 'Ver Paciente';
			$data['enrolado'] = $this->Enrolamiento_model->getById($id);
			$data['alergias'] = $this->Enrolamiento_model->getAlergia($id);
			$data['afiliaciones'] = $this->Enrolamiento_model->getAfiliaciones($id);
			
			$data['vacunas']=$this->Enrolamiento_model->get_catalog_view("vacuna",$id);
			
			$data['iras']=$this->Enrolamiento_model->get_catalog_view("ira",$id);
			$data['edas']=$this->Enrolamiento_model->get_catalog_view("eda",$id);
			$data['consultas']=$this->Enrolamiento_model->get_catalog_view("consulta",$id);
			$data['nutricionales']=$this->Enrolamiento_model->get_catalog_view("accion_nutricional",$id);
			
			$nutricion=$this->Enrolamiento_model->get_control_nutricional($id);
			$data['nutriciones']=$nutricion;
			$array=array();$i=0;
			foreach($nutricion as $x)
			{
				$fecha=strtotime($x->fecha);
				$dato = array("d1"=>"[".$fecha.",".$x->talla."]", "d2"=>"[".$fecha.",".$x->peso."]", "d3"=>"[".$fecha.",".$x->altura."]");
				$array[$i] = $dato;
				$i++;
			}
			$data['label']=json_encode(array("d1"=>"Talla","d2"=>"Peso","d3"=>"Altura"));
			$data['control_nutricional']=json_encode($array);
			
		}
		catch(Exception $e)
		{
			$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
		}
		try
		{
			if (empty($this->Enrolamiento_model))
				return false;
		/*
		if (!Usuario_model::checkCredentials(DIR_SIIGS.'::'.__METHOD__, current_url()))
			show_error('', 403, 'Acceso denegado');*/
			
			if($this->validarForm())
			{
				try 
				{
					$this->Enrolamiento_model->setId($id);
					$this->addForm();
					// actualizar si todo bien
					$this->Enrolamiento_model->update();
					$this->session->set_flashdata('infoclass','success');
					$this->session->set_flashdata('msgResult', 'Registro actualizado exitosamente');
					//Bitacora_model::insert(DIR_SIIGS.'::'.__METHOD__, 'Usuario Enrolado: '.strtoupper($this->input->post('nombre')));
					
					redirect(DIR_TES.'/enrolamiento','refresh');
				}
				catch (Exception $e)
				{
					$data['infoclass'] = 'error';
					$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
					$data["title"]="TES";
					$data["titulo"]="Enrolamiento";
					
					//$this->template->write_view('header',DIR_TES.'/header.php');
					//$this->template->write_view('menu',DIR_TES.'/menu.php');
					$this->template->write_view('content',DIR_TES.'/enrolamiento/enrolamiento_update',$data);
					//$this->template->write_view('footer',DIR_TES.'/footer.php');	
					$this->template->render();
				}
			}
			else
			{
				$this->template->write_view('content',DIR_TES.'/enrolamiento/enrolamiento_update', $data);
 				$this->template->render();
			}
		}
		catch(Exception $e)
		{
			$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
		}
 		
	}
	/**
	 *Genera los options de un campo tipo select 
	 *se recibe el parametro $catalog de tipo String que representa la tabla
	 *parametro sel para decidir si hay un valor preseleccionado
	 *
	 */
	public function catalog_select($catalog,$sel="")
	{
		$opcion="";
		$this->load->model(DIR_TES.'/Enrolamiento_model');
		$datos=$this->Enrolamiento_model->get_catalog("cns_".$catalog);
		if(sizeof($datos)!=0)
		{
			$opcion.="<option value=''>Seleccione...</option>";
			foreach($datos as $dato)
			{
				$id=$dato->id;
				$che="";
				if(stripos(".".$sel,$id))$che="selected";
				$descripcion=$dato->descripcion;
				$opcion.="<option value='$id' $che>$descripcion</option>";
			}
			echo $opcion;
		}
		else
		echo "<option>No hay Datos</option>";
	}
	/**
	 *Crea un grupo de radio o check con la informacion de los catalogos
	 *se recibe el parametro $catalog de tipo String que representa la tabla
	 *$tipo representa el tipo de control radio o check
	 *$col es el numero de columnas por las que estara dividido la distribucion
	 *$sel para determinar si hay un dato preseleccionado
	 *$orden determina el orden de la visualizacion
	 *
	 */
	public function catalog_check($catalog,$tipo,$col=1,$sel="",$orden="")
	{
		$opcion="";
		$this->load->model(DIR_TES.'/Enrolamiento_model');
		$datos=$this->Enrolamiento_model->get_catalog("cns_".$catalog,"","",$orden);
		if(sizeof($datos)!=0)
		{
			$i=0;$a=0;$y=0;$temp="";$x=0;
			$opcion='<table width="85%" ><tr>';
			foreach($datos as $dato)
			{
				$id=$dato->id;
				$descripcion=$dato->descripcion;
				$che="";
				if($catalog=="alergia")
				{
					if($temp!=$dato->tipo)
					{
						if($y>0)
						{
							$x++;
							$opcion.="</tr></table>";
							if($x==$col){$opcion.="<tr>"; $x=0;}
						}
						$opcion.="<td width='33%' valign='top'><table border=2><tr><th bgcolor='#CCC'> ".$dato->tipo." </th></tr><tr>";		
						$y++;				
					}
					else $opcion.="</tr><tr>";
					$temp=$dato->tipo;
					
				}
				if(stripos(".".$sel,$id))$che="checked";
				if($a==$col&&$catalog!="alergia"){$opcion.="</tr><tr>"; $a=0;}
				
				$opcion.="<td width='33%' valign='top'><label><input name='".$catalog."[]' id='$catalog$i' type='$tipo' value='$id' $che> $descripcion</label></td>";
				$i++;$a++;
			}
			$opcion.='</tr></table>';
			echo $opcion;
		}
		else
		echo "No hay Datos";
	}
	/**
	 *Crea el autocomplete de los datos del tutor
	 *
	 */
	public function autocomplete()
	{
		$term=$_GET["term"];
		$this->load->model(DIR_TES.'/Enrolamiento_model');
		$datos=$this->Enrolamiento_model->autocomplete_tutor($term);
		$array = array();
		$i=0;
		foreach($datos as $data)
		{
				$array[$i] = trim(($data->curp)." => ".$data->nombre." ".$data->apellido_paterno." ".$data->apellido_materno);
				$i++;
		}
		echo json_encode($array);
	}
	/**
	 *Obtiene inofrmacion del tutor
	 *se recibe el parametro $curp de tipo string 
	 *
	 */
	public function data_tutor($curp)
	{
		$this->load->model(DIR_TES.'/Enrolamiento_model');
		$datos=$this->Enrolamiento_model->data_tutor($curp);
		if(sizeof($datos)!=0)
		{
			
			foreach($datos as $dato)
			{
				$m=FALSE;$f=FALSE;
				if($dato->sexo=="M") $m=1;
				if($dato->sexo=="F") $f=1;
				$array=array(
					array(
						"idtutor" => $dato->id,
						"nombreT" => $dato->nombre,
						"paternoT" => $dato->apellido_paterno,
						"maternoT" => $dato->apellido_materno,
						"celularT" => $dato->celular,
						"curpT" => $dato->curp,
						"telefonoT" => $dato->telefono,
						"companiaT" => $dato->id_operadora_celular,
						"sexoT_1" =>  $m,
						"sexoT_2" => $f,
						"error" => "",
					)
				);
			}
		}
		else
		$array=array(
					array(
					"error" => "No existe curp: ".$curp,
					)
				);
		
		echo json_encode($array);
	}
	/**
	 *prepara los datos para insertarlos
	 *
	 */
	public  function insert()
	{
		$this->load->model(DIR_TES.'/Enrolamiento_model');
		try
		{
			if (empty($this->Enrolamiento_model))
				return false;
		
		if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url()))
			show_error('', 403, 'Acceso denegado');
			
			if($this->validarForm())
			{
				try 
				{						
					$this->addForm();
					
					$this->Enrolamiento_model->insert();
					$this->session->set_flashdata('infoclass','success');
					$this->session->set_flashdata('msgResult', 'Registro agregado exitosamente');
					//Bitacora_model::insert(DIR_SIIGS.'::'.__METHOD__, 'Usuario Enrolado: '.strtoupper($this->input->post('nombre')));
					
					redirect(DIR_TES.'/enrolamiento','refresh');
				}
				catch (Exception $e)
				{
					$data['infoclass'] = 'error';
					$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
					$data["title"]="TES";
					$data["titulo"]="Enrolamiento";
					
					//$this->template->write_view('header',DIR_TES.'/header.php');
					//$this->template->write_view('menu',DIR_TES.'/menu.php');
					$this->template->write_view('content',DIR_TES.'/enrolamiento/enrolamiento',$data);
					//$this->template->write_view('footer',DIR_TES.'/footer.php');	
					$this->template->render();
				}
			}
			else
			{
				$data["title"]="TES";
				$data["titulo"]="Enrolamiento";
				
				//$this->template->write_view('header',DIR_TES.'/header.php');
				//$this->template->write_view('menu',DIR_TES.'/menu.php');
				$this->template->write_view('content',DIR_TES.'/enrolamiento/enrolamiento',$data);
				//$this->template->write_view('footer',DIR_TES.'/footer.php');	
				$this->template->render();
			}
		
		}
		catch(Exception $e)
		{
			$data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
		}
	}
	
	
	/**
	 *valida los datos de entrada en el formulario
	 *
	 */
	public function validarForm()
	{
		$data['titulo'] = 'Nuevo Enrolamiento';
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->form_validation->set_rules('nombre', 'Nombre', 'trim|xss_clean|required|max_length[40]');
		$this->form_validation->set_rules('paterno', 'Apellido Paterno', 'trim|xss_clean|required|max_length[25]');
		$this->form_validation->set_rules('materno', 'Apellido Materno', 'trim|xss_clean|required|max_length[25]');
		$this->form_validation->set_rules('sexo', 'Sexo', 'trim|required');
		$this->form_validation->set_rules('sangre', 'Tipo de Sangre', 'trim|required');
		$this->form_validation->set_rules('fnacimiento', 'Fecha de Nacimiento', 'trim|required');
		$this->form_validation->set_rules('lnacimiento', 'Lugar de Nacimiento', 'trim|required');
		$this->form_validation->set_rules('lnacimientoT', 'Lugar de Nacimiento', 'trim');
		$this->form_validation->set_rules('curp', 'CURP', 'trim|required');
		$this->form_validation->set_rules('curp2', 'CURP', 'callback_ifCurpExists');
		$this->form_validation->set_rules('lnacimientoT', 'Lugar de Nacimiento', 'xss_clean|trim');
		$this->form_validation->set_rules('curp', 'CURP', 'trim|xss_clean|required');
		$this->form_validation->set_rules('curp2', 'CURP', 'xss_clean|callback_ifCurpExists');
		$this->form_validation->set_rules('calle', 'Calle', 'trim|xss_clean|required');
		$this->form_validation->set_rules('cp', 'Codigo Postal', 'trim|xss_clean|required');
		$this->form_validation->set_rules('localidad', 'Localidad', 'trim|xss_clean|required');
		$this->form_validation->set_rules('localidadT', 'Localidad', 'xss_clean');
		$this->form_validation->set_rules('fechacivil', 'Fecha Civil', 'trim|required');
		$this->form_validation->set_rules('lugarcivil', 'Lugar Civil', 'trim|xss_clean|required');
		$this->form_validation->set_rules('lugarcivilT', 'Lugar Civil', 'xss_clean');
		
		$this->form_validation->set_rules('nacionalidad', 'Nacionalidad', '');
		$this->form_validation->set_rules('sangre', 'Tipo de Sangre', 'required');
		
		$this->form_validation->set_rules('fechacivil', 'Fecha Civil', 'trim|required');
		$this->form_validation->set_rules('lugarcivil', 'Lugar Civil', 'trim|required');
		$this->form_validation->set_rules('lugarcivilT', 'Lugar Civil', '');
		
		$this->form_validation->set_rules('calle', 'Calle', 'trim|required');
		$this->form_validation->set_rules('referencia', 'referencia', '');
		$this->form_validation->set_rules('colonia', 'colonia', '');
		$this->form_validation->set_rules('cp', 'Codigo Postal', 'trim|required');
		$this->form_validation->set_rules('localidad', 'Localidad', 'trim|required');
		$this->form_validation->set_rules('localidadT', 'Localidad', '');
		$this->form_validation->set_rules('numero', 'numero', '');
		$this->form_validation->set_rules('celular', 'celular', '');
		$this->form_validation->set_rules('telefono', 'telefono', '');
		$this->form_validation->set_rules('colonia', 'colonia', 'xss_clean');
		$this->form_validation->set_rules('numero', 'numero', 'xss_clean');
		$this->form_validation->set_rules('celular', 'celular', 'xss_clean');
		$this->form_validation->set_rules('telefono', 'telefono', 'xss_clean');
		$this->form_validation->set_rules('compania', 'compania', 'trim');
		$this->form_validation->set_rules('companiaT', 'companiaT', 'trim');
		
		$this->form_validation->set_rules('buscar', 'buscar', 'xss_clean');
		$this->form_validation->set_rules('captura', 'captura', '');
		$this->form_validation->set_rules('nombreT', 'nombreT', 'xss_clean');
		$this->form_validation->set_rules('paternoT', 'paternoT', 'xss_clean');
		$this->form_validation->set_rules('maternoT', 'maternoT', 'xss_clean');
		$this->form_validation->set_rules('celularT', 'celularT', 'xss_clean');
		$this->form_validation->set_rules('curpT', 'curpT', 'xss_clean|callback_ifCurpTExists');
		$this->form_validation->set_rules('telefonoT', 'telefonoT', 'xss_clean');
		$this->form_validation->set_rules('sexoT', 'sexoT', '');
					
		return $this->form_validation->run();
	}
	
	/**
	 *Pase de parametros para la insercion o actualizacion
	 *
	 */
	public function addForm()
	{
		$this->Enrolamiento_model->setnacionalidad($this->input->post('nacionalidad'));				
		$this->Enrolamiento_model->setnombre($this->input->post('nombre'));
		$this->Enrolamiento_model->setpaterno($this->input->post('paterno'));
		$this->Enrolamiento_model->setmaterno($this->input->post('materno'));
		$this->Enrolamiento_model->setlnacimiento($this->input->post('lnacimiento'));
		$this->Enrolamiento_model->setcurp($this->input->post('curp').$this->input->post('curp2'));
		$this->Enrolamiento_model->setsexo($this->input->post('sexo'));
		$this->Enrolamiento_model->setsangre($this->input->post('sangre'));
		$this->Enrolamiento_model->setfnacimiento($this->input->post('fnacimiento'));
		$this->Enrolamiento_model->settbeneficiario($this->input->post('tbeneficiario'));
		
		$this->Enrolamiento_model->setidtutor($this->input->post('idtutor'));				
		$this->Enrolamiento_model->setnombreT($this->input->post('nombreT'));
		$this->Enrolamiento_model->setpaternoT($this->input->post('paternoT'));
		$this->Enrolamiento_model->setmaternoT($this->input->post('maternoT'));
		$this->Enrolamiento_model->setcurpT($this->input->post('curpT'));
		$this->Enrolamiento_model->setsexoT($this->input->post('sexoT'));
		$this->Enrolamiento_model->settelefonoT($this->input->post('telefonoT'));
		$this->Enrolamiento_model->setcompaniaT($this->input->post('companiaT'));
		$this->Enrolamiento_model->setcelularT($this->input->post('celularT'));
		
		$this->Enrolamiento_model->setfechacivil($this->input->post('fechacivil'));				
		$this->Enrolamiento_model->setlugarcivil($this->input->post('lugarcivil'));
		
		$this->Enrolamiento_model->setcalle($this->input->post('calle'));
		$this->Enrolamiento_model->setreferencia($this->input->post('referencia'));				
		$this->Enrolamiento_model->setcolonia($this->input->post('colonia'));
		$this->Enrolamiento_model->setlocalidad($this->input->post('localidad'));
		$this->Enrolamiento_model->settelefono($this->input->post('telefono'));
		$this->Enrolamiento_model->setcompania($this->input->post('compania'));
		$this->Enrolamiento_model->setcelular($this->input->post('celular'));
		$this->Enrolamiento_model->setnumero($this->input->post('numero'));
		$this->Enrolamiento_model->setcp($this->input->post('cp'));
		
		$this->Enrolamiento_model->setafiliacion($this->input->post('afiliacion'));
		$this->Enrolamiento_model->setalergias($this->input->post('alergia'));
		
		$this->Enrolamiento_model->setvacuna($this->input->post('vacuna'));
		$this->Enrolamiento_model->setfvacuna($this->input->post('fvacuna'));
		
		$this->Enrolamiento_model->setira($this->input->post('ira'));
		$this->Enrolamiento_model->setfira($this->input->post('fira'));
		
		$this->Enrolamiento_model->seteda($this->input->post('eda'));
		$this->Enrolamiento_model->setfeda($this->input->post('feda'));
		
		$this->Enrolamiento_model->setconsulta($this->input->post('consulta'));
		$this->Enrolamiento_model->setfconsulta($this->input->post('fconsulta'));
		
		$this->Enrolamiento_model->setaccion_nutricional($this->input->post('accion_nutricional'));
		$this->Enrolamiento_model->setfaccion_nutricional($this->input->post('faccion_nutricional'));
		
		$this->Enrolamiento_model->setpeso($this->input->post('cpeso'));
		$this->Enrolamiento_model->setaltura($this->input->post('caltura'));
		$this->Enrolamiento_model->settalla($this->input->post('ctalla'));
		$this->Enrolamiento_model->setfnutricion($this->input->post('fCNu'));
	}
	
	// valida el curp del paciente
	public function ifCurpExists($curp) 
	{
		$id=$this->input->post('id');
		$curp = $this->input->post('curp').$this->input->post('curp2');
		if (empty($this->Enrolamiento_model))
			return false;
		$is_exist = null;
		try {
			$is_exist = $this->Enrolamiento_model->getByCurp($curp,'cns_persona',$id);
		}
		catch(Exception $e){
		}
		if ($is_exist) 
		{
			$this->form_validation->set_message(
					'ifCurpExists', 'El curp del paciente ya existe.'
			);
			return false;
		} 
		else 
		{
			if (!$this->Enrolamiento_model->getMsgError())
				return true;
			else{
				$this->form_validation->set_message(
						'ifCurpExists', $this->Enrolamiento_model->getMsgError()
				);
				return false;
			}
		}
	}
	// valida el curp del padre o tutor
	public function ifCurpTExists($curp) 
	{
		$id=$this->input->post('idtutor');
		if (empty($this->Enrolamiento_model))
			return false;
		$is_exist = null;
		try {
			$is_exist = $this->Enrolamiento_model->getByCurp($curp,'cns_tutor',$id);
		}
		catch(Exception $e){
		}
		if ($is_exist) 
		{
			$this->form_validation->set_message(
					'ifCurpTExists', 'El curp del tutor ya existe.'
			);
			return false;
		} 
		else 
		{
			if (!$this->Enrolamiento_model->getMsgError())
				return true;
			else{
				$this->form_validation->set_message(
						'ifCurpTExists', $this->Enrolamiento_model->getMsgError()
				);
				return false;
			}
		}
	}
}