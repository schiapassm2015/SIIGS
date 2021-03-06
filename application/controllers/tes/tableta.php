<?php

/**
 * Controlador Tableta
 *
 * @package    TES
 * @subpackage Controlador
 * @author     Pascual
 * @created    2013-11-26
 */
class Tableta extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();

        if(!$this->db->conn_id) {
            $this->template->write('content', 'Error no se puede conectar a la Base de Datos');
            $this->template->render();
        }
        
        $this->load->helper('url');
        $this->load->model(DIR_TES.'/Tableta_model');
    }

    /**
     * Lista todos los registros de tabletas, con su correspondiente paginación
     * permite eliminar un conjunto de registro o un elemento individual,
     * muestra enlaces para actualizar y ver detalles de un elemento especifico
     *
     * @access public
     * @param  int    $pag Establece el desplazamiento del primer registro a devolver
     * @return void
     */
    public function index($pag = 0)
    {
        if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url())) {
            show_error('', 403, 'Acceso denegado');
            return false;
        }
        
        if(!isset($this->Tableta_model))
            return false;

        try {
            $this->load->library('pagination');
            $this->load->helper(array('form', 'formatFecha'));
            $this->load->model(array(DIR_TES.'/Tipo_censo_model', DIR_SIIGS.'/ArbolSegmentacion_model'));
            
            $data = array();

            $data['pag'] = $pag;
            $data['msgResult'] = $this->session->flashdata('msgResult');
            $data['clsResult'] = $this->session->flashdata('clsResult');
            $data['estados'] = (array)$this->ArbolSegmentacion_model->getDataKeyValue(1, 1);
            $data['jurisdicciones'] = array(0=>'Seleccione una opción');
            $data['municipios'] = array(0=>'Seleccione una opción');
            $data['localidades'] = array(0=>'Seleccione una opción');
            $data['unidades'] = array(0=>'Seleccione una opción');
            $data['title'] = 'Tableta';
            $data['tipos_censo'] = $this->Tipo_censo_model->getAll();
            $data['unidades_medicas'] = array();
            
            $registroEliminar = isset($_POST['registroEliminar']) ? $_POST['registroEliminar'] : null;

            if( !empty($registroEliminar) ) {
                $this->Tableta_model->delete($registroEliminar);
                $data['msgResult'] = 'Registros Eliminados exitosamente';
                $data['clsResult'] = 'success';
            }
            
            // Configuración para el Paginador
            $configPag['base_url']    = site_url().DIR_TES.'/tableta/index/';
            $configPag['first_link']  = 'Primero';
            $configPag['last_link']   = '&Uacute;ltimo';
            $configPag['uri_segment'] = 4;
            $configPag['total_rows']  = $this->Tableta_model->getNumRows();
            $configPag['per_page']    = 20;

            $this->pagination->initialize($configPag);
            
            $filtro = array(
                'edo'    => $this->input->post('estados'),
                'juris'  => $this->input->post('juris'),
                'muni'   => $this->input->post('municipios'),
                'locali' => $this->input->post('localidades'),
                'um'     => $this->input->post('ums'),
            );
            
            if(!empty($filtro['edo'])) {
                $query = $this->db->query('SELECT id,descripcion FROM asu_arbol_segmentacion WHERE id_raiz=1 AND id_padre='.$filtro['edo'].' ORDER BY descripcion');
                $result = $query->result();

                if ($result){
                    foreach ($result as $temp) {
                        $data['jurisdicciones'][$temp->id] = $temp->descripcion;
                    }
                }
            }
            
            if(!empty($filtro['juris'])) {
                $query = $this->db->query('SELECT id,descripcion FROM asu_arbol_segmentacion WHERE id_raiz=1 AND id_padre='.$filtro['juris'].' ORDER BY descripcion');
                $result = $query->result();

                if ($result){
                    foreach ($result as $temp) {
                        $data['municipios'][$temp->id] = $temp->descripcion;
                    }
                }
            }
            
            if(!empty($filtro['muni'])) {
                $query = $this->db->query('SELECT id,descripcion FROM asu_arbol_segmentacion WHERE id_raiz=1 AND id_padre='.$filtro['muni'].' ORDER BY descripcion');
                $result = $query->result();

                if ($result){
                    foreach ($result as $temp) {
                        $data['localidades'][$temp->id] = $temp->descripcion;
                    }
                }
            }
            
            if(!empty($filtro['locali'])) {
                $query = $this->db->query('SELECT id,descripcion FROM asu_arbol_segmentacion WHERE id_raiz=1 AND id_padre='.$filtro['locali'].' ORDER BY descripcion');
                $result = $query->result();

                if ($result){
                    foreach ($result as $temp) {
                        $data['unidades'][$temp->id] = $temp->descripcion;
                    }
                }
            }

            $data['registros'] = $this->Tableta_model->getAll($configPag['per_page'], $pag, $filtro);
            
            // Obtener la descripcion de cada unidad medica
            foreach ($data['registros'] as $registro) {
                if(!empty($registro->id_asu_um)) {
                    $unidad_medica = $this->ArbolSegmentacion_model->getDescripcionById(array($registro->id_asu_um), 2);
                    
                    if($unidad_medica) {
                        $data['unidades_medicas'][$unidad_medica[0]->id] = $unidad_medica[0]->descripcion;
                    }
                }
            }
        } catch (Exception $e) {
            $data['msgResult'] = Errorlog_model::save($this->Tableta_model->getMsgError(), __METHOD__);
            $data['clsResult'] = 'error';
        }
        
        $this->template->write('ajustaAncho', 1, true);
        $this->template->write_view('content',DIR_TES.'/tableta/index', $data);
		$this->template->render();
    }

    /**
     * Muestra el formulario para crear un nuevo registro en la tableta,
     * las variables se obtienen por el metodo POST
     *
     * @access public
     * @return void
     */
    public function insert()
    {
        if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url())) {
            show_error('', 403, 'Acceso denegado');
            return false;
        }
        
        if(!isset($this->Tableta_model))
            return false;

        try {
            $this->load->helper('form');

            $datos = $this->input->post();
            $data['title'] = 'Crear un nuevo registro';
            $data['msgResult'] = $this->session->flashdata('msgResult');
            $data['clsResult'] = $this->session->flashdata('clsResult');

            if(!empty($datos)) {
                $this->load->library('form_validation');

                $this->form_validation->set_rules('mac', 'MAC', 'trim|xss_clean|max_length[20]|required|callback__validateMac');

                if ($this->form_validation->run() === true) {
                    $this->Tableta_model->setMac($datos['mac']);
                    
                    $this->Tableta_model->insert();
                    
                    $this->session->set_flashdata('msgResult', 'Registro guardado exitosamente');
                    $this->session->set_flashdata('clsResult', 'success');

                    Bitacora_model::insert(DIR_TES.'::'.__METHOD__, 'Registro creado: '.$this->Tableta_model->getId());
                    //redirect(DIR_TES.'/tableta/', 'refresh');
                    //die();
                } else {
                    $this->session->set_flashdata('msgResult', validation_errors());
                    $this->session->set_flashdata('clsResult', 'error');
                }
            }
        } catch (Exception $e) {
            $data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
            $data['clsResult'] = 'error';
        }

        //$this->template->write_view('content',DIR_TES.'/tableta/insert', $data);
		//$this->template->render();
        redirect(DIR_TES.'/tableta/', 'refresh');
    }

    /**
     * Muestra el formulario con los datos del registro especificado por el id,
     * para actualizar sus datos
     *
     * @access public
     * @param  int    $id ID del elemento a actualizar
     * @return void
     */
    public function update($id)
    {
        if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url())) {
            show_error('', 403, 'Acceso denegado');
            return false;
        }

        if(!isset($this->Tableta_model))
            return false;

        try {
            $this->load->helper('form');
          
            $datos = $this->input->post();
            $data['title'] = 'Actualizar datos del registro';
            $data['registro'] = $this->Tableta_model->getById($id);
            
            // Cargar los datos a mostrar en el select de status
            $this->load->model( DIR_TES.'/Estado_tableta_model' );
            $estados_tableta = $this->Estado_tableta_model->getAll();
            
            $data['status'][0] = 'Elegir';
            foreach ($estados_tableta as $status) {
                $data['status'][$status->id] = $status->descripcion;
            }

            if(!empty($datos)) {
                $this->load->library('form_validation');

                $this->form_validation->set_rules('mac', 'MAC', 'trim|xss_clean|max_length[25]|required');
                
                if ($this->form_validation->run() === true) {
                    $this->Tableta_model->setMac($datos['mac']);
                    
                    if(isset($datos['status']))
                        $this->Tableta_model->setId_tes_estado_tableta($datos['status']);
                    
                    if(isset($datos['periodo_esq_inc']))
                        $this->Tableta_model->setPeriodo_esq_inc($datos['periodo_esq_inc']);

                    $this->Tableta_model->update($id);

                    Bitacora_model::insert(DIR_TES.'::'.__METHOD__, 'Registro actualizado: '.$id);
                    $this->session->set_flashdata('msgResult', 'Registro guardado exitosamente');
                    $this->session->set_flashdata('clsResult', 'success');
                    redirect(DIR_TES.'/tableta/', 'refresh');
                    die();
                }
            }
        } catch (Exception $e) {
            $data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
            $data['clsResult'] = 'error';
        }

        $this->template->write_view('content',DIR_TES.'/tableta/update', $data);
		$this->template->render();
    }

    /**
     * Muestra los datos del registro especificado por el id
     *
     * @access public
     * @param  int    $id ID del elemento a actualizar
     * @return void
     */
    public function view($id)
    {
        $usuarios = array();
        
        if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url())) {
            show_error('', 403, 'Acceso denegado');
            return false;
        }

        if(!isset($this->Tableta_model))
            return false;

        try {
            $this->load->model(DIR_TES.'/Usuario_tableta_model');
            
            $data['registro'] = $this->Tableta_model->getById($id);
            $data['title'] = 'Datos del registro';
            
            $this->load->helper('formatFecha');

            if( empty($data['registro']) ) {
                $data['msgResult'] = 'ERROR: El registro solicitado no existe';
                $data['clsResult'] = 'error';
            } else {
                $usuariosTableta = $this->Usuario_tableta_model->getUsuariosByTableta($id);

                if(!empty($usuariosTableta)) {
                    $this->load->model(DIR_TES.'/Usuario_model');

                    foreach ($usuariosTableta as $usuario) {
                        $infoUsuario = $this->Usuario_model->getById($usuario->id_usuario, true);
                        $usuarios[] = $infoUsuario->nombre_usuario;
                    }
                }
            }
            
            $data['usuarios'] = $usuarios;
        } catch (Exception $e) { 
            $data['msgResult'] = Errorlog_model::save($e->getMessage(), __METHOD__);
            $data['clsResult'] = 'error';
        }

        $this->template->write_view('content',DIR_TES.'/tableta/view', $data);
        $this->template->write('menu','',true);
        $this->template->write('sala_prensa','',true);
		$this->template->render();
    }

    /**
     * Eliminar el registro especificado por el id
     *
     * @access public
     * @param  int    $id ID del elemento a eliminar
     * @return void
     */
    public function delete($id)
    {
        if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url())) {
            show_error('', 403, 'Acceso denegado');
            return false;
        }
        
        if(!isset($this->Tableta_model))
            return false;
        
        try {
            $this->Tableta_model->delete($id);
            $this->session->set_flashdata('msgResult', 'Registro eliminado exitosamente');
            $this->session->set_flashdata('clsResult', 'success');
        } catch (Exception $e) {
            $this->session->set_flashdata('msgResult', Errorlog_model::save($this->Tableta_model->getMsgError(), __METHOD__));
            $this->session->set_flashdata('clsResult', 'error');
        }

        if (count($id) > 1)
        	Bitacora_model::insert(DIR_TES.'::'.__METHOD__, 'Registro eliminado: '.implode(',',$id));
        else
        	Bitacora_model::insert(DIR_TES.'::'.__METHOD__, 'Registro eliminado: '.$id);
        redirect(DIR_TES.'/tableta/', 'refresh');
        die();
    }
    
    /**
     * Valida si existe una MAC en la base de datos
     * 
     * @param type $mac
     * @return boolean
     */
    public function _validateMac($mac)
	{
        $result = $this->Tableta_model->getByMac($mac);
        
		if(!empty($result))
		{
			$this->form_validation->set_message('_validateMac', 'La direccion MAC ya esta registrada.');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
    
    /**
     * Registra tabletas desde un archivo csv
     * 
     * @return redirect
     */
    public function uploadFile()
	{
        if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url())) {
            show_error('', 403, 'Acceso denegado');
            return false;
        }
        
        $config['upload_path']   = 'application/updloads';
		$config['allowed_types'] = 'csv|txt|xls|xlsx';
		$config['max_size']      = '5120'; //5MB
        $config['overwrite']     = true;
        $errores = array();
        $msjErrores = '';
        
		$this->load->library('upload', $config);
        
        try {
            if ( !$this->upload->do_upload('archivo') ) {
                $this->session->set_flashdata('msgResult', $this->upload->display_errors());
                $this->session->set_flashdata('clsResult', 'error');
            } else {
                $archivo = $this->upload->data();
                $fichero = @fopen($archivo['full_path'], "r");

                if($fichero) {
                    while(($linea = fgets($fichero)) !== false) {
                        $linea = trim($linea);
                        $result = $this->Tableta_model->getByMac($linea);
        
                        if(!empty($result)) {
                            $errores[] = $linea;
                        } else {
                            $this->Tableta_model->setMac($linea);
                            $this->Tableta_model->insert();
                        }
                    }

                    if(!feof($fichero)) {
                        $this->session->set_flashdata('msgResult', 'Error al leer el archivo '.$archivo['file_name']);
                        $this->session->set_flashdata('clsResult', 'error');
                    } else {
                        if(!empty($errores)) {
                            $msjErrores = 'Las siguientes direccciones MAC ya estan registradas en el sistema: '.implode(', ', $errores).'.';
                        }
                        
                        $this->session->set_flashdata('msgResult', 'Datos registrados correctamente. '.$msjErrores);
                        $this->session->set_flashdata('clsResult', 'success');
                    }
                    fclose($fichero);
                } else {
                    $this->session->set_flashdata('msgResult', 'Error al leer el archivo '.$archivo['file_name']);
                    $this->session->set_flashdata('clsResult', 'error');
                }
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('msgResult', Errorlog_model::save($e->getMessage(), __METHOD__));
            $this->session->set_flashdata('clsResult', 'error');
        }
        
        redirect(DIR_TES.'/tableta/', 'refresh');
	}
    
    /**
     * Asignar unidad medica y tipo de censo
     * 
     * @param int $id
     * @return redirect
     */
    public function setUM($id)
    {
        if (!Usuario_model::checkCredentials(DIR_TES.'::'.__METHOD__, current_url())) {
            show_error('', 403, 'Acceso denegado');
            return false;
        }

        if(!isset($this->Tableta_model))
            return false;

        try {
            $datos = $this->input->post();
            $this->Tableta_model->getById($id);

            if(!empty($datos)) {
                $this->Tableta_model->setId_tipo_censo($datos['id_tipo_censo']);
                $this->Tableta_model->setId_asu_um($datos['id_unidad_medica']);
                $this->Tableta_model->setPeriodo_esq_inc($datos['periodo_esq_inc']);

                $this->Tableta_model->update($id);

                Bitacora_model::insert(DIR_TES.'::'.__METHOD__, 'Registro actualizado: '.$id);
                $this->session->set_flashdata('msgResult', 'Registro actualizado exitosamente');
                $this->session->set_flashdata('clsResult', 'success');
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('msgResult', Errorlog_model::save($e->getMessage(), __METHOD__));
            $this->session->set_flashdata('clsResult', 'error');
        }
        
        redirect(DIR_TES.'/tableta/', 'refresh');
    }
}
?>