<?php

class Company_Form_DateForm extends Zend_Form
{

    public function init()
    {
        $this->addPrefixPath('NepalAdvisor_Form', 'NepalAdvisor/Form');
        $value = 2;
        $options = array('0' => '--Select--', '1' => 'Kathmandu', '2' => 'Bhaktapur');
//        $selection = new Zend_Element_Selection('my_name');
//        $selection->setLabel('My name');
//        $this->addElement($selection);
        //$asldflsdjk = new Zend_
        $this->addElement('selection', 'Select Parent Feature', array(
            'label' => 'Date of birth:',
            'value' => $value,
            'selectAttribs' => array('class' => 'form-select', 'multiOptions' => $options),
            'textAttribs' => array('class' => 'form-text'),
        ));
        $this->addElement('submit', 'Go');
    }

}