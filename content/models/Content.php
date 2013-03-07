<?php

class Content_Model_Content
{

    protected $_content_id;
    protected $_content_image_link;
    protected $_upperContentId;
    protected $_language_id;
    protected $_content_type;
    protected $_content_type_id;
    protected $_element_id;
    protected $_element_category_id;
    protected $_menu_id;
    public    $_heading;
    protected $_location;
    protected $_keyword;
    protected $_title_tag;
    protected $_desc_tag;
    protected $_one_line_desc;
    protected $_two_line_desc;
    protected $_three_line_desc;
    protected $_one_para_desc;
    protected $_short_desc;
    protected $_description;
    protected $_order_no;
    protected $_status;
    protected $_entered_by;
    protected $_entered_dt;
    protected $_checked;
    protected $_checked_by;
    protected $_checked_dt;
    protected $_approved;
    protected $_approved_by;
    protected $_approved_dt;
    protected $_is_leaf;
    protected $_file_name;
    protected $_file_path;
    protected $_data = array();

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

    /**
     * @param field_type $_content_id
     */
    public function setContent_id($_content_id)
    {
        $this->_content_id = (int) $_content_id;
        return $this;
    }

    /**
     * @param field_type $_language_id
     */
    public function setLanguage_id($_language_id)
    {
        $this->_language_id = $_language_id;
        return $this;
    }

    /**
     * @param field_type $_content_type
     */
    public function setContent_type($_content_type)
    {
        $this->_content_type = (string) $_content_type;
        return $this;
    }

    /**
     * @param field_type $_content_type_id
     */
    public function setContent_type_id($_content_type_id)
    {
        $this->_content_type_id = (int) $_content_type_id;
        return $this;
    }

    /**
     * @param field_type $_element_id
     */
    public function setElement_id($_element_id)
    {
        $this->_element_id = (int) $_element_id;
        return $this;
    }

    /**
     * @param field_type $_element_category_id
     */
    public function setElementCategory_id($_element_category_id)
    {
        $this->_element_category_id = (int) $_element_category_id;
        return $this;
    }

    public function setUpperContentId($uid)
    {
        $this->_upperContentId = (int) $uid;
        return $this;
    }

    /**
     * @param field_type $_menu_id
     */
    public function setMenu_id($_menu_id)
    {
        $this->_menu_id = (int) $_menu_id;
        return $this;
    }

    /**
     * @param field_type $_title_tag
     */
    public function setTitle_tag($_title_tag)
    {
        $this->_title_tag = (string) $_title_tag;
        return $this;
    }
    
    /**
     * @param field_type $_desc_tag
     */
    public function setDesc_tag($_desc_tag)
    {
        $this->_desc_tag = (string) $_desc_tag;
        return $this;
    }


    /**
     * @param field_type $_short_desc
     */
    public function setShort_desc($_short_desc)
    {
        $this->_short_desc = (string) $_short_desc;
        return $this;
    }

    /**
     * @param field_type $_order_no
     */
    public function setOrder_no($_order_no)
    {
        $this->_order_no = (int) $_order_no;
        return $this;
    }

    /**
     * @param field_type $_entered_by
     */
    public function setEntered_by($_entered_by)
    {
        $this->_entered_by = $_entered_by;
        return $this;
    }

    /**
     * @param field_type $_entered_dt
     */
    public function setEntered_dt($_entered_dt)
    {
        $this->_entered_dt = $_entered_dt;
        return $this;
    }

    /**
     * @param field_type $_checked_by
     */
    public function setChecked_by($_checked_by)
    {
        $this->_checked_by = $_checked_by;
        return $this;
    }

    /**
     * @param field_type $_checked_dt
     */
    public function setChecked_dt($_checked_dt)
    {
        $this->_checked_dt = $_checked_dt;
        return $this;
    }

    /**
     * @param field_type $_approved_by
     */
    public function setApproved_by($_approved_by)
    {
        $this->_approved_by = $_approved_by;
        return $this;
    }

    /**
     * @param field_type $_approved_dt
     */
    public function setApproved_dt($_approved_dt)
    {
        $this->_approved_dt = $_approved_dt;
        return $this;
    }
    /**
     * @param field_type $_approved_dt
     */
    public function setContent_image_link($_content_image_link)
    {
        $this->_content_image_link = $_content_image_link;
        return $this;
    }

    /**
	 * @return the $_one_line_desc
	 */
	public function getContent_image_link() {
		return $this->_content_image_link;
	}


    /**
	 * @return the $_one_line_desc
	 */
	public function getOne_line_desc() {
		return $this->_one_line_desc;
	}

	/**
	 * @return the $_two_line_desc
	 */
	public function getTwo_line_desc() {
		return $this->_two_line_desc;
	}

