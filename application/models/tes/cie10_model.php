<?php

/**
 * Modelo Cie10
 *
 * @author     Geovanni
 * @created    2013-12-02
 */
class Cie10_model extends CI_Model {

    
    	/**
	 * @access private
	 * @var    int
	 */
   	private $id;

   	/**
   	 * @access private
   	 * @var    string
   	 */
   	private $descripcion;
        
	/**
	 * @access private
	 * @var    string
	 */
   	private $msg_error_log;

   	/**
   	 * @access private
   	 * @var    string
   	 */
   	private $msg_error_usr;


	/**
	 * @access private
	 * @var    int
	 */
	private $offset;

	/**
	 * @access private
	 * @var    int
	 */
	private $rows;      

	public function setOffset($value) {
		$this->offset = $value;
	}
	public function setRows($value) {
		$this->rows = $value;
	}
        
           	/***************************/
	/*Getters and setters block*/
   	/***************************/
   	public function getId() {
		return $this->id;
	}

	public function setId($value) {
		$this->id = $value;
	}

	public function getDescripcion() {
		return $this->descripcion;
	}

	public function setDescripcion($value) {
		$this->descripcion = $value;
	}
        
       	/*******************************/
	/*Getters and setters block END*/
	/*******************************/

        
	/**
	 * Devuelve los mensajes de error en caso de ocurrir alguna excepción
	 * 'usr' devuelve el mensaje para la vista de usuario
	 * 'log' devuelve el mensaje para el log de errores
	 *
	 * @access  public
	 * @return  string|boolean
	 *  @param  string $value, default 'usr' (Tipo mensaje)
	 */
	public function getMsgError($value = 'usr')
	{
		if (!empty($this->msg_error_usr))
		{
			if ($value == 'usr')
				return $this->msg_error_usr;
			else if ($value == 'log')
				return $this->msg_error_log;
		}
		else
		{
			return false;
		}
	}

	public function __construct()
	{
		$this->load->database();
		if(!$this->db->conn_id)
		{
			throw new Exception("No se pudo conectar a la base de datos");
		}
	}

        	/**
	 *Devuelve el numero de registros
	 *
	 *@access  public
	 *@return  int
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function getNumRows()
	{
		$query = $this->db->query('select count(*) as num from cns_cie10');
		if (!$query)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al obtener los datos del catalogo cie10";
			throw new Exception(__CLASS__);
		}
		else
			return $query->row()->num;
	}
        
        
	/**
	 *Devuelve una lista con los registros existentes en el catalogo cie10
	 *
	 *@access  public
	 *@return  ArrayObject
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function getAll()
	{
                $consulta = 'select a.id as id,a.cie10 as cie10,a.descripcion as descripcion,case when ifnull(b.id,"") = "" then false else true end as eda,case when  ifnull(c.id,"") = "" then false else true end as ira,case when  ifnull(d.id,"") = "" then false else true end as consulta from cns_cie10 a left outer join cns_eda b on b.id_cie10 = a.id left outer join cns_ira c on c.id_cie10 = a.id left outer join cns_consulta d on d.id_cie10 = a.id';
		if ((!empty($this->offset) || $this->offset == 0) && !empty($this->rows))
		$consulta .= ' limit '.$this->offset. ','.$this->rows;
                
                $datos = $this->db->query($consulta);
                
                if (!$datos)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al obtener los datos del catalogo cie10";
			throw new Exception(__CLASS__);
		}
		else
                {
                    return $datos->result();
                }
	}

	/**
	 *Devuelve una lista con los registros existentes en el catalogo cie10 omitiendo los ID
	 *
	 *@access  public
	 *@return  ArrayObject
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function getData()
	{
                $consulta = 'select cie10,descripcion from cns_cie10';                
                $datos = $this->db->query($consulta);
                
                if (!$datos)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al obtener los datos del catalogo cie10";
			throw new Exception(__CLASS__);
		}
		else
                {
                    return $datos->result();
                }
	}
        
	/**
	 *Devuelve una lista con los registros existentes en el catalogo requerido
	 *
	 *@access  public
	 *@return  ArrayObject
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function getCatalogoByName($cat)
	{
                $consulta = 'select * from cns_'.$cat;  
                $datos = $this->db->query($consulta);
                
                if (!$datos)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al obtener los datos del catalogo cie10";
			throw new Exception(__CLASS__);
		}
		else
                {
                    return $datos->result();
                }
	}
        
        
	/**
	 *Devuelve la información de un registro del catalogo cie10 por su ID
	 *
	 *@access  public
	 *@return  Object
	 *@param   int $id ID (Llave primaria)
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function getById($id)
	{
		$query = $this->db->get_where('cns_cie10', array('id' => $id));

		if (!$query)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al obtener la información del registro cie10";
			throw new Exception(__CLASS__);
		}
		else
			return $query->row();
	}
        
        /**
         * Accion para agregar registros del CIE10 a otros catalogos como el de EDA, IRA y Consultas
         * @param type $id el id del registro en el catalogo cie10
         * @param type $catalogo nombre del catalogo donde se realizara la operacion
         * @param type $valor agregar o eliminar el registro del catalogo
         * @return boolean como el resultado de la operación
         * @throws Exception Si ocurre algun error al consultar y modificar la base de datos
         */
        
