<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Flashmanager
{
  	protected $ci;
  	private $messages = array();
  	private $statuses = array('success', 'error', 'info');
  	private $flash_template = 'flash/basic_flash_messages';
  	private $session_flash_name = 'ci_flash';

  	public $flash_duration = 2000; //default is 5000

	public function __construct()
	{
        $this->ci =& get_instance();
        $this->ci->load->helper('translate');
        $this->ci->load->helper('util');
        $this->ci->load->helper('url');

        if ( $messages = $this->ci->session->flashdata($this->session_flash_name) )
      	{
      		$this->messages = $messages;
      	}
      	
      	if ( $form_errors = $this->ci->session->flashdata('form_errors') )
      	{
      		$this->ci->session->set_flashdata('flash_duration', $this->flash_duration);
      		$this->messages[] = array('content' => $form_errors, 'status' =>'error');
      	}
	}

	public function set_msg($msg_text, $msg_status = 'success', $msg_template = '', $flash_duration = null)
	{	
		if ( !is_null($flash_duration) )
		{
			$this->flash_duration = $flash_duration;
		}

		$this->ci->session->set_flashdata('flash_duration', $this->flash_duration);

		$this->messages[] = array('content' => $msg_text, 'status' => $msg_status);
		$this->ci->session->set_flashdata( $this->session_flash_name, $this->messages );

		return $msg_text;
	}	


	public function print_msgs()
	{
		$view_data['messages'] = $this->messages;
		$view_data['duration'] = $this->flash_duration;


		if ( $view_data['messages'] )
		{
			$this->ci->load->view($this->flash_template, $view_data);
		}
	}

	public function has_msgs( $which = '')
	{
		if ( empty($which) )
		{
			return !empty($this->messages);
		}

		$has_messages = false;
		foreach( $this->messages as $msg )
		{
			if ( $msg['status'] == $which ) 
			{
				$has_messages = true;
				break;
			}
		}

		return $has_messages;
	}

	private function _remove_flash()
	{
		$this->ci->session->set_userdata( array($this->session_flash_name => array()) );
	}


}

/* End of file flashmanager.php */
/* Location: ./application/libraries/flashmanager.php */
