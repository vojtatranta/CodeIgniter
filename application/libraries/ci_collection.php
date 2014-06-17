<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CI_Collection implements ArrayAccess, Iterator
{
	protected $ci;
	protected $db_query;
	protected $instance;

	private $position = 0;

	public $result; //array
	public $row_result;

	public function __construct($config)
	{
        $this->ci =& get_instance();

        $this->instance = $config[0];
        $this->db_query = $config[1];
	}

	public function __get($key)
	{
		if (!isset($this->row_result)) $this->row_result = $this->row();

		return $this->row_result->$key;
		
	}

	public function __call($method, $args)
	{
		if (!isset($this->row_result)) $this->row_result = $this->row();

		if (method_exists($this->row_result, $method)) return call_user_func_array(array($this->row_result), $args);

		return call_user_func_array(array($this->db_query, $method), $args); 
		
	}	

	public function __toString()
	{
		if (!isset($this->row_result)) $this->row_result = $this->row();

		return (string) $this->row_result;
	}

	public function row()
	{
		$class_name = $this->lower_class_name($this->instance);

		$this->row_result = $this->db_query->get($class_name)->row(0, $class_name);

		return $this->row_result;
	}

	public function result($limit = null, $offset = null)
	{
		$class_name = $this->lower_class_name($this->instance);

		$this->result = $this->db_query->get($class_name, $limit, $offset)->result($class_name);

		return $this->result;
	}
	
	public function db()
	{
		return $this->db_query;
	}

	public function lower_class_name($inst)
	{
		return strtolower(get_class($inst));
	}

	public function offsetSet($offset, $value) 
	{
		if (!isset($this->result)) $this->result = $this->result();

        if (is_null($offset)) 
        {
            $this->result[] = $value;
        } 
        else 
        {
            $this->result[$offset] = $value;
        }
    }

    public function offsetExists($offset) 
    {
		if (!isset($this->result)) $this->result = $this->result();

        return isset($this->result[$offset]);
    }

    public function offsetUnset($offset) 
    {
		if (!isset($this->result)) $this->result = $this->result();

        unset($this->result[$offset]);
    }

    public function offsetGet($offset) 
    {
		if (!isset($this->result)) $this->result = $this->result();	

        return isset($this->result[$offset]) ? $this->result[$offset] : null;
    }

    function rewind() 
    {
        $this->position = 0;
    }

    function current() 
    {
        return $this->result[$this->position];
    }

    function key() 
    {
        return $this->position;
    }

    function next() 
    {
        ++$this->position;
    }

    function valid() 
    {
    	if (!isset($this->result)) $this->result = $this->result();

        return isset($this->result[$this->position]);
    }

}

/* End of file cI_Collection.php */
/* Location: ./application/libraries/cI_Collection.php */
