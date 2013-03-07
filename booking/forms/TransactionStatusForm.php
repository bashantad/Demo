<?php

class Booking_Form_TransactionStatusForm extends Zend_Form
{

    public function init()
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "transaction.ini", 'production');
        
        $this->setMethod("post")->setEnctype("multipart/form-data");
        $form = array();
        
        $paymentstatus = array_map('ucfirst',array_flip($config->paymentstatus->toArray()));
        $form['payment_status'] = new Zend_Form_Element_Select('payment_status');
        $form['payment_status']->setLabel('Payment Status')
                ->setAttrib('class', 'form-select')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Cannot be empty')))
                ->setRequired(true)
                ->addMultiOptions($paymentstatus);
        
        $form["remarks"] = new Zend_Form_Element_Textarea("remarks");
        $form["remarks"]->setLabel("Remarks")
                ->setRequired(true)
                ->setAttrib("cols", "30")
                ->setAttrib("rows", "5");
        
        $this->addElements($form);
        $this->setAttrib("class", "add-form");

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
        $submit = new Zend_Form_Element_Submit("submit");
        $submit->setAttrib("class", "submit-menu")
                ->setLabel("Submit")
                ->setAttrib("class", "form-submit")
                ->setRequired(true)
                ->setIgnore(true);
        
        $this->addElements(array($submit));
    }

}

?>