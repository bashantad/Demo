<?php

class Content_Model_ContentMapMapper
{

    /**
     * @var Zend_Db_Table_Abstract
     */
    protected $_dbTable;

    /**
     * Specify Zend_Db_Table instance to use for data operations
     * 
     * @param  Zend_Db_Table_Abstract $dbTable 
     * @return Content_Model_ContentMapper
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
            $this->setDbTable('Content_Model_DbTable_ContentMap');
        }
        return $this->_dbTable;
    }

    public function insert(Content_Model_ContentMap $contentMap)
    {
        $contentMapData = array(
            'content_id' => $contentMap->getContent_id(),
            'parent_id' => $contentMap->getParent_id(),
            'level' => $contentMap->getLevel());
        $isInserted = $pkId = $this->getDbTable()->insert($contentMapData);
        if (!$isInserted) {
            throw new Zend_Db_Exception('Cannot insert data.');
        }

        return $this->getDbTable()->getAdapter()->lastInsertId();
    }

    /* public function updateParentId($parentId, $contentMap)
      {
      $contentData = array(
      'content_id' => $contentMap->getContent_id(),
      'parent_id' => $contentMap->getParent_id(),
      'level' => $contentMap->getLevel());
      );
      } */

    public function update(Content_Model_ContentMap $contentMap)
    {
        //$level = $this->getLevel($contentMap->getParent_id());
        $contentMapData = array(
            'parent_id' => $contentMap->getParent_id(),
            'level' => $this->getLevel($contentMap->getParent_id())
        );
        $cid = $contentMap->getContent_id();
        //$profiler = Zend_Db_Table_Abstract::getDefaultAdapter()->getProfiler()->setEnabled(true); 
        $isUpdated = $rowsAffected = $this->getDbTable()->update($contentMapData, array('content_id = ?' => $cid));
        //var_dump($profiler->getLastQueryProfile()->getQuery());
        /*  if (!$isUpdated) {
          throw new Zend_Db_Exception('Cannot update data.');
          } */

        return $cid;
    }

    public function getOrderOfSiblings($parent_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = 'select c.order_no from nad_content as c inner join nad_content_map as cm on c.content_id=cm.content_id where cm.parent_id=' . $parent_id . ' order by c.order_no';
        $results = $db->fetchAll($sql);
        $data = array();
        foreach ($results as $result) {
            $data[] = $result->order_no;
        }
        return $data;
    }

    public function delete(Content_Model_ContentMap $contentMap)
    {
        $cid = (int) $contentMap->getContent_id();
        if (!$cid) {
            throw new Exception('Content ID not found.');
        }

        $where = Zend_Db_Table::getDefaultAdapter()->quoteInto('content_id = ?', $cid);
        $isDeleted = $rowsAffected = $this->getDbTable()->delete($where);
        if (!$isDeleted) {
            throw new Exception('Delete Failed!!!');
        }

        return $rowsAffected;
    }

    public function save(Content_Model_ContentMap $contentMap)
    {
        if (!($id = $content->getContent_id())) {
            $contentMapData = $contentMap->toArray();
            unset($contentMapData ['content_id']);
            $isInserted = $pkId = $this->getDbTable()->insert($contentMapData);
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
        $row = $resultSet->current();
        return $row;
    }

    public function getLevel($parent_id)
    {
        if ($parent_id == NULL) {
            $parent_id = "0";
        }
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT level
                FROM nad_content_map 
                where content_id = " . $parent_id;
        $result = $db->fetchRow($sql);
        $result->level++;
        return $result->level;
    }

    public function getMainParent($contentId)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select();
        $select->from(array('c' => 'nad_content'), array('c.heading as parent_name', 'c.content_id', 'cm.level', 'cm.parent_id'))
                ->join(array('cm' => 'nad_content_map'), 'c.content_id=cm.content_id')
                ->where('c.content_id=' . $contentId);
        $result = $db->fetchRow($select);
        if ($result->parent_id != '0') {
            $result = $this->getMainParent($result->parent_id);
        }
        return $result;
    }

