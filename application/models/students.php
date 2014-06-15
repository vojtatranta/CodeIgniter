<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Students extends Basemodel {

	public $firstname = '';
	public $familyname = '';

	public function __construct()
	{
		parent::__construct();
		
	}

	public function get_unicode()
	{
		return $this->fullname();
	}

	public function fullname()
	{
		return $this->firstname .' '. $this->familyname;
	}

}

/* End of file student.php */
/* Location: ./application/models/student.php */