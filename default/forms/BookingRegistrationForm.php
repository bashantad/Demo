<?php

class Default_Form_BookingRegistrationForm extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email:')
                ->setAttribs(array('class'=> 'form-text','id'=>'email-field','size'=>'23'))
                ->setRequired(true)
                ->addValidator('EmailAddress')
                ->addErrorMessage('Please enter valid email address');
                

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password:')
                ->setAttribs(array('class'=> 'form-text','id'=>'password-field','size'=>'23'))
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array("messages"=>"Please enter your password"));
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
                ->setLabel("Register");


        $this->addElements(array($email, $password, $captcha, $submit));
        $this->setAttrib('class', 'add-form');
        $this->setElementDecorators(array(
            'viewHelper',
            'Errors', array()
        ));
        //$decorators = NepalAdvisor_Decorators::getBlank();
        //$this->setElementDecorators($decorators);
        $submit->setDecorators(array(
            'viewHelper',
            'Errors', array()
        ));
        $submit->removeDecorator('label');
    }

}

