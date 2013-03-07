<?php

class Content_Model_DbTable_NadContentTagMap extends Zend_Db_Table_Abstract
{

    protected $_name = 'nad_content_tag_map';
    protected $_primary = array('content_id');
    protected $_referenceMap = array(
        'ContentMap' => array(
            'columns' => array('content_id'), // the column(fk) in the nad_content_map for joining nad_content
            'refTableClass' => 'Content_Model_DbTable_Content',
            'refColumns' => array('content_id'), // column(pk) of the nad_content
            'onDelete' => self::CASCADE
        )
    );

}

?>
