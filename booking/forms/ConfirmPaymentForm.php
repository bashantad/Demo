<?php

class Booking_Form_ConfirmPaymentForm extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $amount_confirm = new Zend_Form_Element_Radio('amount_confirm');
        $amount_confirm-> setLabel('Do you Confirm this payment?')
                        ->addMultiOptions(array(
                            '1' => 'YES',
                            '0' => 'NO'
                        ))
                        ->setSeparator('')
                        ->setRequired(true);

        $confirm = new Zend_Form_Element_Submit('confirm');
        $confirm->setAttrib('id', 'submitbutton')
                ->setAttrib('class', 'form-submit')
                ->setLabel('Confirm')
//                ->setRequired(true)
                ->setIgnore(true);

        $this->addElements(array($amount_confirm, $confirm));
        $this->setAttrib('class', 'add-form');

        $this->setDecorators(array(
            'FormElements',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'confirm-amount')),
            'Form'
        ));
        $this->setElementDecorators(array(
            'viewHelper',
            'Errors',
//            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'Label')),
//            array('Label', array('tag' => 'div')),
            array('Label', array('requiredSuffix' => '&nbsp;<span class="form-required">*</span>&nbsp;', 'escape' => false)),
//            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-item'))
        ));
//        $decorators = NepalAdvisor_Decorators::getBlank();
//        $this->setElementDecorators($decorators);
        // $submit->setDecorators(array('ViewHelper'));
        $confirm->removeDecorator('label');
    }

}

?>