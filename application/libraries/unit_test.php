<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Unit_test extends CI_Unit_test
{
  protected 	$ci;

	public function __construct()
	{
        $this->ci =& get_instance();
	}

	function run($test, $expected = TRUE, $test_name = 'undefined', $notes = '')
	{
		if ($this->active == FALSE)
		{
			return FALSE;
		}

		if (in_array($expected, array('is_object', 'is_string', 'is_bool', 'is_true', 'is_false', 'is_int', 'is_numeric', 'is_float', 'is_double', 'is_array', 'is_null'), TRUE))
		{
			$expected = str_replace('is_float', 'is_double', $expected);
			$result = ($expected($test)) ? TRUE : FALSE;
			$extype = str_replace(array('true', 'false'), 'bool', str_replace('is_', '', $expected));
		}
		else
		{
			if ($this->strict == TRUE)
				$result = ($test === $expected) ? TRUE : FALSE;
			else
				$result = ($test == $expected) ? TRUE : FALSE;

			$extype = gettype($expected);
		}

		$back = $this->_backtrace();

		$report[] = array (
							'test_name'			=> $test_name,
							'test_datatype'		=> gettype($test),
							'res_datatype'		=> $extype,
							'result'			=> ($result === TRUE) ? 'passed' : 'failed',
							'returned value'	=> $test,
							'expected value'	=> $expected,
							'file'				=> $back['file'],
							'line'				=> $back['line'],
							'notes'				=> $notes
						);

		$this->results[] = $report;

		return($this->report($this->result($report)));
	}

	

}

/* End of file unit_test extends CI_Unit_test.php */
/* Location: ./application/libraries/unit_test extends CI_Unit_test.php */
