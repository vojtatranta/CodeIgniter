<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Textbook extends Basemodel {

	public $title = '';

	public function __construct()
	{
		parent::__construct();
		
		$this->sync_db();
	}	

}

/* End of file textbook.php */
/* Location: ./application/models/textbook.php */