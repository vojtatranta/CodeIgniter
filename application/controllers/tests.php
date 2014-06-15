<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tests extends CI_Controller {

	public function index()
	{
		$this->load->library('unit_test');
		$this->unit->use_strict(TRUE);

		$course = Course::get(3)->row();

		$this->unit->run($course instanceof Course AND $course->id == 3, True, 'Testing get method ');

		$false_course = Course::get(0)->row();

		$this->unit->run($false_course, False, 'Trying get course that does not exists');

		$lector = $course->relation('lector');
		$this->unit->run($lector instanceof Lector AND isset($lector->id), True, 'Relation with referencing column');

		$courses_of_lector = $lector->relation('course');
		$test = is_array($courses_of_lector) AND !empty($courses_of_lector) AND $courses_of_lector[0] instanceof Course;
		$this->unit->run($test, True, 'Testing other direction relation');

		$courses_times = $course->relation('course_time');
		$test = empty($courses_times);
		$this->unit->run($test, True, 'Testing M:N relation');

		$this->unit->run($course->relation('fsdfsdf'), false, 'Testing relation to not existing model');


 
		echo $this->unit->report();
	}

}

/* End of file tests.php */
/* Location: ./application/controllers/tests.php */