	/**
	 * @return the $_three_line_desc
	 */
	public function getThree_line_desc() {
		return $this->_three_line_desc;
	}

	/**
	 * @return the $_one_para_desc
	 */
	public function getOne_para_desc() {
		return $this->_one_para_desc;
	}

	/**
	 * @param field_type $_one_line_desc
	 */
	public function setOne_line_desc($_one_line_desc) {
		$this->_one_line_desc = $_one_line_desc;
        return $this;
	}

	/**
	 * @param field_type $_two_line_desc
	 */
	public function setTwo_line_desc($_two_line_desc) {
		$this->_two_line_desc = $_two_line_desc;
        return $this;
	}

	/**
	 * @param field_type $_three_line_desc
	 */
	public function setThree_line_desc($_three_line_desc) {
		$this->_three_line_desc = $_three_line_desc;
        return $this;
	}

	/**
	 * @param field_type $_one_para_desc
	 */
	public function setOne_para_desc($_one_para_desc) {
		$this->_one_para_desc = $_one_para_desc;
        return $this;
	}

    /**
     * @return the $_content_id
     */
    public function getContent_id()
    {
        return (int) $this->_content_id;
    }

    /**
     * @return the $_element_id
     */
    public function getElement_id()
    {
        return (int) $this->_element_id;
    }

    /**
     * @return the $_element_category_id
     */
    public function getElementCategory_id()
    {
        return (int) $this->_element_category_id;
    }

    /**
     * @return the $_language_id
     */
    public function getLanguage_id()
    {
        return $this->_language_id;
    }

    /**
     * @return the $_content_type
     */
    public function getContent_type()
    {
        return $this->_content_type;
    }

    /**
     * @return the $_menu_id
     */
    public function getMenu_id()
    {
        return $this->_menu_id;
    }

    /**
     * @return the $_metatag
     */
    public function getTitle_tag()
    {
        return $this->_title_tag;
    }
    public function getDesc_tag()
    {
        return $this->_desc_tag;
    }

    /**
     * @return the $_short_desc
     */
    public function getShort_desc()
    {
        return $this->_short_desc;
    }

    /**
     * @return the $_order_no
     */
    public function getOrder_no()
    {
        return $this->_order_no;
    }

    /**
     * @return the $_entered_by
     */
    public function getEntered_by()
    {
        return $this->_entered_by;
    }

    /**
     * @return the $_entered_dt
     */
    public function getEntered_dt()
    {
        return $this->_entered_dt;
    }

    /**
     * @return the $_checked_by
     */
    public function getChecked_by()
    {
        return $this->_checked_by;
    }

    /**
     * @return the $_checked_dt
     */
    public function getChecked_dt()
    {
        return $this->_checked_dt;
    }

    /**
     * @return the $_approved_by
     */
    public function getApproved_by()
    {
        return $this->_approved_by;
    }

    /**
     * @return the $_approved_dt
     */
    public function getApproved_dt()
    {
        return $this->_approved_dt;
    }

    /**
     * @return the $_content_type_id
     */
    public function getContent_type_id()
    {
        return $this->_content_type_id;
        return $this;
    }

    /**
     * @return the $_upper_content_id
     */
    public function getUpperContentId()
    {
        return $this->_upperContentId;
    }

    public function setHeading($heading)
    {
        $this->_heading = (string) $heading;
        return $this;
    }

    public function getHeading()
    {
        return $this->_heading;
    }

    public function setLocation($location)
    {
        $this->_location = $location;
        return $this;
    }

    public function getLocation()
    {
        return $this->_location;
    }

    public function setKeyword($keyword)
    {
        $this->_keyword = (string) $keyword;
        return $this;
    }

    public function getKeyword()
    {
        return $this->_keyword;
    }

    public function setDescription($description)
    {
        $this->_description = (string) $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function setChecked($checked)
    {
        $this->_checked = $checked;
        return $this;
    }

    public function getChecked()
    {
        return $this->_checked;
    }

    public function setApproved($approved)
    {
        $this->_approved = $approved;
        return $this;
    }

    public function getApproved()
    {
        return $this->_approved;
    }
    
    public function setIs_leaf($is_leaf)
    {
        $this->_is_leaf = (string) $is_leaf;
        return $this;
    }

    public function getIs_leaf()
    {
        return $this->_is_leaf;
    }
    
    public function setFile_name($file_name)
    {
        $this->_file_name = $file_name;
        return $this;
    }

    public function getFile_name()
    {
        return $this->_file_name;
    }
    
    public function setFile_path($file_path)
    {
        $this->_file_path = $file_path;
        return $this;
    }

    public function getFile_path()
    {
        return $this->_file_path;
    }


}