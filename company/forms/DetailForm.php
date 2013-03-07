<?php

class Company_Form_DetailForm extends Zend_Form
{

    public function init()
    {

        $this->setMethod('post');
        $desc = new Zend_Form_Element_Textarea('desc');
        $desc->setLabel('Description')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);
        $featureMapModel = new Company_Model_Feature();
        $features = $featureMapModel->fetchHierarchy();
        $company_feature_id = new Zend_Form_Element_MultiCheckbox('company_feature_id');
        $company_feature_id->setLabel('Features')
                ->setAttrib('class','form-select')
                ->setRequired(false)
                ->addMultiOptions($features);
        

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('class', 'submit-menu')
                ->setLabel('Submit')
                ->setAttrib('class', 'form-submit')
                ->setRequired(true)
                ->setIgnore(true);

        $this->addElements(array($company_feature_id,$desc, $submit));
        $this->setAttrib('class', 'add-form');
        $this->getElement('company_feature_id')->addDecorator('Label', array('placement' => 'PREPEND'));
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
        $submit->setDecorators(array('ViewHelper'));
        $submit->removeDecorator('label');
    }

}

?>