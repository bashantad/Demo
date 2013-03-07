<?php

class Company_Form_FeatureForm extends Zend_Form
{

    public function init()
    {
        $this->addPrefixPath('NepalAdvisor_Form', 'NepalAdvisor/Form');
        $this->setMethod('post');
        $short_name = new Zend_Form_Element_Text('short_name');
        $short_name->setLabel('Short Name')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);

        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);
        $order_no = new Zend_Form_Element_Text('order_no');
        $order_no->setLabel('Order no')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);
        $elementModel = new Package_Model_Element();
        $elements = $elementModel->getElements();

        $elementType = new NepalAdvisor_Form_Element_Selection('element_id');
        $elementType->setLabel('Company Type')
                ->setTextAttribs(array('class' => 'form-text defined-text', 'size' => '8'))
                ->setSelectAttribs(array('class' => 'form-select defined-select'))
                ->addMultiOptions($elements)
                ->setRequired(true);

        $defined_id = new Zend_Form_Element_Text('defined_id');
        $defined_id->setLabel('Defined Code')
                ->setAttrib('class', 'form-text');

        // $features = array('0' => 'Company type first');
        $featureTypes = array(
            'FLAG' => 'FLAG',
            'DESCRIPTION' => 'DESCRIPTION'
        );
        $featureType = new Zend_Form_Element_Radio('feature_type');
        $featureType->setLabel('Feature Type')
                ->setAttrib('class', 'form-select')
                ->addMultiOptions($featureTypes)
                ->setRequired(true);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Submit')
                ->setAttrib('class', 'form-submit')
                ->setRequired(true)
                ->setIgnore(true);
        $featureModel = new Company_Model_Feature();
        $allFeatures = $featureModel->fetchFeatureOption();
        $upperFeatureId = new NepalAdvisor_Form_Element_Selection('upper_feature_id');
        $upperFeatureId->setLabel('Parent Feature')
                ->setTextAttribs(array('class' => 'form-text defined-text', 'size' => '8'))
                ->setSelectAttribs(array('class' => 'form-select defined-select'))
                ->addMultiOptions($allFeatures);
                
        $this->addElements(array($name, $short_name, $elementType, $featureType, $defined_id, $upperFeatureId, $submit));
        $this->setAttrib('class', 'add-form');
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
        $submit->setDecorators(array('ViewHelper'));
        $submit->removeDecorator('label');
    }

}

?>