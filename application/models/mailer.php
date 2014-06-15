<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mailer extends CI_Model {



	function send_email($address, $template, $subject, $lang, $email_data = array())
	{	
		$template_file = 'email/'.$lang.'/'.$template;

		$mail_text = $this->load->view($template_file, $email_data, True);

		if ( !$mail_text ) 
		{
			return false;
		}
		$this->load->library('email');

		$config['mailtype'] = 'html';
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = 'posta2.vshosting.cz';
		$config['smtp_user'] = 'jidelniplan@mailserver.abuco.cz';
		$config['smtp_pass'] = 'sdf55sdfSDd5.sd';
		$config['smtp_port'] = 25;

		$this->email->initialize($config);


		$this->email->from($this->config->item('site_email'), $this->config->item('app_name'));
		$this->email->to($address); 

		$this->email->subject($subject);
		$this->email->message($mail_text );	

		$this->email->send();
		
			

		return true;
	}

}

/* End of file mailer.php */
/* Location: ./application/models/mailer.php */