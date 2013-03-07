<?php

class Default_Form_PasswordRecoveryForm extends Zend_Form
{

    public function init()
    {
        $email = new Zend_Form_Element_Text("email");
        $email->setLabel("Enter Your Email Address")
                ->addValidator('EmailAddress')
                ->setRequired(true)
                ->setAttribs(array("size" => "30"));
        $submit = new Zend_Form_Element_Submit("submit");
        $submit->setLabel("Reset Your Password")
                ->setAttribs(array("id" => "signin_submit"));
        $this->addElements(array($email, $submit));
        $this->setElementDecorators(array(
            'viewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'Label')),
            array('Label', array('tag' => 'div')),
            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-item'))
        ));
        $submit->removeDecorator("label");
    }

}

?>
