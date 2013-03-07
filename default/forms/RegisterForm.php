<?php

class Default_Form_RegisterForm extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $username = new Zend_Form_Element_Text('username');
        $username->setLabel('Full Name:')
                ->setAttribs(array('class'=> 'form-text','id'=>'full-name'))
                ->setRequired(true)
                ->addFilters(array('StringTrim'));
        
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email:')
                ->setAttribs(array('class'=> 'form-text','id'=>'email-field'))
                ->setRequired(true)
                ->addValidator('EmailAddress')
                ->addValidators(array('NotEmpty', array('StringLength', false, array(1, 200))));

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password:')
                ->setAttribs(array('class'=> 'form-text','id'=>'password-field'))
                ->setRequired(true)
                ->addFilters(array('StringTrim'))
                ->addValidators(array('NotEmpty', 'Alnum', array('StringLength', false, array(4, 20))));
        $publickey = '6LcKstASAAAAAFxXkNFV4CnESG9AVfgEbyABS_Ex';
        $privatekey = '6LcKstASAAAAAM2_f3Fkf_0B4tkoAmAglynBo3z2';
        $recaptcha = new Zend_Service_ReCaptcha($publickey, $privatekey);

        $captcha = new Zend_Form_Element_Captcha('captcha', array(
                    'label' => 'Enter the letters given below',
                    'captcha' => 'ReCaptcha',
                    'captchaOptions' => array('captcha' => 'ReCaptcha', 'service' => $recaptcha),
                    'ignore' => true
                        )
        );

        $submit = new Zend_Form_Element_Submit('register');
        $submit->setAttrib('id', 'signin_submit')
                ->setRequired(true)
                ->setLabel("Register");


        $this->addElements(array($email, $password, $captcha, $submit));
        $this->setAttrib('class', 'add-form');

        $this->setDecorators(array(
            'FormElements',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'delement')),
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
        $submit->setDecorators(array(
            'viewHelper',
            'Errors', array()
        ));
        
        //$submit->setDecorators(array('ViewHelper'));
        $submit->removeDecorator('label');
    }

}

