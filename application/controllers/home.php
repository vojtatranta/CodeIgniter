<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	function __construct() 
	{
		parent::__construct();
	}

	public function index()
	{
		/*$from = $this->course_type->get(array('id' => 2))->row();
		$to   = $this->course_type->get(array('id' => 3))->row();

		$from->copy_courses($to);*/


		$course = Course::get(3);
	
		prer($course);
		

		//echo $course->_create_form();

		//$this->listing('course_month');
	}

	public function get_filtered_models()
	{
		return array('main' => array('course_month', 'course'), 'dropdown' => array('course_type', 'classroom', 'course_time', 'lector', 'textbook', 'student_course'));
	}



	public function course_type_detail($id)
	{
		$course_type = $this->course_type->get(array('id' => $id))->row();
		$view_data['course_type'] = $course_type;



		$view_data['edit_form'] = $course_type->create_form('home/process_edit/', array('course_type', $id)); 

		$view_data['model_listing'] = 'course_type';
		$view_data['models'] = $this->models;


		$this->load->view('course_type_detail', $view_data);
	}

	public function course_detail($id)
	{
		$course = $this->course->get_with_relations(array('id' => $id));
		$view_data['course'] = $course;

		$view_data['colors'] = $this->config->item('statuse_colors');

		$view_data['edit_form'] = $course->create_form('home/process_edit/', array('course', $id)); 


		$types = $this->course_type->get_instances_for_select();


		$view_data['copy_form'] = copy_form('home/copy_courses', $course->id, $types);

		$view_data['model_listing'] = 'course';
		$view_data['models'] = $this->models;


		$this->load->view('course_detail', $view_data);
	}

	public function course_month_detail($id)
	{
		$course_month = $this->course_month->get(array('id' => $id))->row();
		$view_data['course_month'] = $course_month;
		$view_data['edit_form'] = $course_month->create_form('home/process_edit/', array('course_month', $id)); 

		$view_data['model_listing'] = 'course_month';
		$view_data['models'] = $this->models;

		$course_months = $this->course_month->get_instances_for_select();
		$attend_statuses = $this->config->item('attend_statuses');

		$view_data['copy_form'] = copy_form('home/copy_courses/', $course_month->id, $course_months, $attend_statuses);


		$this->load->view('course_month_detail', $view_data);
	}

	public function lector_detail($id)
	{
		$lector = $this->lector->get(array('id' => $id))->row();
		$view_data['lector'] = $lector;
		$view_data['edit_form'] = $lector->create_form('home/process_edit/', array('lector', $id)); 

		$view_data['model_listing'] = 'lector';
		$view_data['models'] = $this->models;

		$view_data['lectors_schedule'] = $lector->get_schedule(date('m'), date('Y')); 

		//prer($view_data['lectors_schedule']);
		
		$view_data['day_names'] = $this->course_time->get_day_options();
		$view_data['day_times'] = $this->course_time->get_time_options();

		$this->load->view('lector_detail', $view_data);
	}

	public function listing($model, $page = 1)
	{
		$view_data['page'] = $page == 1 ? '' : $page;
		$page--;

		$view_data['model']['columns'] = $this->basemodel->get_props($model);
		$view_data['models'] = $this->models;
		$view_data['model_listing'] = $model;
		$view_data['pages'] = $this->$model->get_paging($this->basic_offset);

		if ( method_exists($this, $model.'_listing') )
		{
			$method = $model.'_listing';	
			return call_user_func_array(array($this, $method), array($model, $page, $view_data));
		}

		$view_data['model']['instances'] = $this->$model->get_instances($this->basic_offset, $page * $this->basic_offset);

		$this->load->view('model_table', $view_data);
	}

		public function course_listing($model, $page, $view_data)
		{
			$view_data['model']['instances'] = $this->$model->get_instances($this->basic_offset, $page * $this->basic_offset);

			$course_months = $this->course_month->get_instances(null, null, 'asc', 'month');
			$attend_statuses = $this->config->item('attend_statuses');

			$view_data['course_copy_form'] = course_copy_form('home/copy_courses',$course_months ,$attend_statuses );

			$this->load->view('course_table', $view_data);
		}

		public function course_month_listing($model, $page, $view_data)
		{
			$view_data['model']['instances'] = $this->$model->get_instances($this->basic_offset, $page * $this->basic_offset);

			$this->load->view('course_month_table', $view_data);
		}


	public function edit($model, $id)
	{
		$inst = $this->$model->get_with_relations(array('id' => $id));


		$view_data['content'] = $inst->create_form('home/process_edit/', array($model, $id));
		$view_data['models'] = $this->models;
		$view_data['model'] = $model;


		$this->load->view('model_edit_form', $view_data);
	}

	public function process_edit($model, $id)
	{
		$inst = $this->$model->get(array('id' => $id))->row();
		$form = $inst->create_form('');

		$post = $this->input->post();

		if ( $form->validate_post() )
		{
			$this->$model->init_props($this->input->post());
			
			$inst = $this->$model->save();

			$this->flashmanager->set_msg(t('item_edited', 'admin'), 'success');

			$ref = $_SERVER['HTTP_REFERER'];


			if (strstr($ref, '?'))
			{	
				if ($referer = get_url_param('referer', $ref))
				{
					redirect($referer);
				}
				else
				{
					redirect_back();
				}
			}
			else
			{
				redirect('home/listing/'.$model);
			}
		}
	}

	public function add($model)
	{
		$get = $this->input->get();


		foreach($this->$model as $prop => $val)
		{
			if (isset($get[$prop])) $this->$model->$prop = $get[$prop];
		}


		$view_data['content'] = $this->$model->create_form('home/process_add/', array($model));
		$view_data['models'] = $this->models;
		$view_data['model'] = $model;

		$this->load->view('model_edit_form', $view_data);
	}


	public function process_add($model)
	{
		$form = $this->$model->create_form('');

		if ( $form->validate_post() )
		{
			$this->$model->init_props($this->input->post());
			
			$inst = $this->$model->save();

			$this->flashmanager->set_msg(t('item_created', 'admin'), 'success');

			
			$ref = $_SERVER['HTTP_REFERER'];

			if (strstr($ref, '?'))
			{	
				if ($referer = get_url_param('referer', $ref))
				{
					redirect($referer);
				}
				else
				{
					redirect_back();
				}
			}
			else
			{
				redirect('home/listing/'.$model);
			}
		}
	}

	public function copy_courses()
	{
		$input = $this->input->post();

		$src = $this->course_month->get(array('id' => $input['src']))->row();

		$tar = $this->course_month->get(array('id' => $input['tar']))->row();


		$status = $input['attend_status'];

		$src->copy_in($tar, $status);

		$this->flashmanager->set_msg('Courses copied.', 'success');

		redirect_back();
	}

	public function delete($model, $id)
	{
		$inst = $this->$model->get(array('id' => $id))->row()->delete();

		$this->flashmanager->set_msg(t('item_deleted', 'admin'), 'success');

		$ref = current_url();

		if (strstr($ref, '?'))
		{	
			if ($referer = get_url_param('referer', $ref))
			{
				redirect($referer);
			}
			else
			{
				redirect_back();
			}
		}
		else
		{
			redirect('home/listing/'.$model);
		}
	}

}
/* End of file home.php */
/* Location: ./application/controllers/home.php */