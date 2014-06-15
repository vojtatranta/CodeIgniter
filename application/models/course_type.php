<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Course_type extends Basemodel {

	public $title = '';

	protected $field_settings = array(
									'title' => array('rules' => array('required'))
								);

	public function __construct()
	{
		parent::__construct();


		$this->sync_db();
	}

	public function get_unicode()
	{
		return $this->title;
	}

	

}

/* End of file course_type.php */
/* Location: ./application/models/course_type.php */