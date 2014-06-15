<?php

function prer($arr)
{
	echo '<pre>';
		print_r($arr);
	echo '</pre>';
}

function assets_file( $filename, $subfolder)
{
	return base_url()."assets/$subfolder/$filename";
}

function img_link( $img )
{
	$ci =& get_instance();
	$paths = $ci->config->item('predefined_paths');

	return base_url().$paths['images'].$img;

}

function config($item)
{
	$ci =& get_instance();

	return $ci->config->item($item);
}

function css_link( $css )
{
	$ci =& get_instance();
	$paths = $ci->config->item('predefined_paths');

	return base_url().$paths['css'].$css;
}

function shorten($str, $long)
{
	$str_length = strlen($str);

	if ( $long > $str_length )
	{
		return $str;
	}
	else
	{
		return mb_substr($str, 0, $long).'...';
	}
}

function czech_day($i)
{
	$ci =& get_instance();

	return $ci->time->czech_day($i);
}

function js_link( $js )
{
	$ci =& get_instance();
	$paths = $ci->config->item('predefined_paths');

	return base_url().$paths['js'].$js;

}

function get_activity_name( $index )
{
	$ci =& get_instance();

	$activities = $ci->config->item('physical_activity');

	if ( isset( $activities[$index]) ) return $activities[$index];
	else return false;
}

function minify_css($files = array())
{
	$ci =& get_instance();
	$paths = $ci->config->item('predefined_paths');
	$css_path = '/'.$paths['css'];


	$css_paths = array();
	foreach ( $files as $file )
	{
		$css_paths[] = $css_path.$file;
	}


	$ci->load->library('css_combinator', $css_paths);

}

function a( $controller_method, $text, $attrs = array('class' => '') )
{
 	$ci =& get_instance();
 	$ci->load->helper('url');
 	$base_url = base_url();

 	$valid_link = true;
	if ( ENVIRONMENT == 'development' OR ENVIROMENT == 'testing' ) $valid_link = check_link( $controller_method );

 	$out = "<a href=\"$base_url$controller_method\"";

 	if ( is_string($attrs) )
 	{
 		$attrs = array('class' => $attrs);
 	}
 	else
 	{

 		$attrs['class'] .= ( $valid_link ) ? '' : ' invalid';
 	}

 		foreach( $attrs as $k => $v )
 		{
 			$out .= " $k=\"$v\"";
 		}

 	$out .= ">$text</a>";

 	return $out;
}


function redirect_back()
{
	if ( !empty($_SERVER['HTTP_REFERER']) ) redirect( $_SERVER['HTTP_REFERER'] );
	else                                    redirect('');
}

function check_link( $link )
{
	return true;
 	$ci =& get_instance();
 	$ci->load->helper('url');
	if ( !strstr($link, 'http://') ) $link = base_url().$link;
	
	$headers = @get_headers( $url);
	$headers = (is_array($headers)) ? implode( "\n ", $headers) : $headers;

	return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
}


function is_404( $link )
{
	if(!function_exists('get_headers'))
	{
	    $resp = custom_get_headers( $link );
	}
	else
	{
		$resp = get_headers($link, 1);
	}

	return $resp;
}

function create_link( $method )
{
	return base_link().$method;
}

function format_date( $format, $date )
{
	$time = strtotime($date);

	return date($format, $time);
}


