<?php

function get_factory_instance()
{
	$ci =& get_instance();

	$inst_name = generateRandomString();

	$ci->load->library('ci_form', array(),$inst_name);

	return $ci->$inst_name;
}

function copy_form($action, $source_id, $opts = array(), $attend_statuses = array())
{
	$ci =& get_instance();
	$factory = get_factory_instance();

	$form = $factory->create_form($action);	

	$src = $form->create_input('hidden', 'src', '');
		$src->attributes['value'] = $source_id;

	$div = $form->create_element('div', 'field-wrap' );
		$select = $form->create_select( 'tar', $opts, 'Copy courses to new month' );
	$form->end_element( $div );

	$div = $form->create_element('div', 'field-wrap' );
		$select = $form->create_select( 'attend_status', $attend_statuses, "and set students's statuses to" );
	$form->end_element( $div );

	$div = $form->create_element('div', 'field-wrap submit-area' );
		$submit = $form->create_input('submit', '', 'Submit');
				$submit->attributes['value'] = 'Copy';
				$submit->classes[] = 'btn large btn-success';

	$form->end_element( $div );
	
	return $form->assemble();
}




function course_copy_form($action, $months, $attend_statuses)
{
	$ci =& get_instance();
	$factory = get_factory_instance();

	$form = $factory->create_form($action);
	$form->form_class = 'course-filtering-form';

	$months_from = array();
	$months_to = array();
	foreach ($months as $month) 
	{
		if ( $month->year < date('Y') OR ($month->month <= date('m') AND $month->year <= date('Y')) )
		{
			$months_to[$month->id] = $month->get_unicode();
		}
		else
		{
			$months_from[$month->id] = $month->get_unicode();
		}
	}


	$last = isset($months_to[count($months_to) - 1]) ? $months_to[count($months_to) - 1] : null;
	reset($months_from);


	$div = $form->create_element('div', 'field-wrap' );
		$select = $form->create_select( 'src', $months_to, 'Copy students from' );
		$select->attributes['value'] = is_null($last) ? null : key($months_to) ;
	$form->end_element( $div );
	$div = $form->create_element('div', 'field-wrap' );
		$select = $form->create_select( 'tar', $months_from, 'into' );
		$select->attributes['value'] = key($months_from);
	$form->end_element( $div );
	$div = $form->create_element('div', 'field-wrap' );
		$select = $form->create_select( 'attend_status', $attend_statuses, "and set students's statuses to" );
	$form->end_element( $div );

	$div = $form->create_element('div', 'field-wrap submit-area' );
		$submit = $form->create_input('submit', '', 'Submit');
				$submit->attributes['value'] = 'Save';
				$submit->classes[] = 'btn large btn-danger';

	$form->end_element( $div );
	
	return $form->assemble();
}

function question_form( $spam_string )
{	

	$ci =& get_instance();
	$factory = get_factory_instance();

	$lang = $ci->session->userdata('lang');

	$form = $factory->create_form('actions/ask_question');

		$form->form_id = 'profile-settings-form';
		$user = $ci->appuser->get_logged_user();



		$div = $form->create_element('div', 'email-wrap');
			$email = $form->create_input('email', 'question_email', t('your_email'));
			$email->attributes['value'] = (isset($user->email)) ? $user->email: '';

			$email->rules[] = 'required';
		$form->end_element($div);

		foreach($ci->config->item('question_types') as $type)
		{
			$question_types[] = t($type);
		}

		$div = $form->create_element('div', 'question-type-wrap');
			$question = $form->create_select('question_type', $question_types, t('question_type_public'));
			$question->rules[] = 'required';
		$form->end_element($div);
			

		$div = $form->create_element('div', 'question-wrap');
			$question = $form->create_textarea('question', t('your_question'));
			$question->rules[] = 'required';
		$form->end_element($div);

		
		$div = $form->create_element('div', 'public-wrap checkbox-field');
			$form->create_input('checkbox', 'is_public', t('is_question_public'));
		$form->end_element($div);

		$spam_origin = $form->create_input('hidden', 'origin', '');
			$spam_origin->attributes['value'] = $spam_string;

		$spam_target = $form->create_input('hidden', 'target', '');
		$spam_target->rules[] = 'required';

		$div = $form->create_element('div', 'field-wrap submit-area' );

		$submit = $form->create_input('submit', '', 'Submit');
				$submit->attributes['value'] = t('ask_question');
				$submit->classes[] = 'btn large btn-success';

		$form->end_element( $div );
	
	return $form->assemble();
}

