<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Newmodel extends Basemodel {
	
	public $name;
	public $created;

	protected static $fields_settings = array(
		'name'    => array('type' => 'varchar', 'rules' => array('required')),
		'created' => array('type' => 'datetime')
	);

	public function __construct()
	{
		parent::__construct();
		
		$this->_sync_db();
	}
	

}

/* End of file newmodel.php */
/* Location: ./application/models/newmodel.php */