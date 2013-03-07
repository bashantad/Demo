<?php
class Content_Form_UploadForm extends Zend_Form
{
        public function init()
        {
            $this->setMethod('post');
     
            $title = new Zend_Form_Element_Text('title', array(
                'label' => 'Title',
                'required' => false,
                'filters' => array(
                    'StringTrim'
                ),
                'validators' => array(
                    array('StringLength', false, array(3, 100))
                ),
            ));
     
            $picture = new Zend_Form_Element_File('picture', array(
                'setIsArray'=>true,
                'Multiple' => true,
                //'setMultiFile' => '5',
                'label' => 'Picture',
                'setOptions'=> array('useByteString' => false),
                'required' => true,
                'MaxFileSize' => 2097152, // 2097152 bytes = 2 megabytes
                'validators' => array(
                    array('Count', false, array('min'=>0,'max' => 5)),
                    array('Size', false, 2097152),
                    array('Extension', false, 'gif,jpg,jpeg,png'),
                     array('Size', false, 1024000)

                  /*  array('ImageSize', false, array('minwidth' => 100,
                                                    'minheight' => 100,
                                                    'maxwidth' => 1000,
                                                    'maxheight' => 1000))*/
                )
            ));
     
            $picture->setValueDisabled(true);
     
            $submit = new Zend_Form_Element_Submit('upload', array(
                'label' => 'Upload'
            ));
     
            $this->addElements(array(
                $title,
                $picture,
                $submit
            ));
        }
    }     


?>
