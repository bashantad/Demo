<?php

class Content_Model_NadContentTagMapMapper
{

    /**
     * @var Zend_Db_Table_Abstract
     */
    protected $_dbTable;

    /**
     * Specify Zend_Db_Table instance to use for data operations
     * 
     * @param  Zend_Db_Table_Abstract $dbTable 
     * @return Content_Model_NadContentTagMapper
     */
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable ();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * Get registered Zend_Db_Table instance
     *
     * Lazy loads Content_Model_DbTable_Content if no instance registered
     * 
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Content_Model_DbTable_NadContentTagMap');
        }
        return $this->_dbTable;
    }

    public function insert(Content_Model_NadContentTagMap $contentTagMap)
    {
        if (is_array($contentTagMap->getElement_Category_id())) {
            foreach ($contentTagMap->getElement_Category_id() as $key => $element) {
                $contentTagMapData = array(
                    'content_id' => $contentTagMap->getContent_id(),
                    'element_category_id' => $key,
                    'default_tag' => $contentTagMap->getDefault_tag());
                $isInserted = $pkId = $this->getDbTable()->insert($contentTagMapData);
            }
        } else {
            $contentTagMapData = array(
                'content_id' => $contentTagMap->getContent_id(),
                'element_category_id' => $contentTagMap->getElement_Category_id(),
                'default_tag' => $contentTagMap->getDefault_tag());
            $isInserted = $pkId = $this->getDbTable()->insert($contentTagMapData);
        }
        if (!$isInserted) {
            throw new Zend_Db_Exception('Cannot insert data.');
        }
        return $this->getDbTable()->getAdapter()->lastInsertId();
    }

    public function update(Content_Model_NadContentTagMap $contentTagMap)
    {
        $rowsAffected = 0;
        if (is_array($contentTagMap->getElement_Category_id())) {
            foreach ($contentTagMap->getElement_Category_id() as $key => $element) {
                $contentTagMapData = array(
                    'content_id' => $contentTagMap->getContent_id(),
                    'element_category_id' => $key);
                $cid = $contentTagMap->getContent_id();
                $isUpdated = $rowsAffected = $this->getDbTable()->update($contentTagMapData, array('content_id = ?' => $cid));
            }
        } else {
            $contentTagMapData = array(
                'default_tag' => $contentTagMap->getDefault_tag());
            $where = 'content_id = ' . $contentTagMap->getContent_id() . ' AND element_category_id = ' . $contentTagMap->getElement_Category_id();
            $rowsAffected = $this->getDbTable()->update($contentTagMapData, $where);
        }
        return $rowsAffected;
    }

    public function delete(Content_Model_NadContentTagMap $contentTagMap, $type = null)
    {
        $cid = (int) $contentTagMap->getContent_id();
        if (!$cid) {
            throw new Exception('Content ID not found.');
        }
        $type = $type ? " AND B.type='{$type}'" : '';
        $sql = "DELETE A.* From 
            nad_content_tag_map as A
            join nad_element_category As B ON A.element_category_id = B.element_category_id            
            WHERE A.content_id = {$cid}{$type}";
        $rowsAffected = Zend_Db_Table::getDefaultAdapter()->query($sql);
        return $rowsAffected;
    }

    public function save(Content_Model_NadContentTagMap $contentTagMap)
    {
        if (!($id = $content->getContent_id())) {
            $contentTagMapData = $contentTagMap->toArray();
            unset($contentTagMapData ['content_id']);
            $isInserted = $pkId = $this->getDbTable()->insert($contentTagMapData);
            if (!$isInserted) {
                throw new Zend_Db_Exception('Cannot Insert Data');
            }
        } else {
            $rowsAffected = $isUpdated = $this->getDbTable()->update($data);
            if (!$isUpdated) {
                throw new Zend_Db_Exception('Cannot Update Data');
            }
        }
    }

    public function find($id)
    {
        $resultSet = $this->getDbTable()->find($id);
        if (0 == count($resultSet)) {
            return false;
        }
        return $resultSet;
    }

    public function getTagsByContentId($id)
    {
        $select = $this->getDbTable()->select()
                ->from(array('nad_content_tag_map'), array('element_category_id'))
                ->where('content_id = ' . $id);
        $resultSet = $this->getDbTable()->fetchAll($select)->toArray();
        //var_dump($resultSet);
        return $resultSet;
    }
    
    public function getTagsByContentIdWithoutDefault($id)
    {
        $select = $this->getDbTable()->select()
                ->from(array('nad_content_tag_map'), array('element_category_id'))
                ->where('content_id = ' . $id)
                ->where('default_tag IS NULL');
        $resultSet = $this->getDbTable()->fetchAll($select)->toArray();
        //var_dump($resultSet);
        return $resultSet;
    }

    public function hasDefaultTag($cid, $ecid)
    {
        $select = $this->getDbTable()->select()
                ->from(array('nad_content_tag_map'))
                ->where("content_id = {$cid} AND element_category_id = {$ecid}");

        $rs = $this->getDbTable()->fetchRow($select);
        if ($rs) {
//            $data = $rs->toArray();
//            if ('Y' == $data['default_tag']) {
//                return true;
//            }
//            else {
//                return false;
//            }
            return true;
        } else {
            return false;
        }
    }

    public function suggestedHotels($cid)
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'element_id');
        
        $tags = $this->getTagsByContentIdWithoutDefault($cid);
        $tagStrings ='';
        $count = count(array_keys($tags));
        
        foreach($tags as $key => $val){
            if($key+1 == $count){
                $tagStrings .= $val['element_category_id'];
            }
            else{
                $tagStrings .= $val['element_category_id'].",";
            }
        }
        $sql = "SELECT distinct(T.content_id), C.heading, C.content_image_link, C.file_name, C.file_path, C.three_line_desc
                FROM nad_content_tag_map as T
                JOIN nad_content as C on T.content_id = C.content_id 
                WHERE T.element_category_id in ($tagStrings)
                AND C.content_id <> $cid
                AND C.element_id = {$config->hotels->id}
                limit 7";
                
        $result = $this->getDbTable()->getAdapter()->fetchAll($sql);
        return $result;
    }

}

?>
