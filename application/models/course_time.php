<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Course_time extends Basemodel {

	public $day = 0;
	public $time = '';
	public $to = '';

	protected $field_settings = array(
				'day' => array('widget' => 'select', 'rules' => array('required')),
				'time' => array('widget' => 'select', 'rules' => array('required')),
				'to' => array('widget' => 'select', 'rules' => array('required')),
		);

	public function __construct()
	{
		parent::__construct();
		
		$this->field_settings['day']['options']  = $this->get_day_options();
		$this->field_settings['time']['options'] = $this->get_time_options();
		$this->field_settings['to']['options']   = $this->get_time_options();

		$this->sync_db();
	}

	public function get_duration($precision = 5)
	{
		$diff = strtotime($this->to) - strtotime($this->time);

		return $diff / 60 / $precision;
	}

	public function get_unicode()
	{
		return ucfirst($this->czech_day($this->day)) . '  ' . date('H:i', strtotime($this->time)). '-' . date('H:i', strtotime($this->to));
	}

	public function czech_day($i)
	{
		static $days = array( 1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
    	return isset($days[$i]) ? $days[$i] : '';
	}
	
	public function get_day_options()
	{
		$opts = array();
		for ( $i = 1; $i <= 7; $i++ )
		{
			$opts[$i] = $this->czech_day($i);
		}

		return $opts;
	}

	public function get_time_options()
	{
		$opts = array();
		$hours = 6;
		$mins = 0;
		for ( $i = 0; $i <= 287; $i++ )
		{ 

			$mins = $mins + 5;
			if ( $mins % 60 == 0 AND $mins != 0)
			{
				$hours++;
				$mins = 0;
			}

			$hours_disp = str_pad($hours, 2, '0', STR_PAD_LEFT);

			if ( $hours_disp == '24' ) $hours_disp = '00';

			$mins_disp = str_pad($mins, 2, '0', STR_PAD_LEFT);

			$opts["$hours_disp:$mins_disp"] = "$hours_disp:$mins_disp";

			if ( $hours == 24 ) break;
		}

		return $opts;
	}

}

/* End of file time.php */
/* Location: ./application/models/time.php */