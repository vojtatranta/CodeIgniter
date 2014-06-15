<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Unit_test extends CI_Unit_test
{
  protected 	$ci;

	public function __construct()
	{
		parent::__construct();
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

	function result($results = array())
	{
		$CI =& get_instance();
		$CI->load->language('unit_test');

		if (count($results) == 0)
		{
			$results = $this->results;
		}

		$retval = array();
		foreach ($results as $result)
		{
			$temp = array();
			foreach ($result as $key => $val)
			{
				

				if (is_array($val))
				{
					foreach ($val as $k => $v)
					{
						if (FALSE !== ($line = $CI->lang->line(strtolower('ut_'.$v))))
						{
							$v = $line;
						}
						$temp[$k] = $v;
					}
				}
				else
				{
					if (FALSE !== ($line = $CI->lang->line(strtolower('ut_'.$val))))
					{
						$val = $line;
					}
					$temp[$key] = $val;
				}
			}

			$retval[] = $temp;
		}

		return $retval;
	}

	function report($result = array())
	{
		if (count($result) == 0)
		{
			$result = $this->result();
		}

		$CI =& get_instance();
		$CI->load->language('unit_test');

		$this->_parse_template();

		$r = '';

		foreach ($result as $res)
		{
			$table = '';

			foreach ($res as $key => $val)
			{
				

					if ($val == $CI->lang->line('ut_passed'))
					{
						$val = '<span style="color: #0C0;">'.$val.'</span>';
					}
					elseif ($val == $CI->lang->line('ut_failed'))
					{
						$val = '<span style="color: #C00;">'.$val.'</span>';
					}
				

				$temp = $this->_template_rows;
				$temp = str_replace('{item}', $key, $temp);
				$temp = str_replace('{result}', $val, $temp);
				$table .= $temp;
			}

			$r .= str_replace('{rows}', $table, $this->_template);
		}

		return $r;
	}
	

}

/* End of file unit_test extends CI_Unit_test.php */
/* Location: ./application/libraries/unit_test extends CI_Unit_test.php */
