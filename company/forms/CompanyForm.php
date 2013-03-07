<?php

class Company_Form_CompanyForm extends Zend_Form
{

    private $_elementId = 0;
    public function __construct($eid=null)
    {
        $this->_elementId = $eid;
        parent::__construct($eid);
    }
    
    public function init()
    {
        $this->setMethod('post');
        $short_name = new Zend_Form_Element_Text('short_name');
        $short_name->setLabel('Short Name')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);

        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);
        
        $address = new Zend_Form_Element_Text('address');
        $address->setLabel('Address')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);
        $locationModel = new Address_Model_Location();
        $locations = $locationModel->getLocations();
        $location = new NepalAdvisor_Form_Element_Selection('location_id');
        $location->setLabel('Location')
                ->setTextAttribs(array('class' => 'form-text defined-text', 'size' => '8'))
                ->setSelectAttribs(array('class' => 'form-select defined-select'))
                ->addMultiOptions($locations)
                ->setRequired(true);
        $elementModel = new Package_Model_Element();
        $elements = $elementModel->getElements();
        $elementType = new NepalAdvisor_Form_Element_Selection('element_id');
        $elementType->setLabel('Company type')
                ->setTextAttribs(array('class' => 'form-text defined-text', 'size' => '8'))
                ->setSelectAttribs(array('class' => 'form-select defined-select'))
                ->addMultiOptions($elements)
                ->setRequired(true);
        $contents = array();
        if($this->_elementId){
            $contentMapper = new Content_Model_ContentMapper;
            $contents = $contentMapper->fetchContentHierarchy($this->_elementId);    
        }
        
        $contentOptions = array('0' => '--Select Content--')+$contents; 
        $contentId = new Zend_Form_Element_Select('content_id');
        $contentId->setLabel('Select Content')
                ->setAttrib('class', 'form-select')
                ->setRequired(false)
                ->addMultiOptions($contentOptions);
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('class', 'submit-menu')
                ->setLabel('Submit')
                ->setAttrib('class', 'form-submit')
                ->setRequired(true)
                ->setIgnore(true);

        $this->addElements(array($name, $short_name, $elementType, $address, $location, $contentId, $submit));
        $this->setAttrib('class', 'add-form');

        $this->setDecorators(array(
            'FormElements',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'element node-form')),
            'Form'
        ));
        $this->setElementDecorators(array(
            'viewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'Label')),
            array('Label', array('tag' => 'div')),
            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-item'))
        ));
        $decorators = NepalAdvisor_Decorators::getBlank();
        $this->setElementDecorators($decorators);
        $submit->setDecorators(array('ViewHelper'));
        $submit->removeDecorator('label');
    }

}

?>