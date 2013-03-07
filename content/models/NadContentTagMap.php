<?php

class Content_Model_NadContentTagMap
{

    protected $_content_id;
    protected $_element_category_id;
    protected $_default_tag = null;
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
               // $this->_element_category_id+= array($key=>$value);
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
   

    public function getContent_id()
    {
        return $this->_content_id;
    }
    
    public function setElement_category_id($_element_category_id)
    {
        $this->_element_category_id = $_element_category_id;
        return $this;
    }
   
    public function getElement_category_id()
    {
        return $this->_element_category_id;
    }
    
    public function setDefault_tag($_default_tag)
    {
        $this->_default_tag = $_default_tag;
        return $this;
    }
    
    public function getDefault_tag()
    {
        return $this->_default_tag;
    }

}

?>
