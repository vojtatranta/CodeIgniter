<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Course_month extends Basemodel {
	
	public $month = 0;
	public $year = 0;

	protected $field_settings = array(
				'month' => array('widget' => 'select', 'rules' => array('required')),
				'year' => array('widget' => 'select', 'rules' => array('required')),

		);

	public function __construct()
	{
		parent::__construct();
		
		$this->field_settings['month']['options'] = $this->months();

		$this->year = date('Y');
		$this->field_settings['year']['options'] = $this->current_years();

		$this->sync_db();
	}

	public function get_unicode()
	{ 
		if (!$this->is_valid()) return '';
		return $this->field_settings['month']['options'][$this->month].' '.$this->year;
	}




	public function copy_in($to, $attend_status)
	{
		$courses = $this->relation('course');



		if (!$courses) return false;

		foreach($courses as $k => $course)
		{
			$students_conn = $course->relation('student_course');
			$course_times  = $course->relation('course_time');


			$course = clone $course;
			unset($course->id);

			$course->course_month = $to->id;

			$course->save();	

			if ( $students_conn )
			{
				foreach ($students_conn as $conn) 
				{
					$conn = clone $conn;
					unset($conn->id);	

					$conn->course = $course->id;
					$conn->status = $attend_status;
					$conn->save();
				}	

				$course->save();
			}	
		}
	}

	public function months()
	{
		static $months = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
   		
   		$ret = array();	
   		for ($i = 1; $i <= 12; $i++)
   		{
   			$ret[$i] = $months[$i];
   		}	
   		
   		return $ret;
	}

	public function current_years()
	{
		$ret = array();
		for ($i = date('Y') - 1; $i < date('Y') + 5; $i++)
		{
			$ret[$i] = $i;
		}

		return $ret;
	}

}

/* End of file course_month.php */
/* Location: ./application/models/course_month.php */