<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Student_course extends Basemodel {

	public $course = 0;
	public $student = 0;
	public $status = 0;
	public $credit = '';
	public $tag = '';
	public $note = '';

	protected $field_settings = array(
			'status' => array('widget' => 'select'),
			'tag' => array('widget' => 'select'),
			'note' => array('widget' => 'textarea'),
			'course' => array('rules' => array('required')),
			'student' => array('type' => 'student', 'rules' => array('required'))
		);

	public function __construct()
	{
		parent::__construct();

		$this->field_settings['status']['options'] = $this->config->item('attend_statuses');
		$this->field_settings['tag']['options'] = $this->config->item('student_course_tags');

		$this->clear_nonsenses();

		$this->sync_db();
	}


	public function get_unicode()	
	{
		$student = $this->relation('students');
		$course = $this->relation('course');

		if (!$student OR !$course) return '';

		return $student->get_unicode() . '-' . $course->get_unicode();
	}


	public function clear_nonsenses()
	{
		$this_table = $this->model_to_table_name($this);
	}
}

/* End of file student_course.php */
/* Location: ./application/models/student_course.php */