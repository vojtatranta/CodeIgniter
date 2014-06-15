<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lector extends Basemodel {

	public $name = '';

	protected $field_settings = array(
								'name' => array('rules' => array('required'))
							);

	public function __construct()
	{
		parent::__construct();
		
		$this->sync_db();
	}

	public function get_unicode()
	{
		return $this->name;
	}


	public function get_schedule($month, $year)
	{
		$month = 7;
		$courses = $this->course_month->get(array('month' => $month, 'year' => $year))->row()->relation('course', array('lector' => $this->id));
		
		$ret = array();				
		foreach ($courses as $course) 
		{	
			$times = $course->relation('course_time');

			foreach($times as $time)
			{
				$cl = clone $course;
				$cl->course_time = $time;
				$ret[$time->day][$time->time][] = $cl;
			}
		}

		return $ret;
	}

}

/* End of file lector.php */
/* Location: ./application/models/lector.php */