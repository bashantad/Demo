<?php

class Default_Form_SettingsForm extends Zend_Form
{

    public function init()
    {
        $this->setName("login");
        $this->setMethod('post');

        $this->addElement('text', 'full_name', array(
            'filters' => array('StringTrim'),
//            'validators' => array(
//                array('StringLength', false, array(0, 50)),
//                array('Alnum')
//                ),
            'validators' => array(
                  array('regex', false, array(
                  'pattern'   => "#^[a-z0-9\x20]+$#i",
                  'messages' => 'Only alphanumeric and space are allowed'
                      ))
              ),
            'label' => 'Full Name:',
            'class' => 'form-text',
            'size'  =>'25'
        ));

        $this->addElement('text', 'username', array(
            'filters' => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
                array('Alnum')
                // array('Db_NoRecordExists', false, array('table' => 'nad_user', 'field' => 'username')),
            ),
            'label' => 'Username:',
            'class' => 'form-text',
            'size'  =>'25'
        ));
        //$this->addElement('text', 'email', array(
        //    'filters' => array('StringTrim', 'StringToLower'),
        //    'validators' => array(
        //        array('StringLength', false, array(0, 50)),
        //        array('EmailAddress')
        //        //array('Db_NoRecordExists', false, array('table' => 'nad_user', 'field' => 'email')),
        //    ),
        //    'required' => true,
        //    'label' => 'Email:',
        //    'id'=>'email-field',
        //    'class' => 'form-text',
        //    'size'  =>'25'
        //));



        $this->addElement('password', 'password', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
            ),
            'label' => 'Password:',
            'id'=>'password-field',
            'class' => 'form-text',
            'size'  =>'25'
        ));

        $countryModel = new User_Model_DbTable_Countries();
        $countries = $countryModel->getCountries();
        $allCountries = array();
        foreach ($countries as $key => $val) {
            $arr = explode('::', $key);
            $allCountries[$arr[0]] = $val;
        }

        $this->addElement('select', 'country_id', array(
            'label' => 'Select Country',
            'class' => 'form-text',
            'multiOptions' => $allCountries,
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
            ),
        ));

        $this->addElement('text', 'address', array(
            'filters' => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
            ),
            'label' => 'Address:',
            'class' => 'form-text',
            'size'  =>'25'
        ));

        $this->addElement('submit', 'save', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Save',
            'id' => 'signin_submit',
            'setDecorator' => array('ViewHelper'),
            'removeDecorator' => 'label'
        ));

        $this->setDecorators(array(
            'FormElements',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'element')),
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
        $this->save->setDecorators(array('ViewHelper'));
        $this->save->removeDecorator('label');
    }

}

?>
