<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends CI_Controller {

	public $site_lang;
	public $page_title = 'Magnazone | Login';
	public $section = 'auth';
	public $parent;

	public function __construct()
	{
		parent::__construct();		
	}

	public function login( $back_uri = null )
	{	

		if ( $this->user->is_logged_in() ) redirect('');

		$this->section = 'login';


		$view_data['back_uri'] = ( !is_null($back_uri) ) ? $back_uri : '' ;
		$view_data['content'] = $this->load->view('login_form', $view_data, True);
	
		$this->load->view('admin/simple_view', $view_data);
	}

	public function process_login()
	{
		$post = $this->input->post();

		if ( empty($post) )
		{
			redirect('');
		}

		$username = $post['email'];
		$password = $post['password'];

		$user = $this->user->authenticate( $username, $password );


		if ( !empty($user) )
		{
			$this->flashmanager->set_msg(t('welcome'));
			$this->user->execute_login( $user->id );
			
			if ( empty($post['back_uri']) )
			{
				redirect('');
			}
			else
			{
				redirect( $post['back_uri'] );
			}
		} 
		else
		{
			$this->flashmanager->set_msg(t('email_or_password_wrong'), 'error');
			redirect('auth/login');
		}
		
	}

	public function logout()
	{
		$this->user->execute_logout();
	
		redirect(base_url().'?out=true');
	}

	public function register()
	{
		if ( $this->user->is_logged_in() ) redirect('profile');
		$view_data['page_heading'] = t('registration');
		
		$view_data['content'] = $this->user->register_form('auth/process_register');
		$view_data['content'] .= 


		$this->load->view('admin/simple_view', $view_data);
	}

	public function process_register($form = null)
	{
		if ( !is_null($form) )
		{
			$form_values = $form->get_values();

			$this->user->init_props( $form_values );

			$msgs = array();

			if ( $this->user->is_email_used() )
			{
				$msgs[] = $this->flashmanager->set_msg(t('e-mail_in_use'), 'error');
			}
			if ( $this->user->passwords_not_match($form_values['password_again']) )
			{
				$msgs[] = $this->flashmanager->set_msg(t('passwords_not_match'), 'error');
			}


			if ( !empty($msgs) )
			{
				redirect('auth/register');
			}

			$this->flashmanager->set_msg(t('welcome'));
			$res = $this->user->register_user();	

			redirect('profile');
		}

		redirect('');
	}

	public function forgot()
	{
		$view_data['content'] = $this->user->forgot_form('auth/process_forgot');
		$view_data['pageClass'] = "forgot";
		$view_data['page_heading'] = t("forgotten_password");
		$this->load->view('simple_view', $view_data);
	}

	public function process_forgot($form = null)
	{
		$post = $this->input->post();		

		if ( !is_null($form) )
		{
			$form_values = $form->get_values();

			$email = $form_values['email'];

			$user = $this->user->get( array('email' => $email) );
			if ( empty($user[0]->email) )
			{
				$this->flashmanager->set_msg(t('not_registered_email'), 'error');
				redirect('auth/forgot');
			}

			$rand_string = $this->user->random_string();
				
			$new_password = $this->user->random_string(6);
			
			$user[0]->password = $user[0]->calculate_hash($new_password);
			$user[0]->save();

			$email_data['new_password'] = $new_password;
			$email_data['email'] = $email;

			$lang = $this->session->userdata('lang');

			if ($this->mailer->send_email($email, 'forgot_email',t('forgotten_password'), $lang, $email_data) )
			{
				$this->flashmanager->set_msg(t('new_password_sent'));
				redirect('auth/login');
			}
			else
			{
				$this->flashmanager->set_msg(t('email_send_error'), 'error');
				redirect('auth/forgot');
			}

		}

		redirect('auth/forgot');

	}


	function forgot_pass( $hash = null )
	{
		if ( is_null($hash) )
		{
			$this->flashmanager->set_msg( t('invalid_link'), 'error');
			redirect('auth/login');
		}

		$forgot_arr = $this->session->userdata('forgot');


		if ( !$forgot_arr OR !isset($forgot_arr[$hash])  )
		{
			$this->_destroy_forgot( );
		}

		$validity = $this->config->item('forgot_link_expiry');
		$forgot_date = new Datetime( $forgot_arr['created'] );
		$forgot_date = $forgot_date->modify( $validity )->format('Y-m-d H:i:s');

		if ( strtotime(date('Y-m-d H:i:s')) > strtotime($forgot_date) )
		{
			$this->_destroy_forgot( t('forgot_link_expired') );
		}

		$user = $this->user->get( array('email' => $forgot_arr['email']) );

		if ( !$user[0]->is_valid() )
		{
			$this->_destroy_forgot( t('not_registered_email') );
		}
		else
		{
			$user = $user[0];
		}

		$this->user->execute_login( $user->id );

		$forgot_arr = array();
		
		$this->session->set_userdata( array('forgot' => $forgot_arr) );

		$this->flashmanager->set_msg( t('change_your_password').'!!', 'error' );

		redirect(base_url());

	}

	private function _destroy_forgot( $msg = null )
	{
		$forgot_arr = array();
		$this->session->set_userdata( array('forgot' => $forgot_arr) );

		if ( is_null($msg) )
		{
			$this->flashmanager->set_msg(t('invalid_link'), 'error');
		}
		else
		{
			$this->flashmanager->set_msg( $msg, 'error');
		}
		
		redirect('auth/forgot');
	}
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */