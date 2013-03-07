<?php

/**
 * ContentModel
 * 
 * @author reena
 * @version 
 */
class Content_Model_DbTable_Content extends Zend_Db_Table_Abstract
{

    /**
     * The default table name 
     */
    protected $_name = 'nad_content';
    protected $_primary = array('content_id');
    //protected $_dependentTables = array('nad_content_map');
    

}
