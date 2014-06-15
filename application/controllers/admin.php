<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

	public $admin_menu = array(
							array('method' => 'list_all', 'label' => 'Všechny tabulky'),
						);

	public function __construct()
	{
		parent::__construct();
		

		$user = $this->user->get_logged_user();
		
		if  ( !$user )
		{
			//redirect('');
		}

		$models = $this->basemodel->get_models();

		foreach ($models as $k => $model) 
		{
			$this->admin_menu[] = array('method' => 'list_records/'.$model, 'label' => t($model));
		}

		$this->lang->load('admin', 'cs');
		
	}

	public function index()
	{
		//prer($this->meal->get(array('id' => 14))->relation('ingredience'));
		$this->list_all();
	}

	public function process_add($form)
	{
		$form_values = $form->get_values();

		$model_name = $form_values['action_params'][0];

		unset($form_values['action_params']);
		$this->$model_name->init_props($form_values);

		$res = $this->$model_name->save();

		$this->flashmanager->set_msg('Položka přidána');

		redirect('admin/list_all');	
	}

	public function add( $model_name = null )
	{
	
		$this->load->view('admin/simple_view', array('content' => $this->$model_name->create_form('admin/process_add', array($model_name))));
	}

	public function process_edit($form = null)
	{
		$form_values = $form->get_values();
		$model_name = $form_values['action_params'][0];


		$this->$model_name->init_props( $form_values );

		$res = $this->$model_name->save();	

		$this->flashmanager->set_msg('Úpravy uloženy');
		
		redirect('admin/list_all/');
	}
	
	public function edit($model_name = null, $id = null)
	{	
		$res = $this->$model_name->get(array('id'=>$id))->row();


		$instance = $this->$model_name->init_props($res);

		$this->load->view('admin/simple_view', array('content' => $instance->create_form('admin/process_edit', array($model_name, $id))));
	}

	public function translate($model_name = null, $id = null)
	{
		//if ( is_null($model_name) OR is_null($id) OR !class_exists($model_name) ) redirect('');

		if ( $this->session->flashdata('form') )
		{
			$form_values = $this->session->flashdata('form');
			
			$this->translation->init_props( $form_values );

			$res = $this->translation->save();	
			
			redirect('admin/list_all');
		}

		$instance = $this->translation->get(array('model' => $model_name, 'model_id' => $id))->row();

		if ( !$instance->is_valid() )
		{

			$instance = $this->translation->init_props(array('model' => $model_name, 'model_id' => $id));
			$instance->create_translatable_fields();
			$instance->save();
		}

		$this->load->view('admin/simple_view', array('content' => $instance->create_form('admin/edit/', array($instance->id))));
	}



	public function save($what)
	{
		$table_name = strtolower(get_class($what));
		$save_arr = array();

		foreach ($what as $key => $value) {
			if ( !is_array( $value) ) $save_arr[$key] = $value;
		}

		$this->db->insert($table_name, $save_arr);
		echo $this->db->last_query();


	}

	public function is_category($row)
	{
		$fields = explode(';', $row); 

		$x_count = 0;
		foreach ($fields as $key => $value) 
		{
			if ( $value == 'x' ) $x_count++;

			if ( $x_count > 3 ) return true;
		}

		return false;
	}

	public function list_records( $model_name = null )
	{
		if ( is_null($model_name) OR !class_exists($model_name) ) redirect('');

		$view_data['content'] = $this->$model_name->get_instances();
		$view_data['columns'] = $this->$model_name->get_fields( $model_name );
		$view_data['model_name'] = $model_name;

		$this->load->view('admin/table_view', $view_data);
	}

	public function list_all()
	{
		$models = array_flip($this->basemodel->get_models());


		foreach($models as $model => $val)
		{
			$view_data['models'][$model]['instances'] = $this->$model->get_instances(20);
			$view_data['models'][$model]['fields'] = $this->$model->get_fields( $model );
		}
		//prer($view_data);

		$this->load->view('admin/models_table_view', $view_data);
	}

	public function delete($model_name = null, $id = null)
	{
		if ( is_null($model_name) OR is_null($id) OR !class_exists($model_name) ) redirect('');

		$this->$model_name->delete( $id );

		$this->flashmanager->set_msg('Položka smazána');

		if ( empty($_SERVER['HTTP_REFERER']) ) redirect('admin/list_records/'.$model_name);
		else                                   redirect($_SERVER['HTTP_REFERER']);
	}

	public function get_category_props($category_id)
	{
		$category = $this->category->get(array('id' => $category_id));
		unset($category->id);
		unset($category->title);

		echo @json_encode($category);
	}


}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */