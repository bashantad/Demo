<?php

class Menu_Form_MenuForm extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        //->setAttrib('class', 'editor');
        //$this->setDecorators(self::customElementDecorator());
        $parent_id = new Zend_Form_Element_Hidden('menu_id');
        $level = new Zend_Form_Element_Hidden('level');
        //short_name 	label 	link_url

        $short_name = new Zend_Form_Element_Text('short_name');
        $short_name->setLabel('Short Name')
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);

        $label = new Zend_Form_Element_Text('label');
        $label->setLabel('Menu Label:')
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);

        $link_url = new Zend_Form_Element_Text('link_url');
        $link_url->setLabel('URL')
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-text')
                ->setRequired('true');

        $link_alias= new Zend_Form_Element_Text('link_alias');
        $link_alias->setLabel('URL(alias)')
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-text')
                ->setRequired('true');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Submit')
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-submit submit-menu tree-content-submit')
                ->setRequired(true)
                ->setIgnore(true);

        $this->addElements(array($parent_id, $level, $label, $short_name, $link_url, $link_alias, $submit));
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
//        $decorators = NepalAdvisor_Decorators::getBlank();
//        $this->setElementDecorators($decorators);
        $parent_id->setDecorators(array('ViewHelper'));
        $level->setDecorators(array('ViewHelper'));
        $submit->setDecorators(array('ViewHelper'));
        $submit->removeDecorator('label');
    }

}

?>