        public function agregaEnCatalogo($id,$catalogo,$valor)
        {   
            if ($valor == 1)
            {
                $consulta = "insert into ".$catalogo." (id_cie10,descripcion,activo) values (".$id.",(select descripcion from cns_cie10 where id='".$id."'),true)";
            }
            else
            {
                $consulta = "delete from ".$catalogo." where id_cie10=".$id;
            }
            
                $datos = $this->db->query($consulta);
                
                if (!$datos)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al obtener los datos del catalogo cie10";
			throw new Exception(__CLASS__);
		}
		else
                {
                    return true;
                }
        }
        	
	/**
	 *Devuelve los datos de un catalogo pasado como parametro
	 *
	 *@access  public
	 *@return  ArrayObject
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function getAllData($nombrecat)
	{
		$catalogos = $this->db->query('select * from '.$nombrecat);

		if (!$catalogos)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al obtener los datos de los catálogos";
			throw new Exception(__CLASS__);
		}
		else
		return $catalogos->result();
	}

	/**
	 *Devuelve la informacion de un catalogo por su nombre
	 *
	 *@access  public
	 *@return  Object
	 *@param   string $nombre (Nombre del catalogo)
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function getByName($nombre)
	{
		$result = array(
			'nombre' => $nombre
		);
		$tblcampos = $this->db->query('describe '.$this->db->database.'.'.$nombre);
		if (!$tblcampos)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al obtener los datos de los catálogos";
			throw new Exception(__CLASS__);
		}
		else
		{
			$campos = '';
			$llave = '';
			foreach ($tblcampos->result() as $campo)
			{
				if ($campo->Key == '')
					$campos .= $campo->Field.'|'.$campo->Type.'|'.$campo->Null.'|NO||';
				else
					$llave .= $campo->Field.'|'.$campo->Type.'|'.$campo->Null.'|YES||';
			}
			$result['campos'] = substr($campos, 0,count($campos)-3);;

			$result['llave'] = substr($llave, 0,count($llave)-3);
		}

			return (object)$result;
	}
	
	/**
	 *Revisa en la base de datos por registros duplicados en los campos pasados por parametro
	 *
	 * @access public
	 *@param   string $campo (varios campos delimitados por | )
	 * @return boolean (Si no hubo errores al eliminar)
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function checkPk($campo)
	{
		$campo = urldecode($campo);
		$campos = explode('|',$campo);
		$querycampos  = "";
			
		foreach($campos as $c)
			$querycampos .= $c.', ';
		
		$consulta = "select ".$querycampos." count(*) from tmp_catalogo group by ".substr($querycampos,0,strlen($querycampos)-2)." having count(*) > 1";
		$query = $this->db->query($consulta);
		
		//var_dump($consulta);

		if (!$query)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al eliminar el catálogo";
			throw new Exception(__CLASS__);
		}
		else
			return $query->result();
	}

	/**
	 *Inserta en la base datos el catálogo y obtiene los datos
	 *de la tabla temporal
	 *
	 *@access  public
	 *@param string $create (la consulta para crear el catalogo)
	 *@param string $select (la consulta para extraer datos de la tabla tmp_catalogo)
	 *@return  boolean (si la insercion es correcta)
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function insert($create,$select)
	{

		$query = $this->db->query($create);

		if (!$query)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al insertar el catalogo";
			throw new Exception(__CLASS__);
		}
		else
		{
			$query = $this->db->query($select);

			if (!$query)
			{
				$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
				$this->msg_error_usr = "Ocurrió un error al insertar el catálogo";
				throw new Exception(__CLASS__);
			}
			else
				return true;
		}
	}

	/**
	 *Actualiza el objeto actual en la base de datos
	 *
	 *@access  public
	 *@return  boolean (Si no hubo errores al actualizar)
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function update()
	{
		$data = array('descripcion' => $this->descripcion);

		$this->db->where('id' , $this->getId());
		$query = $this->db->update('cns_cie10', $data);

		if (!$query)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al actualizar los datos del catálogo";
			throw new Exception(__CLASS__);
		}
		else
			return true;
	}

	/**
	 * Elimina el registro actual de la base de datos
	 *
	 * @access public
	 * @return boolean (Si no hubo errores al eliminar)
	 * @throws Exception En caso de algun error al consultar la base de datos
	 */
	public function delete()
	{

		$query = $this->db->query('drop table '.$this->nombre);

		if (!$query)
		{
			$this->msg_error_log = "(". __METHOD__.") => " .$this->db->_error_number().': '.$this->db->_error_message();
			$this->msg_error_usr = "Ocurrió un error al eliminar el catálogo";
			throw new Exception(__CLASS__);
		}
		else
			return true;
	}
}