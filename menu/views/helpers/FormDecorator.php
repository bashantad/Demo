<?php
class Admin_View_Helper_FormDecorator extends Zend_View_Helper_Abstract
{
	public function setCustomDecorator($param)
	{
		echo $param;exit;
		$this->setDecorators(array( 

               'FormElements',

               array(array('data'=>'HtmlTag'),array('tag'=>'div')),

               'Form' 

       ));
       $this->setElementDecorators(array('ViewHelper',
			   'Label',
               'Errors', array(array('data'=>'HtmlTag'), array('tag' => 'div')),

       ));
       /*$submit->setDecorators(array('ViewHelper',			  
               'Description',
               'Errors', array(array('data'=>'HtmlTag'), array('tag' => 'div'))
       )); */  
       return $this;
	}
	
}