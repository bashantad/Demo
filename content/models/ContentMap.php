<?php

class Content_Model_ContentMap
{

    protected $_content_id;
    protected $_parent_id;
    protected $_level;
    protected $_order_no;
    protected $_data;

    /**
     * Constructor
     * 
     * @param  array|null $options 
     * @return void
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Overloading: allow property access
     * 
     * @param  string $name 
     * @param  mixed $value 
     * @return void
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        $this->$method($value);
    }

    /**
     * Overloading: allow property access
     * 
     * @param  string $name 
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        return $this->$method();
    }

    /**
     * Set object state
     * 
     * @param  array $options 
     * @return Content_Model_Content
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
                //$this->_data += array($key=>$value);
            }
        }
        return $this;
    }

    public function toArray()
    {
        return (array) $this->_data;
    }

    public function setContent_id($_content_id)
    {
        $this->_content_id = (int) $_content_id;
        return $this;
    }

    public function setOrder_no($_order_no)
    {
        $this->_order_no = (int) $_order_no;
        return $this;
    }

   
    public function setParent_id($_parent_id)
    {
        $this->_parent_id = (int) $_parent_id;
        return $this;
    }

    public function setLevel($_level)
    {
        $this->_level = (int) $_level;
    }

    public function getContent_id()
    {
        return $this->_content_id;
    }
    
    public function getOrder_no()
    {
        return $this->_order_no;
    }
    
    public function getParent_id()
    {
        return $this->_parent_id;
    }

    public function getLevel()
    {
        return $this->_level;
    }

}

?>