function rewrite_urls( $str )
{
	if ( is_string( $str) )
	{
		$fin = preg_replace("#(<\s*img\s+[^>]*src\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1http://www.magna.sk/$2$3', $str);
	}	 
	else
	{
		if ( is_array($str) )
		{
			foreach ( $str as $k => $v )
			{
				if ( is_array($v) )
				{
					foreach ($v as $d => $string ) 
					{
						$fin[$k][$d] = preg_replace("#(<\s*img\s+[^>]*src\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1http://www.magna.sk/$2$3', $string);
					}
				}
				else
				{
					$fin[$k] = preg_replace("#(<\s*img\s+[^>]*src\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1http://www.magna.sk/$2$3', $v);
				}
				
			}
		}
		
	}
	

	return (isset($fin)) ? $fin : array();		
}

function rewrite_links( $str )
{
	$str = rewrite_urls($str);
	if ( is_string( $str) )
	{
		$fin = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)([^<]*)(</a>)#", '', $str);
	}	 
	else
	{
		if ( is_array($str) )
		{
			foreach ( $str as $k => $v )
			{
				if ( is_array($v) )
				{
					foreach ($v as $d => $string ) 
					{
		$fin = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)([^<]*)(</a>)#", '', $str);
					}
				}
				else
				{
		$fin = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)([^<]*)(</a>)#", '', $str);
				}
				
			}
		}
		
	}
	
	return $fin;
		
}



function custom_get_headers($url,$format=0)
{
    $url=parse_url($link);
    $end = "\r\n\r\n";
    $fp = fsockopen($url['host'], (empty($url['port'])?80:$url['port']), $errno, $errstr, 30);
    if ($fp)
    {
        $out  = "GET / HTTP/1.1\r\n";
        $out .= "Host: ".$url['host']."\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $var  = '';

        fwrite($fp, $out);
        while (!feof($fp))
        {
            $var.=fgets($fp, 1280);
            if(strpos($var,$end))
            break;
        }

        fclose($fp);

        $var=preg_replace("/\r\n\r\n.*\$/",'',$var);
        $var=explode("\r\n",$var);
        if($format)
        {
            foreach($var as $i)
            {
                if(preg_match('/^([a-zA-Z -]+): +(.*)$/',$i,$parts))
                $v[$parts[1]]=$parts[2];
            }
         	   return $v;
        }
        else
        {
            return $var;
  	    }
	}
}

function msg( $text, $success = true )
{
	$ci =& get_instance();

	if ( $ci->session->flashdata('msg') )
	{
		return $text;
	}

	if ( $success )
	{
		$ci->session->set_flashdata('msg', '<div class="flash success">'.$text.'</div>');
	}
	else
	{
		$ci->session->set_flashdata('msg', '<div class="flash error">'.$text.'</div>');
	}

	return $text;
}


function block( $name )
{
	$ci =& get_instance();
	
	$lang = $ci->session->userdata('lang');		

	$block =  $ci->magnaModel->getBlob( $name );


	if ( $ci->session->userdata('user_id') == 1874 )
	{
		$block_name = "[$name]";
	}
	else
	{
		$block_name = '';
	}

	if ( isset($block['html_'.$lang]) ) 
	{
		return $block['html_'.$lang];
	}
	else 
	{
		return null;
	}
}


function filter_out( $str )
{
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function utftoasci($str)
{
    $return = Str_Replace(
                    array("á","č","ď","é","ě","í","ľ","ň","ó","ř","š","ť","ú","ů","ý ","ž","Á","Č","Ď","É","Ě","Í","Ľ","Ň","Ó","Ř","Š","Ť","Ú","Ů","Ý","Ž") ,
                    array("a","c","d","e","e","i","l","n","o","r","s","t","u","u","y ","z","A","C","D","E","E","I","L","N","O","R","S","T","U","U","Y","Z") ,
                    $str);
    $return = StrToLower($return); //velká písmena nahradí malými.
    return $return;
}

function get_url_param($key, $url)
{
	$url = explode('?', $url);
	unset($url[0]);
	$url = strstr($url[1], '&') ? explode('&', $url[1]) : $url;


	$params = array();

	foreach($url as $pair)
	{
		$pair = explode('=', $pair);
		$params[$pair[0]] = $pair[1];
	}


	return isset($params[$key]) ? $params[$key] : false;
}

function current_url()
{
	return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}



function vd($str) {
	echo "<pre>";
	var_dump($str);
	echo "</pre>";
}