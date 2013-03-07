<?php

class Default_Form_LoginForm extends Zend_Form
{

    protected $_param;

    public function __construct($options = null)
    {
        $this->_param = $options;
        parent::__construct($options);
    }

    public function init()
    {
        $this->setName("login");
        $this->setMethod('post');

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email')
                ->addValidator('StringLength', false, array(0, 50))
                ->addFilters(array('StringTrim', 'StringToLower'))
                ->setRequired(true)
                ->addValidator(new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_ALL))
                ->setAttrib('class', 'txtbx');

        $requestUri = new Zend_Form_Element_Hidden('request_uri');
        $uri = $_SERVER['REQUEST_URI'];
        $requestUri->setValue($uri);

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password')
                ->addValidator('StringLength', false, array(0, 50))
                ->addFilters(array('StringTrim', 'StringToLower'))
                ->setRequired(true)
                ->setAttrib('class', 'txtbx');

        $submit = new Zend_Form_Element_Submit('login');
        $submit->setAttribs(array('id' => 'signin_submit'))
                ->setLabel('Login')
                ->setRequired(false);

        $this->addElements(array($email, $requestUri, $password, $submit));
        $this->setAttrib('class', 'add-form');

        $this->setElementDecorators(array(
            'viewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'Label')),
            array('Label', array('tag' => 'div')),
            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-item'))
        ));
        $requestUri->setDecorators(array('viewHelper','Errors',array()));
        if ($this->_param) {
            $submit->setDecorators(array(
                'viewHelper',
                'Errors',array(array('row'=>'HtmlTag'),array('tag'=>'div','class'=>'user-submit-wrapper'))
            ));
        }
        $submit->removeDecorator('label');
        $requestUri->removeDecorator('label');
    }

}