function upload_form(  )
{	

	$ci =& get_instance();
	$factory = get_factory_instance();

	$lang = $ci->session->userdata('lang');

	$form = $factory->create_form('actions/upload');

		$form->form_id = 'profile-settings-form';

		$spam_origin = $form->create_input('text', 'origin', '');

		$div = $form->create_element('div', 'field-wrap submit-area' );
		$spam_origin = $form->create_input('file', 'image', '');

		$div = $form->create_element('div', 'field-wrap submit-area' );

		$submit = $form->create_input('submit', '', 'Submit');
				$submit->attributes['value'] = t('ask_question');
				$submit->classes[] = 'btn large btn-success';

		$form->end_element( $div );
	
	return $form->assemble();
}

function create_import_form( $action )
{	

	$ci =& get_instance();
	$factory = get_factory_instance();
	$categories = $ci->category->get_instances();

	$select_cat = array();
	foreach($categories as &$category)
	{	
		$select_cat[$category->id] = $category->title;
	}

	$lang = $ci->session->userdata('lang');

	$form = $factory->create_form( $action );

		$form->form_id = 'profile-settings-form';

		$div = $form->create_element('div', 'question-wrap');
			$form->create_select('category', $select_cat, 'Kategorie');
		$form->end_element($div);

		$div = $form->create_element('div', 'question-wrap');
			$unit = $form->create_input('text', 'unit', 'Jednotka');
			$unit->attributes['value'] = 'g';
		$form->end_element($div);
		
		$div = $form->create_element('div', 'question-wrap');
			$textarea = $form->create_textarea('import', 'Ingredience');
		$form->end_element($div);

		$div = $form->create_element('div', 'field-wrap submit-area' );

			$submit = $form->create_input('submit', '', 'Submit');
					$submit->attributes['value'] = t('ask_question');
					$submit->classes[] = 'btn large btn-success';

		$form->end_element( $div );
	
	return $form->assemble();

}


function program_settings_form( $donor, $payment_methods )
{	

	$ci =& get_instance();
	$factory = get_factory_instance();

	$newsletter_options = array(t('no'), t('yes'));

	$lang = $ci->session->userdata('lang');

	$form = $factory->create_form('actions/update-program-settings/');
		$form->set_default_values( $donor );
		$form->form_class .= 'program-settings-form';

		$form->set_language( $lang );


	if ( $donor['payment_type'] > 1 )
	{
		$field = $form->create_input( 'hidden', 'id', '' );
			$field->attributes['value'] = $donor['donor_id'];

		$div = $form->create_element('div', 'field-wrap' );
			$select = $form->create_select( 'payment', $payment_methods, t('payment_method') );
		$form->end_element( $div );

		$div = $form->create_element('div', 'field-wrap' );
			$field = $form->create_input( 'number', 'ammount', t('amount') );
				$field->label .= " (". $donor['currency'] .")";
		$form->end_element( $div );

		$div = $form->create_element('div', 'field-wrap' );
			$select = $form->create_select( 'email_notification', $newsletter_options, t('email_notification') );
		$form->end_element( $div );

		$div = $form->create_element('div', 'field-wrap' );
			$select = $form->create_select( 'payment_notification', $newsletter_options, t('payment_notification') );
		$form->end_element( $div );
	}
	else
	{
		$div = $form->create_element('div', 'field-wrap' );
			$select = $form->create_select( 'payment', $payment_methods, t('payment_method') );
		$form->end_element( $div );

		$div = $form->create_element('div', 'field-wrap' );
			$field = $form->create_input( 'number', 'ammount', t('amount') );
				$field->label .= " (". $donor['currency'] .")";
		$form->end_element( $div );
	}



	$div = $form->create_element('div', 'field-wrap submit-area' );

		$submit = $form->create_input('submit', '', 'Submit');
			$submit->attributes['value'] = t('save');
			$submit->classes[] = 'save-event zone-button smaller';

	$form->end_element( $div );
	
	return $form->assemble();

}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}