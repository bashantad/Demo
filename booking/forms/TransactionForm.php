<?php

class Booking_Form_TransactionForm extends Zend_Form
{

    public function init()
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "transaction.ini", 'production');
        
        $this->setMethod("post")->setEnctype("multipart/form-data");
        $form = array();
        
        $userModel = new User_Model_User();
        $users = $userModel->listAllUserForSelectBox();
        
        $form['user_id'] = new Zend_Form_Element_Select('user_id');
        $form['user_id']->setLabel('User ')
                ->setAttrib('class', 'form-select')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Cannot be empty')))
                ->setRequired(true)
                ->addMultiOptions($users);

        $form["gross_amount"] = new Zend_Form_Element_Text("gross_amount");
        $form["gross_amount"]->setLabel("Amount")
                ->setAttrib("class", "form-text")
                ->setRequired(true);
        
        $form['debit_credit'] = new Zend_Form_Element_Select('debit_credit');
        $form['debit_credit']->setLabel('Debit/Credit ')
                ->setAttrib('class', 'form-select')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Cannot be empty')))
                ->setRequired(true)
                ->addMultiOptions(array('DEBIT'=>'Debit', 'CREDIT'=>'Credit'));

        $form["quantity"] = new Zend_Form_Element_Text("quantity");
        $form["quantity"]->setLabel("Quantity")
                ->setAttrib("class", "form-text")
                ->setRequired(true);
        
        $paymentstatus = array_map('ucfirst',array_flip($config->paymentstatus->toArray()));
        $form['payment_status'] = new Zend_Form_Element_Select('payment_status');
        $form['payment_status']->setLabel('Payment Status')
                ->setAttrib('class', 'form-select')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Cannot be empty')))
                ->setRequired(true)
                ->addMultiOptions($paymentstatus);
        
        $paymenttypes = array_map('ucfirst',array_flip($config->paymenttype->toArray()));
        $form['payment_type'] = new Zend_Form_Element_Select('payment_type');
        $form['payment_type']->setLabel('Payment Type')
                ->setAttrib('class', 'form-select')
                ->setRequired(true)
                ->addMultiOptions($paymenttypes);
        
        $form["pending_reason"] = new Zend_Form_Element_Textarea("pending_reason");
        $form["pending_reason"]->setLabel("Pending Reason")
                ->setAttrib("cols", "30")
                ->setAttrib("rows", "5");
        
        $form["date_creation"] = new Zend_Form_Element_Text("date_creation");
        $form["date_creation"]->setLabel("Transaction Date")
                ->setAttrib("class", "form-text")
                ->setRequired(true);
        
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