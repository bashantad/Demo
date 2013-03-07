<?php

class Content_Form_ContentForm extends Zend_Form
{

    public function init()
    {

        $decorators = NepalAdvisor_Decorators::getBlank();
        $this->setMethod('post')
                ->setAttrib('id', 'formid');
        $id = new Zend_Form_Element_Hidden('content_id');

        $contentType = new Zend_Form_Element_Hidden('parent_id');
        $oldHeading = new Zend_Form_Element_Hidden('oldHeading');

        $heading = new Zend_Form_Element_Text('heading');
        $heading->setLabel('Heading:')
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-text')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty',
                    )
                ));

        $keyWord = new Zend_Form_Element_Text('keyword');
        $keyWord->setLabel('Keyword:')
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-text')
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty'
                        )))
                ->setRequired(true);

        $titleTag = new Zend_Form_Element_Text('title_tag');
        $titleTag->setLabel('Title Tags:')
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-text')
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty'
                        )))
                ->setRequired(true);

        $descTag = new Zend_Form_Element_Text('desc_tag');
        $descTag->setLabel('Description Tags:')
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-text')
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty'
                        )))
                ->setRequired(true);

        $shortDesc = new Zend_Form_Element_Text('short_desc');
        $shortDesc->setLabel('Short Description:')
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty'
                        )))
                ->addValidators(array(array(
                        'StringLength', true, array(6, 50, 'messages' => array(
                                Zend_Validate_StringLength::INVALID => 'Invalid description entered',
                                Zend_Validate_StringLength::TOO_SHORT => 'Your description is too short.',
                                Zend_Validate_StringLength::TOO_LONG => 'Your description is too long.',
                        )))))
                ->setAttrib('maxlength', 50)
                ->setAttrib('size', '83')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);

        $oneLineDesc = new Zend_Form_Element_Textarea('one_line_desc');
        $oneLineDesc->setLabel('One Line Description:')
                ->setAttribs(array('Cols' => '80', 'Rows' => '1'))
                ->setAttrib('class', 'form-textarea')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty',
                        )));
        $twoLineDesc = new Zend_Form_Element_Textarea('two_line_desc');
        $twoLineDesc->setLabel('Two Line Description:')
                ->setAttribs(array('Cols' => '80', 'Rows' => '2'))
                ->setAttrib('class', 'form-textarea')
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty'
                        )))
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty',
                        )));
        $threeLineDesc = new Zend_Form_Element_Textarea('three_line_desc');
        $threeLineDesc->setLabel('Three Line Description:')
                ->setAttribs(array('Cols' => '80', 'Rows' => '3'))
                ->setAttrib('class', 'form-textarea')
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty'
                        )))
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty',
                        )));
        $oneParaDesc = new Zend_Form_Element_Textarea('one_para_desc');
        $oneParaDesc->setLabel('Paragraph Description:')
                ->setAttrib('rows', '15')
                ->setAttrib('class', 'form-textarea')
                ->setRequired(FALSE);

        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Description:')
                ->setAttrib('class', 'form-textarea')
                ->addValidator('NotEmpty', true, array('messages' => array(
                        'isEmpty' => 'Cannot be empty'
                        )))
                ->setRequired(true);

        /* $image = new Zend_Form_Element_TextArea('files');
          $image->setLabel('Upload:')
          ->setAttrib('readonly','readonly')
          ->setAttrib('onclick',"openKCFinder(this)")

          ->setRequired(true); */

        $imageLink = new Zend_Form_Element_Hidden('content_image_link');
        $imageLink->setLabel('Choose Display Image')
                ->setAttrib('readonly', 'readonly')
                ->setAttribs(array('style' => "width:350px;cursor:pointer"))
                ->setDescription('<div id="image" onclick="openKCFinder(this)"><div style="margin:5px">Click here to choose an image</div></div>');

        $weight = new Zend_Form_Element_Text('order_no');
        $weight->setLabel('Weight')
                ->addValidator('Digits')
                ->setAttrib('class', 'form-text')
                ->setRequired(true);

        $leaf = new Zend_Form_Element_Radio('is_leaf');
        $leaf->setLabel('Show Content On Tab?')
                ->setValue('N')
                ->setAttribs(array('class' => 'leaf-radio'))
                ->setRequired(true)
                ->addMultiOptions(array(
                    'Y' => 'Yes',
                    'N' => 'No'
                ))
                ->setSeparator('');
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton')
                ->setAttrib('class', 'form-submit btn-content-save tree-content-submit')
                ->setLabel('Submit')
                ->setIgnore(true);

        $this->addElements(array($leaf, $id, $contentType, $heading, $keyWord, $titleTag, $descTag, $shortDesc, $oneLineDesc, $twoLineDesc, $threeLineDesc, $oneParaDesc, $description, $oldHeading, $imageLink, $weight, $submit));
        $this->setAttrib('name', 'add-form');

        $this->setDecorators(array(
            'FormElements',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'element node-form'), array('Label',)),
            'Form'
        ));
        $this->setElementDecorators($decorators);
        $leaf->setDecorators(
                array(
            'ViewHelper',
            'Label',
            array('HtmlTag', array('tag' => 'div', 'placement' => 'REPLACE', 'class' => 'radioBtn'))
                )
                , array('is_leaf')
        );
        $id->setDecorators(array('ViewHelper'));
        $contentType->setDecorators(array('ViewHelper'));
        $oldHeading->setDecorators(array('ViewHelper'));
        $submit->setDecorators(array('ViewHelper'));
        $submit->removeDecorator('label');
    }

}

?>