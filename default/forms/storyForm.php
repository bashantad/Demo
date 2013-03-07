<?php

class Default_Form_storyForm extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $id = new Zend_Form_Element_Hidden('inspire_id');

        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Package Name')
                ->setAttrib('class', 'form-text')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Cannot be empty')))
                ->setRequired(true);

        $desc = new Zend_Form_Element_Textarea('description');
        $desc->setLabel('Description')
                ->setAttrib('class', 'form-text')
                ->setAttribs(array('Cols' => '60', 'Rows' => '10'))
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Cannot be empty')))
                ->setRequired(true);

        $inspire_dt = new Zend_Form_Element_Text('inspire_dt');
        $inspire_dt->setLabel('Visit Date')
                ->setAttrib('class', 'form-text')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Cannot be empty')))
                ->setRequired(true);

        $uploadFrontPath = UPLOAD_PATH . "/../../package/inspirations/front/images";
        $uploadImage = new Zend_Form_Element_File('front');
        $uploadImage->setLabel('Upload Image:')
                ->setDestination($uploadFrontPath)
                ->setMaxFileSize(2097152) // limits the filesize on the client side
                ->addValidator('Size', false, 2097152)
                ->addValidator('Extension', false, 'jpg,png,gif,jpeg')
                ->addValidator(new Zend_Validate_File_ImageSize(array(
                            'minheight' => 256, 'minwidth' => 256
                        )))
                ->addValidator(new Zend_Validate_File_IsImage())
                ;

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('class', 'submit-menu')
                ->setLabel('Submit')
                ->setAttrib('class', 'form-submit')
                ->setRequired(true)
                ->setIgnore(true);

        $this->addElements(array($id, $title, $desc, $inspire_dt, $uploadImage, $submit));
        $this->setAttrib('class', 'add-form');

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
        $id->setDecorators(array('ViewHelper'));
        $submit->setDecorators(array('ViewHelper'));
        $submit->removeDecorator('label');
    }

}

