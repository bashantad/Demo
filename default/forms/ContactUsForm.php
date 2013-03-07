<?php

class Default_Form_ContactUsForm extends Zend_Form
{

    protected $_param;

    public function __construct($options = null)
    {
        $this->_param = $options;
        parent::__construct($options);
    }

    public function init()
    {        
        $this->setName("contact-us");
        $this->setMethod('post');

        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Your name')
                ->setRequired(true)
                ->addFilters(array('StringTrim'));
        
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Your Email Address')
                ->addValidator('StringLength', false, array(0, 50))
                ->addFilters(array('StringTrim', 'StringToLower'))
                ->setRequired(true)
                //->addValidator(new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_ALL))
                ->addValidator(new Zend_Validate_EmailAddress())
                ->setAttrib('class', 'txtbx');

        $subject = new Zend_Form_Element_Text('subject');
        $subject->setLabel('Subject')
                ->setRequired(true);
        
        $message = new Zend_Form_Element_Textarea('message');
        $message->setLabel('Message')
                ->setAttribs(array('rows'=>'5', 'cols'=>40))
                ->setRequired(true);
        
        $submit = new Zend_Form_Element_Submit('send');
        $submit->setAttribs(array('id' => 'btn-contact-us-send'))
                ->setLabel('Send')
                ->setRequired(false);

        $this->addElements(array($name, $email, $subject, $message, $submit));
        $this->setAttrib('class', 'add-form');

        $this->setElementDecorators(array(
            'viewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'field-data')),
            array('Label', array('tag' => 'div')),
            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-item'))
        ));
        $submit->removeDecorator('label');
        
        /*
        if ($this->_param) {
            $submit->setDecorators(array(
                'viewHelper',
                'Errors',array(array('row'=>'HtmlTag'),array('tag'=>'div','class'=>'user-submit-wrapper'))
            ));
        }*/
        
    }

}

