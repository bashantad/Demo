<?php

/**
 * CommonModel
 * 
 * @author reena
 * @version 
 */

class Content_Model_CommonModel  {
	public function ddlContentTypeList(){
	
	$contentType = 	array(
						'Select' => '---Select---',
						'Places'=>'Places',
						'Activity'=>'Activity',
						'Hotels' => 'Hotels',
						'Transportation'=>'Transportation',
						'Others'=>'Others'
						);
	return $contentType;
	}
}