    public function searchContent($params=null)
    {
        $where = '';
        if($params){
            $where = " AND c.content_id in ({$params})";    
        }
        $sql = "SELECT c.content_id, c.heading, c.description, e.name AS element, c.entered_by, DATE_FORMAT( c.entered_dt, '%d-%M-%Y' ) as entered_date\n"
                . "FROM `nad_content` AS c\n"
                . "LEFT JOIN nad_element AS e ON c.element_id = e.element_id where c.is_leaf='N'".$where;
        $db = Zend_Db_Table::getDefaultAdapter();
        $results = $db->fetchAll($sql);
        $contentIds = array();
        $allResults = array();
        $i = 0;
        foreach ($results as $result) {
            array_push($contentIds, $result->content_id);
            $allResults[$result->content_id] = (array) $result;
        }
        $contentIds = implode(",", $contentIds);
        $sql = "SELECT content_id, file_name from nad_content_file where content_id in ($contentIds)";
        $files = $db->fetchAll($sql);
        $sql = "SELECT map.content_id,ec.name as tag_name from nad_content_tag_map as map inner join nad_element_category as ec on map.element_category_id=ec.element_category_id where content_id in ($contentIds)";
        $allTags = $db->fetchAll($sql);
        $sql = "SELECT map.content_id,map.parent_id,c.heading,c.description from nad_content_map as map inner join nad_content as c on c.content_id=map.content_id where map.parent_id in ($contentIds) AND c.is_leaf='Y'";
        $allTabs = $db->fetchAll($sql);
        $tabs = array();
        foreach ($allTabs as $tab) {
            $tabs[$tab->parent_id][] = ucwords($tab->heading)."(".str_word_count(strip_tags($tab->description)).")";
        }
        $contentFiles = array();
        $tags = array();
        foreach ($allTags as $tag) {
            $tags[$tag->content_id][] = ucwords($tag->tag_name);
        }
        foreach ($files as $file) {
            $contentFiles[$file->content_id][] = $file->file_name;
        }
        $contents = array();
        $finalResult = array();
        foreach ($allResults as $key => $value) {
            $i++;
            $contents['sn'] = $i;
            $contents['content_id'] = $value['content_id'];
            $contents['heading'] = $value['heading'];
            //$contents['description'] = $value['description'];
            $contents['word_length'] = str_word_count(strip_tags($value['description']));
            $contents['element'] = $value['element'];
            if (isset($tabs[$key])) {
                $contents['tab_name'] = implode(', ', $tabs[$key]);
                
            } else {
                $contents['tab_name'] = null;
            }
            if (isset($tags[$key])) {
                $contents['tag_name'] = implode(', ', $tags[$key]);
            } else {
                $contents['tag_name'] = null;
            }
            if (isset($contentFiles[$key])) {
                $contents['no_of_images'] = sizeof($contentFiles[$key]);
            } else {
                $contents['no_of_images'] = 0;
            }
            $contents['entered_by'] = $value['entered_by'];
            $contents['entered_date'] = $value['entered_date'];
            $finalResult[$key] = $contents;
        }
        return $finalResult;
    }
    public function searchContentByLocationMap($contentIds, $selected)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        if($selected=="Y"){
            $where = " AND c.content_id in ($contentIds)";
        }else{
            $where = " AND c.content_id not in ($contentIds)";
        }
        $sql = "SELECT c.content_id, c.heading as content, l.name as location from nad_content as c\n".
                "LEFT JOIN nad_location_mst as l on c.content_id=l.content_id WHERE c.is_leaf='N'".$where;
        $results = $db->fetchAll($sql);
        $allResults = array();
        foreach($results as $result)
        {
            $allResults[] = (array)$result;
        }
        return $allResults;
    }
    public function searchContentByCompanyMap($contentIds, $selected)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        if($selected=="Y"){
            $where = " AND c.content_id in ($contentIds)";
        }else{
            $where = " AND c.content_id not in ($contentIds)";
        }
        $sql = "SELECT c.content_id, c.heading as content, comp.name as company from nad_content as c\n".
                "LEFT JOIN nad_company_mst as comp on c.content_id=comp.content_id WHERE c.is_leaf='N'".$where;
        $results = $db->fetchAll($sql);
        $allResults = array();
        foreach($results as $result)
        {
            $allResults[] = (array)$result;
        }
        return $allResults;
    }
    
    public function getChildren($contentId)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT c.content_id from nad_content as c INNER JOIN nad_content_map as cm on c.content_id=cm.content_id where c.is_leaf='N' AND cm.parent_id=$contentId";
        $results  = $db->fetchAll($sql);
        $allResults = array();
        foreach($results as $result)
        {
            array_push($allResults, $result->content_id);
        }
        return implode(",", $allResults);
    }
    public function getName($contentId)
    {
        if(!$contentId){
            return "All Contents";
        }
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT heading from nad_content where content_id=".$contentId;
        $result = $db->fetchAll($sql);
        return $result[0]->heading;
    }
    
    public function getDetailById($id)
    {
        $sql = "SELECT c.*, ec.name as element_category, el.name as content_type from nad_content as c"
                ." left join nad_element_category as ec on c.element_category_id=ec.element_category_id"
                ." left join nad_element as el on el.element_id=c.element_id"
                ." where c.content_id='$id'";
        $db = Zend_Db_Table::getDefaultAdapter();
        $results = $db->fetchAll($sql);
        $result = '';
        if($results){
            $result = (array)$results[0];
            $result['is_leaf'] = ($result['is_leaf']=='N')?"No":"Yes";
            $result['status'] = ($result['status']=='E')?"Enabled":"Disabled";
            $result['approved'] = ($result['approved']=='N')?"No":"Yes";
            unset($result['element_id']);
            unset($result['element_category_id']);
            unset($result['language_id']);
            unset($result['order_no']);
            unset($result['menu_id']);
        }
        return $result;
    }
    
    public function getAllContents($params=null)
    {
        $where = '';
        if($params){
            $where = " AND c.content_id in ({$params})";    
        }
        $sql = "SELECT c.content_id, c.heading, c.description, e.name AS content_type, c.location,c.keyword, c.title_tag,"
                ." c.desc_tag,c.one_line_desc,c.two_line_desc,c.three_line_desc,c.one_para_desc,c.short_desc," 
                ." if(c.approved='Y','Yes','No')approved, c.entered_by, DATE_FORMAT( c.entered_dt, '%d-%M-%Y' ) as entered_date\n"
                . "FROM `nad_content` AS c\n"
                . "LEFT JOIN nad_element AS e ON c.element_id = e.element_id where c.is_leaf='N'".$where;
        $db = Zend_Db_Table::getDefaultAdapter();
        $results = $db->fetchAll($sql);
        $contentIds = array();
        $allResults = array();
        $i = 0;
        foreach ($results as $result) {
            array_push($contentIds, $result->content_id);
            $allResults[$result->content_id]["content"] = (array) $result;
        }
        $contentIds = implode(",", $contentIds);
        $sql = "SELECT content_id, file_name from nad_content_file where content_id in ($contentIds)";
        $files = $db->fetchAll($sql);
        $sql = "SELECT map.content_id,ec.name as tag_name from nad_content_tag_map as map inner join nad_element_category as ec on map.element_category_id=ec.element_category_id where content_id in ($contentIds)";
        $allTags = $db->fetchAll($sql);
        $sql = "SELECT map.content_id,map.parent_id,c.heading,c.description from nad_content_map as map inner join nad_content as c on c.content_id=map.content_id where map.parent_id in ($contentIds) AND c.is_leaf='Y'";
        $allTabs = $db->fetchAll($sql);
        $tabs = array();
        foreach ($allTabs as $tab) {
            $allResults[$tab->parent_id]["tabs"][] = (array)$tab;
        }
        $tags = array();
        foreach ($allTags as $tag) {
            $tags[$tag->content_id][] = ucwords($tag->tag_name);
        }
        foreach ($tags as $key=>$tag) {
            $allResults[$key]["tags"] = implode(",",$tag);
        }
        $contentFiles = array();
        foreach ($files as $file) {
            $contentFiles[$file->content_id][] = $file->file_name;
        }
        foreach ($contentFiles as $key=>$file) {
            $allResults[$key]["files"] = sizeof($file);
        }
        return $allResults;
    }
    public function getAncestors($id)
    {
        static $ids = array();
        $sql = "SELECT cm.parent_id,c.content_id,c.heading from nad_content_map as cm left join nad_content as c on c.content_id=cm.content_id where c.content_id=".$id;
        $db = Zend_Db_Table::getDefaultAdapter();
        $result = $db->fetchALl($sql);
        if($result){
            if($result[0]->parent_id!=0){
                $id = $this->getAncestors($result[0]->parent_id);
            }
            $ids[] = $result[0];
        }
        return $ids;
    }
}

