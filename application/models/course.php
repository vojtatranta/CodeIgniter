<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Course extends Basemodel {

	public $title = '';
	public $course_month = 0;
	public $course_type = 0;
	public $course_time = array();
	public $from = '';
	public $to = '';
	public $lector = 0;
	public $place = '';
	public $classroom = 0;
	public $textbook = 0;

	protected $field_settings = array(
			'from' => array('widget' => 'autodate'),
			'to'   => array('widget' => 'autodate'),
			'course_time' => array('type' => 'course_time',	'widget' => 'selects', 'multiple' => true, 'count' => 10),
			'title' => array('rules' => array('required')),
			'course_month' => array('rules' => array('required')),
			'course_type' => array('rules' => array('required')),
			'lector' => array('rules' => array('required')),
			'classroom' => array('rules' => array('required')),
			'textbook' => array('rules' => array('required')),
		);
	
	public function __construct()
	{
		parent::__construct();
			
		$this->field_settings['status']['options'] = $this->config->item('course_statuses');
			
		$this->sync_db();
	}


	public function get_unicode()
	{
		$months = $this->basemodel->retrieve_class('course_month')->months();
		$days = $this->basemodel->retrieve_class('course_time')->get_day_options();

		$time_str = ' ';
		$times = $this->relation('course_time');

		if ($times)
		{
			foreach($times as $time)
			{
				$time_str .= substr($days[$time->day],0,3);
				$time_str .= " {$time->time}, ";
			}
		}

		$classroom_str = ' ';
		if ($classroom = $this->relation('classroom'))
		{
			$classroom_str .= $classroom->title;
		}
		
		$month_str = ' ';

		if ($month = $this->relation('course_month'))
		{
			$month_str .= $months[$month->month];
			$month_str .= ' '.$month->year;
		}

		return $this->title.$time_str.$classroom_str.$month_str;
	}

	public function count_students()
	{
		return count($this->relation('student_course'));
	}
	

}

/* End of file course.php */
/* Location: ./application/models/course.php */