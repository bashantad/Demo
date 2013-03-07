<?php

class Content_Model_NadContentFile
{

    protected $_content_file_id;
    protected $_content_file_type_id;
    protected $_content_id;
    protected $_file_name;
    protected $_original_file_name;
    protected $_file_size;
    protected $_file_path;
    protected $_mime_type;
    protected $_status;
    protected $_entered_by;
    protected $_entered_dt;
    protected $_checked;
    protected $_checked_by;
    protected $_checked_dt;
    protected $_approved;
    protected $_approved_by;
    protected $_approved_dt;
    protected $_caption;
    protected $_description;

    
    /**
	 * @return the $_content_file_id
	 */
	public function getContent_file_id() {
		return $this->_content_file_id;
	}

	/**
	 * @return the $_content_file_type_id
	 */
	public function getContent_file_type_id() {
		return $this->_content_file_type_id;
	}
    
    public function getEmbed_code() {
        return $this->_embed_code;
    }

	/**
	 * @return the $_content_id
	 */
	public function getContent_id() {
		return $this->_content_id;
	}

	/**
	 * @return the $_file_name
	 */
	public function getFile_name() {
		return $this->_file_name;
	}

	/**
	 * @return the $_original_file_name
	 */
	public function getOriginal_file_name() {
		return $this->_original_file_name;
	}

	/**
	 * @return the $_file_size
	 */
	public function getFile_size() {
		return $this->_file_size;
	}

	/**
	 * @return the $_file_path
	 */
	public function getFile_path() {
		return $this->_file_path;
	}

	/**
	 * @return the $_mime_type
	 */
	public function getMime_type() {
		return $this->_mime_type;
	}

	/**
	 * @return the $_status
	 */
	public function getStatus() {
		return $this->_status;
	}

	/**
	 * @return the $_entered_by
	 */
	public function getEntered_by() {
		return $this->_entered_by;
	}

	/**
	 * @return the $_entered_dt
	 */
	public function getEntered_dt() {
		return $this->_entered_dt;
	}

	/**
	 * @return the $_checked
	 */
	public function getChecked() {
		return $this->_checked;
	}

	/**
	 * @return the $_checked_by
	 */
	public function getChecked_by() {
		return $this->_checked_by;
	}

	/**
	 * @return the $_checked_dt
	 */
	public function getChecked_dt() {
		return $this->_checked_dt;
	}

	/**
	 * @return the $_approved
	 */
	public function getApproved() {
		return $this->_approved;
	}

	/**
	 * @return the $_approved_by
	 */
	public function getApproved_by() {
		return $this->_approved_by;
	}

	/**
	 * @return the $_approved_dt
	 */
	public function getApproved_dt() {
		return $this->_approved_dt;
	}

	/**
	 * @param field_type $_content_file_id
	 */
	public function setContent_file_id($_content_file_id) {
		$this->_content_file_id = $_content_file_id;
	}

	/**
	 * @param field_type $_content_file_type_id
	 */
	public function setContent_file_type_id($_content_file_type_id) {
		$this->_content_file_type_id = $_content_file_type_id;
	}

	/**
	 * @param field_type $_content_id
	 */
	public function setContent_id($_content_id) {
		$this->_content_id = $_content_id;
	}
    
    public function setEmbed_code($_embed_code) {
        $this->_embed_code = $_embed_code;
    }

	/**
	 * @param field_type $_file_name
	 */
	public function setFile_name($_file_name) {
		$this->_file_name = $_file_name;
	}

	/**
	 * @param field_type $_original_file_name
	 */
	public function setOriginal_file_name($_original_file_name) {
		$this->_original_file_name = $_original_file_name;
	}

	/**
	 * @param field_type $_file_size
	 */
	public function setFile_size($_file_size) {
		$this->_file_size = $_file_size;
	}

	/**
	 * @param field_type $_file_path
	 */
	public function setFile_path($_file_path) {
		$this->_file_path = $_file_path;
	}

	/**
	 * @param field_type $_mime_type
	 */
	public function setMime_type($_mime_type) {
		$this->_mime_type = $_mime_type;
	}

	/**
	 * @param field_type $_status
	 */
	public function setStatus($_status) {
		$this->_status = $_status;
	}

	/**
	 * @param field_type $_entered_by
	 */
	public function setEntered_by($_entered_by) {
		$this->_entered_by = $_entered_by;
	}

	/**
	 * @param field_type $_entered_dt
	 */
	public function setEntered_dt($_entered_dt) {
		$this->_entered_dt = $_entered_dt;
	}

	/**
	 * @param field_type $_checked
	 */
	public function setChecked($_checked) {
		$this->_checked = $_checked;
	}

	/**
	 * @param field_type $_checked_by
	 */
	public function setChecked_by($_checked_by) {
		$this->_checked_by = $_checked_by;
	}

	/**
	 * @param field_type $_checked_dt
	 */
	public function setChecked_dt($_checked_dt) {
		$this->_checked_dt = $_checked_dt;
	}

	/**
	 * @param field_type $_approved
	 */
	public function setApproved($_approved) {
		$this->_approved = $_approved;
	}

	/**
	 * @param field_type $_approved_by
	 */
	public function setApproved_by($_approved_by) {
		$this->_approved_by = $_approved_by;
	}
    

	/**
	 * @return the $_caption
	 */
	public function getCaption() {
		return $this->_caption;
	}

	/**
	 * @return the $_description
	 */
	public function getDescription() {
		return $this->_description;
	}

	/**
	 * @param field_type $_caption
	 */
	public function setCaption($_caption) {
		$this->_caption = $_caption;
	}

	/**
	 * @param field_type $_description
	 */
	public function setDescription($_description) {
		$this->_description = $_description;
	}

	/**
	 * @param field_type $_approved_dt
	 */
	public function setApproved_dt($_approved_dt) {
		$this->_approved_dt = $_approved_dt;
	}

	//  protected $_data = array();

   
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
                $this->_data += array($key => $value);
            }
        }
        return $this;
    }

    public function toArray()
    {
        return (array) $this->_data;
    }

   

}