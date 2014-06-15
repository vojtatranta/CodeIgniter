<?php

function t( $str, $lang_file = null )
{

	$ci =& get_instance();
	//set correct lang
	
	if (is_null($lang_file))
	{
		$lang_file = $ci->config->item('default_lang_file');
	}			

	$lang_file_system = $lang_file.'_lang.php';


	$lang = $ci->session->userdata('lang');

	$ci->lang->load($lang_file, $lang);

	$supported_langs = $ci->config->item('supported_langs');
	$translated =  $ci->lang->line($str);

	if ( ENVIRONMENT != 'development' ) return (!empty($translated)) ? $translated : $str;


	foreach ($supported_langs as $lang) 
	{	
		$lang_file_path = './'.APPPATH."language/$lang/$lang_file_system";
	

		if ( !file_exists($lang_file_path) ) write_translation( $str, $lang, $lang_file_path );

		$ci->lang->load($lang_file, $lang);

		$lines = $ci->lang->language;
		
		if ( !isset( $lines[ $str ] ) )
		{
			//write_translation( $str, $lang, $lang_file_path );		                      
		}
	}	

	//set correct lang
	$ci->lang->load($lang_file, $lang);

	return (!empty($translated)) ? $translated : $str;
}

function write_translation( $str, $lang, $lang_file )
{
	if ( empty($str) ) return false;
	
	if ( !is_dir("./application/language/$lang") )
	{
		mkdir("./application/language/$lang");
		@chmod("./application/language/$lang", 0666);
	} 


	if ( !file_exists($lang_file) ) $beginning = "<?php\n";
	else                            $beginning = "";

	//@chmod($lang_file, 0777);

	$lang_file = fopen($lang_file, 'a+');

	$array_string = $beginning."\$lang['$str'] = '';\n";

	fwrite($lang_file, $array_string);
	//@chmod($lang_file, 0777);
}


