<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends Basemodel {

	public $email = '';
	public $password = '';

 
	protected $field_settings = array('password' => array('widget' => 'password')
		                            );

	public function __construct()
	{
		parent::__construct();
		
		$this->field_settings['physical_activity']['options'] = $this->config->item('physical_activity');

		$this->sync_db();
	}

	
	function authenticate( $email, $password )
	{
		$this_table = $this->model_to_table_name($this);

		$row = $this->db->where('email', $email)->get($this_table)->row();
		$password = $this->calculate_hash( $password, $row->password );

		$user_row = $this->db->where(array('email' => $email, 'password' => $password))->get( $this_table )->row();

		if ( !empty($user_row) )
		{
			$this->init_props( $user_row );
			return $this;
		}
		else
		{
			return false;
		}
	}	

	function can($perm_name, $model)
	{
		$group = $this->relation('usergroup');
		$permissions = $group->relation('permission');

		foreach($permissions as $perm)
		{
			if ( strtolower($perm->title) == $perm_name AND strtolower($perm->model) == $model ) return true;
		}

		return false;		
	}
	
	
	function is_email_used()
	{
		$this_table = $this->model_to_table_name($this);
		return $this->db->from($this_table)->where('email', $this->email)->count_all_results();
	}

	function passwords_not_match( $password_match )
	{
		return ( $this->password != $password_match );
	}

	function register_form( $action )
	{
		$this->load->library('ci_form');

		$form = $this->ci_form;

		$form->create_form($action);

		$div = $form->create_element('div', 'field-wrap');
			$input = $form->create_input('email', 'email', t('email'));
			$input->rules[] = 'required';
			$input->rules[] = 'valid_email';
		$form->end_element( $div );

		$div = $form->create_element('div', 'field-wrap');
			$input = $form->create_input('password', 'password', t('password'));
			$input->rules[] = 'required';
			$input->rules[] = 'min_length[5]';
			$input->rules[] = 'max_length[15]';
		$form->end_element( $div );

		$div = $form->create_element('div', 'field-wrap');
			$input = $form->create_input('password', 'password_again', t('password_again'));
			$input->rules[] = 'required';
			$input->rules[] = 'min_length[5]';
			$input->rules[] = 'max_length[15]';
		$form->end_element( $div );

		$form->label_postfix_text = '';

	

		$div = $form->create_element('div', 'field-wrap submit-area' );

		$submit = $form->create_input('submit', '', '');
				$submit->attributes['value'] = t('register');
				$submit->classes[] = 'btn large btn-success';

		$form->end_element( $div );

		return $form->assemble();
	}

	

	function forgot_form( $action )
	{
		$this->load->library('ci_form');

		$form = $this->ci_form;

		$form->create_form($action);

		$div = $form->create_element('div', 'field-wrap');
			$email = $form->create_input('email', 'email', t('your_email'));
			$email->rules[] = 'required';
		$form->end_element( $div );

		$div = $form->create_element('div', 'field-wrap submit-area' );

		$submit = $form->create_input('submit', '', '');
				$submit->attributes['value'] = t('send_new_password');
				$submit->classes[] = 'btn large btn-success';

		$form->end_element( $div );

		return $form->assemble();
	}

	function hook_presave()
	{
		$this->password = $this->calculate_hash($this->password);
	}
	

	function register_user()
	{
		$this->password = $this->calculate_hash($this->password);

		$this->save();

		$this->execute_login($this->id);
	}

	function execute_login( $user_id )
	{
		$this->session->set_userdata( array('is_logged_in' => 1, 'user_id' => $user_id) );

		if ( $program = $this->session->userdata('program') )
		{
			$user = $this->get_logged_user();

			$user->program = $program;
			$user->gender = $this->session->userdata('gender');
			$user->save();
		}


		$this->update_session();
	}

	function execute_logout( )
	{
		$this->session->sess_destroy();
	}


	function get_logged_user()
	{
		if ( !$this->is_logged_in() ) return false;

		$user_id = $this->id_of_logged_user();

		$this_table = $this->basemodel->model_to_table_name($this);

		$user = $this->db->where('id', $user_id)->get($this_table)->row(0, $this_table);

		return empty($user) ? false : $user;
	}

	function id_of_logged_user()
	{
		return $this->session->userdata('user_id');
	}

	function update_session()
	{
		$sess_arr = array();
		foreach( $this as $prop => $val )
		{
			if ( !is_array($val) ) $sess_arr[$prop] = $val;
		}

		unset($sess_arr['password']);
		unset($sess_arr['email']);
		unset($sess_arr['id']);
		
		$this->session->set_userdata( $sess_arr );
	}

	function is_logged_in()
	{
		return ($this->session->userdata('is_logged_in') AND $this->session->userdata('user_id'));
	}



	function calculate_hash($password, $salt = NULL)
	{		
		return crypt($password, $salt ?: '$2a$07$' . $this->random_string(22));
	}
	
	function random_string($length = 20) 
	{
         return substr(sha1(rand()), 0, $length);
    }


    /**
	*	Gets user's prefered language from Session or HTTP headers
	*	
	*	@return String
	**/
	function get_user_lang()
	{
		$static_lang = $this->config->item('static_site_lang');
		$supported_langs = $this->config->item('supported_langs');

		if ( $static_lang AND !empty($static_lang) )
		{
			$this->session->set_userdata( array('lang' => $static_lang) );
		}

		if ( !$this->session->userdata('lang') )
		{
			$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

			$array = array('lang' => 'cs'	);
			$this->session->set_userdata( $array );

			
			if ( !in_array($lang, $supported_langs) ) $lang = $supported_langs[0];

			return $lang;
		}
		else
		{
			$lang = $this->session->userdata('lang');

			if ( !in_array($lang, $supported_langs) )
			{
				$lang = $supported_langs[0];
				
				$this->session->set_userdata( array('lang' => 'cs') );
			} 

			$this->lang = $this->session->userdata('lang');
			return $lang;
		}
	
	}

}

/* End of file userModel.php */
/* Location: ./application/models/userModel.php */