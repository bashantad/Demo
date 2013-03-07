<?php

class Content_Model_ContentMapper
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
            $this->setDbTable('Content_Model_DbTable_Content');
        }
        return $this->_dbTable;
    }

    /**
     *
     * @param Content_Model_Content $content
     * @return type 
     */
    public function insert(Content_Model_Content $content)
    {
        $contentData = array(
            'content_id' => $content->getContent_id(),
            'language_id' => '1',
            'element_id' => $content->getElement_id(),
            'element_category_id' => $content->getElementCategory_id(),
            'heading' => $content->getHeading(),
            'keyword' => $content->getKeyword(),
            'one_line_desc' => $content->getOne_line_desc(),
            'two_line_desc' => $content->getTwo_line_desc(),
            'three_line_desc' => $content->getThree_line_desc(),
            'one_para_desc' => $content->getOne_para_desc(),
            'description' => $content->getDescription(),
            'short_desc' => $content->getShort_desc(),
            'title_tag' => $content->getTitle_tag(),
            'desc_tag' => $content->getDesc_tag(),
            'is_leaf' => $content->getIs_leaf(),
            'order_no' => $content->getOrder_no(),
            'content_image_link' => $content->getContent_image_link(),
            'file_name' => $content->getFile_name(),
            'file_path' => $content->getFile_path(),
            'entered_dt' => date('Y-m-d H:i:s'));

        // Unset the primary keys
        unset($contentData['content_id']);
        $isInserted = $pkId = $this->getDbTable()->insert($contentData);
        if (!$isInserted) {
            throw new Zend_Db_Exception('Cannot insert data.');
        }

        return $pkId; //$this->getDbTable()->getAdapter()->lastInsertId();
    }

    public function updateOrderbyId($data, $content_id)
    {
        $msg = '';
        if ($this->getDbTable()->update($data, 'content_id = ' . $content_id)) {
            $msg = "successful";
        }
        return $msg;
    }

    public function update(Content_Model_Content $content)
    {
        $contentData = array(
            'content_id' => $content->getContent_id(),
            'language_id' => '1',
            'element_id' => $content->getElement_id(),
            'element_category_id' => $content->getElementCategory_id(),
            'heading' => $content->getHeading(),
            'keyword' => $content->getKeyword(),
            'one_line_desc' => $content->getOne_line_desc(),
            'two_line_desc' => $content->getTwo_line_desc(),
            'three_line_desc' => $content->getThree_line_desc(),
            'one_para_desc' => $content->getOne_para_desc(),
            'order_no' => $content->getOrder_no(),
            'description' => $content->getDescription(),
            'short_desc' => $content->getShort_desc(),
            'title_tag' => $content->getTitle_tag(),
            'desc_tag' => $content->getDesc_tag(),
            'is_leaf' => $content->getIs_leaf(),
            'file_name' => $content->getFile_name(),
            'file_path' => $content->getFile_path(),
            'content_image_link' => $content->getContent_image_link(),
            'entered_dt' => date('Y-m-d H:i:s'));

        $cid = $content->getContent_id();
        $isUpdated = $rowsAffected = $this->getDbTable()->update($contentData, array('content_id = ?' => $cid));
        return $cid;
    }

    public function delete(Content_Model_Content $content)
    {
        $cid = (int) $content->getContent_id();
        if (!$cid) {
            throw new Exception('Content ID not found.');
        }

        $tables = $this->getTablesAssociatedWithContentId();
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        foreach ($tables as $table) {
            $tableName = $table['table_name'];
            $sql = "delete from $tableName where content_id='$cid'";
            if (!$db->query($sql)) {
                $msg = false;
                throw new Exception("Delete Failed due To data On " . $table . "!!!");
            }
        }

        $delete = $this->getDbTable()->delete("content_id='$cid'");
        if ($delete) {
            $db->commit();
            $msg = true;
        } else {
            $msg = false;
            throw new Exception("Delete Failed!!!");
            $db->rollback();
        }
        return $msg;



        /* $where = Zend_Db_Table::getDefaultAdapter()->quoteInto('content_id = ?', $cid);
          $isDeleted = $rowsAffected = $this->getDbTable()->delete($where);
          if (!$isDeleted) {
          throw new Exception('Delete Failed!!!');
          }

          return $rowsAffected; */
    }

    public function getTablesAssociatedWithContentId()
    {
        $sql1 = "USE information_schema";
        $sql2 = "USE nad";
        $sql = "SELECT table_name FROM KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'nad_content' AND REFERENCED_COLUMN_NAME = 'content_id'";
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        $db->query($sql1);
        $result = $db->fetchAll($sql);
        $db->query($sql2);
        $db->commit();
        $tables = array();
        foreach ($result as $table) {
            $tables[] = (array) $table;
        }
        return $tables;
    }

    /**
     * Save a content entry
     * 
     * @param  Content_Model_Content $content 
     * @return void
     */
//    public function save(Content_Model_Content $content)
//    {
//        $contentData = $content->toArray();
//        if (empty($contentData)) {
//            return false;
//        }
//        $contentMapData = array();
//        // Instantiate the ContentMapMapper class
//        $contentMapMapper = new Content_Model_ContentMapMapper();
//
//        if (!($cid = $content->getContent_id())) {
//            // Unset the primary empty field
//            unset($contentData['content_id']);
//            $isInserted = $pkId = $this->getDbTable()->insert($contentData);
//            if (!$isInserted) {
//                throw new Zend_Db_Exception('Cannot insert data.');
//            }
//            $contentMapData['content_id'] = $this->getDbTable()->getAdapter()->lastInsertId();
//            $contentMapData['parent_id'] = $cid;
//            // Set the default level field in nad_content_map table to 1
//            $level = 1;
//            // New Parent ID in the nad_content_map is equivalent to the current Content ID
//            $pid = $cid;
//            $row = $contentMapMapper->find(17);
//            if ($row) {
//                $level = ((int) $row['level']) + 1;
//            }
//            $contentMapData['level'] = $level;
//        } else {
//            $isUpdated = $rowsAffected = $this->getDbTable()->update($contentData, array('content_id = ?' => $cid));
//            if (!$isUpdated) {
//                throw new Zend_Db_Exception('Cannot update data.');
//            }
//        }
//        /**
//         * Save the nad_content_map table
//         */
//        $contentMapMapper->save(new Content_Model_ContentMap($contentMapData));
//    }

    /**
     * Find a content entry by id
     * 
     * @param  int $id 
     * @param  Content_Model_Content $content 
     * @return void
     */
    public function find($id)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        return $row;
    }

    /**
     * Fetch all content entries
     * 
     * @return array
     */
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Content_Model_Content ();
            $entry->setContent_id($row->content_id)->setHeading($row->heading)->setKeyword($row->keyword)->setOne_line_desc($row->one_line_desc)->setTwo_line_desc($row->two_line_desc)->setThree_line_desc($row->three_line_desc)->setOne_para_desc($row->one_para_desc)->setDescription($row->description)->setShort_desc($row->short_desc)->setTitle_tag($row->title_tag)->setDesc_tag($row->desc_tag)->setIs_leaf($row->is_leaf);
            $entries [] = $entry;
        }
        return $entries;
    }

    public function ddlContentTypeList()
    {
        $resultSet = $this->getDbTable()->fetchAll($this->getDbTable()->select()->from('nad_content', array('key' => 'content_id', 'value' => 'heading')))->toArray();
        return $resultSet;
    }

    public function getDetailById($id = null)
    {
        if (empty($id)) {
            return;
        }
        $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false)
                ->joinLeft('nad_content_map', 'nad_content_map.content_id = nad_content.content_id', 'parent_id as content_type_id')
                ->where('nad_content.content_id = ' . $id);
        $resultSet = $this->getDbTable()->fetchAll($select)->toArray();
        return $resultSet[0];
    }

    public function getContentIdByElementCategoryId($id)
    {
        $resultSet = $this->getDbTable()->fetchRow(array("element_category_id ={$id}"))->toArray();
//        var_dump($resultSet);exit;
        return $resultSet;
    }

    public function fetchContentHierarchy($parent_id)
    {
        static $db, $output;

        if (!isset($output)) {
            $output = array();
        }
        if (!isset($db)) {
            $db = Zend_Db_Table::getDefaultAdapter();
        }

        $sql = "SELECT t.content_id, 
                       t.heading,
                       t.order_no,
                       t.content_id AS tid,
                       h.parent_id,
                       h.level,
                       h.parent_id AS parent                       
                FROM nad_content t
                INNER JOIN nad_content_map h
                ON t.content_id = h.content_id WHERE 1 AND t.is_leaf='N'";
        if ($parent_id) {
            $sql .= " AND h.parent_id={$parent_id}";
        }
        $sql .= ' ORDER BY t.heading, t.order_no';

        $content = $db->fetchAll($sql);
        if (!empty($content)) {
            foreach ($content as $data) {
                //$sp = html_entity_decode('&nbsp;', ENT_COMPAT, 'UTF-8');
                $sp = '--';
                $output[$data->content_id] = str_repeat($sp, ($data->level - 2) * 2) . ' ' . trim($data->heading);
                $this->fetchContentHierarchy($data->content_id);
            }
        }

        return $output;
    }

    public function fetchHierarchy($parent = NULL)
    {

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = new Zend_Db_Select($db);
        $sql = 'SELECT t.*, h.*, 
                       t.content_id AS tid,
                       h.parent_id AS parent                       
                FROM nad_content t
                INNER JOIN nad_content_map h
                ON t.content_id = h.content_id WHERE 1 ORDER BY t.heading';

//        if ($parent) {
//            $sql .= sprintf(' AND h.parent_id = %d', $parent);
//        }

        $resultSet = $db->fetchAll($sql);
//        Zend_Debug::dump($resultSet);
        return $resultSet;
    }

    public function getBreadCrumb($id)
    {
        static $upload = array();
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT m.parent_id, c.heading, m.content_id
                    FROM nad_content_map AS m
                    INNER JOIN nad_content AS c ON c.content_id = m.content_id
                    WHERE m.content_id ='$id'";

        $result = $db->fetchRow($sql);

        if (!$result) {
            throw new Zend_Db_Exception("Can't fetch such data");
        }

        if (0 != $result->parent_id) {
            array_push($upload, str_replace(' ', '', $result->heading));
            $path = $this->getBreadCrumb($result->parent_id);
        } else {
            array_push($upload, $result->heading);
            $upload = array_reverse($upload);
        }
        return $upload;
    }

    public function fetchGridData()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT 
                    nad_content.content_id,
                    nad_content.heading AS title,
                    nad_content.keyword,
                    nad_content.metatag,                    
                    nad_content.order_no,
                    nad_content.status,
                    nad_content_map.level
                FROM nad_content, nad_content_map
                WHERE 1
                AND nad_content.content_id = nad_content_map.content_id
                ORDER BY nad_content.content_id
               ";

        $resultSet = $db->fetchAll($sql);

        $output = array();
        $fc = Zend_Controller_Front::getInstance()->getRequest();
        foreach ($resultSet as $result) {
            //$result->title = sprintf("<a href='%s/admin/content/view/%s'>%s</a>", $fc->getBaseUrl(), $result->content_id, $result->title);
            $output[] = (array) $result;
        }
        return $output;
    }

    public function updateStatus($cid)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select if(status='D','E','D') as status from nad_content where content_id =" . $cid;
        $status = $db->fetchAll($sql);
        $contentData = array(
            'status' => $status[0]->status
        );

        $this->getDbTable()->update($contentData, array('content_id = ?' => $cid));
        return $status[0]->status;
    }

    public function updateApproveStatus($cid)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "select if(approved ='N','Y','N') as approved from nad_content where content_id =" . $cid;
        $status = $db->fetchAll($sql);
        $contentData = array(
            'approved' => $status[0]->approved
        );

        $this->getDbTable()->update($contentData, array('content_id = ?' => $cid));
        return $status[0]->approved;
    }

    public function fetchSearchGridData($query_string = '')
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT  DISTINCT
                    nad_content.content_id,
                    nad_content.heading,
                    nad_content.keyword,
                    nad_content.title_tag,
                    nad_content.desc_tag,
                    nad_content.description,
                    Date_Format(nad_content.entered_dt,'%Y-%m-%d') AS date  ,
                    nad_user.username  AS Entered_By                                  
                FROM nad_content
                Left Join nad_user ON nad_user.user_id = nad_content.entered_by 
                WHERE heading like '%$query_string%' OR keyword like '%$query_string%' OR desc_tag like '%$query_string%'OR title_tag like '%$query_string%'
               ";
        $resultSet = $db->fetchAll($sql);
        $output = array();
        $fc = Zend_Controller_Front::getInstance()->getRequest();
        foreach ($resultSet as $result) {
            $desc = array('content_length' => str_word_count(strip_tags($result->description)));
            $output[] = array_merge((array) $result, $desc);
        }
        return $output;
    }

    public function getContentDetails($content_id, $element_id = null)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT nad_content.content_image_link,nad_content.file_path,nad_content.file_name,nad_content.heading,nad_content.content_id ,nad_content.description \n"
                . "FROM nad_content \n"
                . "LEFT JOIN nad_content_tag_map ON nad_content.content_id = nad_content_tag_map.content_id \n"
//                    . "LEFT JOIN nad_element_category ON nad_content_tag_map.element_category_id = nad_element_category.element_category_id \n"
                . "WHERE nad_content.content_id ='{$content_id}' \n"
                . "AND nad_content.approved ='Y' \n"
                . "AND nad_content.status ='E' \n";
//                    . "AND nad_element_category.element_id='{$element_id}'";
        $tabQuery = "select content_id as tab_id,heading as tab_heading ,description as tab_description
                    from nad_content where nad_content.is_leaf='Y'
                    and content_id in(select content_id from nad_content_map where parent_id=" . $content_id . " ) AND nad_content.approved ='Y' AND nad_content.status ='E' ORDER BY nad_content.order_no DESC";

        $query = "SELECT 
                    nad_content_file.content_file_type_id,
                    nad_content_file.embed_code,
                    nad_content_file.file_name,
                    nad_content_file.file_path,
                    nad_content_file.caption,
                    nad_content_file.description
                    FROM nad_content
                    LEFT JOIN nad_content_file 
                    ON nad_content.content_id = nad_content_file.content_id
                    WHERE nad_content.content_id =" . $content_id . " 
                        AND nad_content.approved ='Y' 
                        AND nad_content.status ='E' 
                        ORDER BY nad_content.order_no DESC";

        $sqlResults = $this->getDbTable()->getAdapter()->fetchAll($sql);
        $queryResults = $this->getDbTable()->getAdapter()->fetchAll($query);
        $tabQueryResults = $this->getDbTable()->getAdapter()->fetchAll($tabQuery);

        $sqloutput = array();
        foreach ($sqlResults as $result) {
            $sqloutput[] = (array) $result;
        }

        $queryoutput = array();
        foreach ($queryResults as $res) {
            if ($res->content_file_type_id && $res->content_file_type_id == '1') {
                $queryoutput['images'][] = (array) $res;
            } else {
                $queryoutput['videos'][] = (array) $res;
            }
        }

        $tabQueryOutput = array();
        foreach ($tabQueryResults as $results) {
            $tabQueryOutput['tabs'][] = (array) $results;
        }
        if (count($sqloutput) > 0) {
            $output = array_merge($sqloutput[0], $queryoutput, $tabQueryOutput);
        } else {
            $output = array();
        }
        return $output;
    }

    public function getRootParentNode()
    {
        $sql = "SELECT c.content_id, \n"
                . "c.element_id, \n"
                . "IFNULL(e.name, 'Other') element_name, \n"
                . "c.heading \n"
                . "FROM nad_content c \n"
                . "INNER JOIN nad_content_map cm ON c.content_id = cm.content_id \n"
                . "LEFT JOIN nad_element e ON c.element_id = e.element_id \n"
                . "WHERE 1 \n"
                . "AND cm.parent_id = 0 \n"
                . "AND c.approved = 'Y' \n"
                . "AND c.status = 'E' \n"
                . "AND c.is_leaf = 'N' \n"
                . "ORDER BY c.heading";

        $resultSet = $this->getDbTable()->getDefaultAdapter()->fetchAll($sql);
        return $resultSet;
    }

    public function getSearchIndexData()
    {
        //return $resultSet = $this->getDbTable()->fetchAll();        
        $sql = "SELECT c.content_id, \n"
//                . "c.content_id AS tid, \n"
                . "c.element_id, \n"
                . "IFNULL(e.name, 'Other') element_name, \n"
                . "c.heading, \n"
                . "c.location, \n"
                . "c.keyword, \n"
                . "c.title_tag, \n"
                . "c.desc_tag, \n"
                . "c.one_line_desc, \n"
                . "c.two_line_desc, \n"
                . "c.three_line_desc, \n"
                . "c.one_para_desc, \n"
                . "c.short_desc, \n"
                . "c.description, \n"
                . "c.location, \n"
                . "c.content_image_link, \n"
                . "c.file_name, \n"
                . "c.order_no \n"
//                . "h.parent_id, \n"
//                . "h.parent_id AS parent \n"
                . "FROM nad_content c \n"
                . "INNER JOIN nad_content_map h \n"
                . "ON c.content_id = h.content_id \n"
                . "LEFT JOIN nad_element e \n"
                . "ON c.element_id = e.element_id \n"
                . "WHERE 1 \n"
                . "AND c.approved = 'Y' \n"
                . "AND c.status = 'E' \n"
                . "AND c.is_leaf = 'N' \n"
                . "ORDER BY element_name, c.heading";

        $resultSet = $this->getDbTable()->getDefaultAdapter()->fetchAll($sql);
        return $resultSet;
    }

    public function getPlacesContent()
    {
//        $sql = "
//                SELECT  c.content_id,  c.`heading`
//                FROM `nad_content` as c
//                JOIN nad_content_map as cmp
//                ON cmp.content_id = c.`content_id`
//                WHERE c.`element_id` = 9 and c.`is_leaf`='n'
//                ORDER BY cmp.level DESC
//                LIMIT 10
//               ";
        $sql = "
                SELECT c.content_id, c.`heading` AS 'short_name' , c.`element_category_id`
                FROM `nad_content` AS c
                WHERE c.`element_id` = 9
                AND c.`is_leaf` = 'n'
                AND c.element_category_id <> 0
                LIMIT 10 
               ";

        $resultSet = $this->getDbTable()->getDefaultAdapter()->fetchAll($sql);
        return $resultSet;
    }

    public function getActivitiesContent()
    {
        $sql = " SELECT  content_id,  heading AS 'name', element_category_id
                 FROM nad_content 
                 WHERE element_id = 3 
                 AND is_leaf = 'n'
                 AND element_category_id <> 0
                 LIMIT 10  
                ";

        $resultSet = $this->getDbTable()->getDefaultAdapter()->fetchAll($sql);
        return $resultSet;
    }

    public function getContentName($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
                ->from('nad_content', array('heading'))
                ->where('content_id = ' . $id);
        $results = $this->getDbTable()->getAdapter()->fetchAll($select);
        if (!empty($results)) {
            return trim($results[0]->heading);
        }
    }

    public function checkName($heading)
    {
        $heading = strtolower($heading);
        $doesExists = '';
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT heading FROM nad_content WHERE heading ='$heading'";
        if ($db->fetchRow($sql)) {
            $doesExists = true;
        } else {
            $doesExists = false;
        }
        return $doesExists;
    }

    public function getGuideDetails($content_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT nad_content.content_image_link,nad_content.file_path,nad_content.file_name,nad_content.heading,nad_content.content_id ,nad_content.description \n"
                . "FROM nad_content \n"
                . "LEFT JOIN nad_content_tag_map ON nad_content.content_id = nad_content_tag_map.content_id \n"
                . "WHERE nad_content.content_id ='{$content_id}' \n"
                . "AND nad_content.approved ='Y' \n"
                . "AND nad_content.status ='E' \n";
        $tabQuery = "select content_id as tab_id,heading as tab_heading ,description as tab_description
                    from nad_content where nad_content.is_leaf='Y'
                    and content_id in(select content_id from nad_content_map where parent_id=" . $content_id . " ) AND nad_content.approved ='Y' AND nad_content.status ='E' ";

        $sqlResults = $this->getDbTable()->getAdapter()->fetchAll($sql);
        $tabQueryResults = $this->getDbTable()->getAdapter()->fetchAll($tabQuery);

        $sqloutput = array();
        foreach ($sqlResults as $result) {
            $sqloutput[] = (array) $result;
        }

        $tabQueryOutput = array();
        foreach ($tabQueryResults as $results) {
            $tabQueryOutput['tabs'][] = (array) $results;
        }
        if (count($sqloutput) > 0) {
            $output = array_merge($sqloutput[0], $tabQueryOutput);
        } else {
            $output = array();
        }
        return $output;
    }

    public function fetchHierarchyByParentId($parent_id)
    {
        if(empty($parent_id)){
            return;
        }
        
        $allResult = $this->getAllChildrens(array($parent_id));
        $thisref = array();
        $refs = array();
        $list = array();
        foreach ($allResult as $data) {
            $data = (array) $data;
            $thisref = &$refs[$data['content_id']];
            $thisref["content_id"] = $data['content_id'];
            $thisref['parent_id'] = $data['parent_id'];
            $thisref['heading'] = $data['heading'];
            $fc = Zend_Controller_Front::getInstance();
            $baseUrl = $fc->getBaseUrl();
            $thisref['uri'] = $baseUrl . $data['heading'];
            if ($data['parent_id'] == $parent_id) {
                $list[$data['content_id']] = &$thisref;
            } else {
                $refs[$data['parent_id']]['children'][$data['content_id']] = &$thisref;
            }
        }
        return $list;
    }

    public function getAllChildrens($contentIds)
    {
        static $childrens = array();
        $order = '';
        if ($contentIds[0] == 274) {
            $order = " ORDER BY A.order_no DESC";
        }
        $contentIds = implode(",", $contentIds);
        $sql = "SELECT A.heading,A.order_no,B.* from nad_content as A join nad_content_map as B ON A.content_id = B.content_id where B.parent_id in ($contentIds) $order";
        $db = Zend_Db_Table::getDefaultAdapter();
        $results = $db->fetchAll($sql);
        $newChildren = array();
        foreach ($results as $result) {
            array_push($newChildren, $result->content_id);
            $childrens[$result->content_id] = $result;
        }
        if ($newChildren) {
            $childrens = $this->getAllChildrens($newChildren);
        }
        return $childrens;
    }

    public function getFooterContent()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $limit = 'LIMIT 10';
        $placeSql = "SELECT A.heading,A.content_id as id
                FROM nad_content as A                
                join nad_element as B ON A.element_id = B.element_id
                where B.element_id = 9 AND A.approved ='Y' 
                AND A.status ='E' AND A.is_leaf = 'N' order by A.order_no DESC {$limit}";

        $activitySql = "SELECT A.heading,A.content_id as id
                FROM nad_content as A                
                join nad_element as B ON A.element_id = B.element_id
                where B.element_id = 3 AND A.approved ='Y' 
                AND A.status ='E' AND A.is_leaf = 'N' order by A.order_no DESC {$limit}";

        $packageSql = "SELECT title as heading,package_id as id
          FROM nad_package_mst
               WHERE approved ='Y' 
                AND status ='E' 
          order by overall_rank DESC {$limit}";

        $placeResults = $this->getDbTable()->getAdapter()->fetchAll($placeSql);
        $activityResults = $this->getDbTable()->getAdapter()->fetchAll($activitySql);
        $packageResults = $this->getDbTable()->getAdapter()->fetchAll($packageSql);
        $results = array_merge(array('Top Places' => $placeResults), array('Activities' => $activityResults), array('Holidays' => $packageResults));
        return $results;
    }

    /**
     * Fetches all the parent content ids less than or equal to given level
     * @staticvar array $pcid
     * @param type $cid
     * @param type $level
     * @return type 
     */
    public function findParentsByContentId($cid, $level, $includeLevel = false)
    {
        if (empty($cid)) {
            return;
        }
        static $pcid = array();
        $sql = "SELECT content_id cid, parent_id pid, level FROM nad_content_map WHERE content_id={$cid} AND level>{$level}";
        if ($includeLevel) {
            $sql = "SELECT content_id cid, parent_id pid, level FROM nad_content_map WHERE content_id={$cid} AND level>={$level}";
        }
        $db = Zend_Db_Table::getDefaultAdapter();
        $rs = $db->fetchRow($sql);
        if ($rs) {
            $pcid[] = $rs->pid;
            $this->findParentsByContentId($rs->pid, $level);
        }
        return $pcid;
    }

    public function fetchContentBreadCrumb($cids = array())
    {
        $output = array('pcid' => array(), 'breadcrumb' => '');
        if (!$cids) {
            return $output;
        }
        $cid = implode(',', $cids);
        $sql = "SELECT c.content_id, c.heading, cm.parent_id \n"
                . "FROM nad_content c \n"
                . "INNER JOIN nad_content_map cm ON c.content_id=cm.content_id \n"
                . "WHERE c.content_id IN ({$cid})";
        $db = Zend_Db_Table::getDefaultAdapter();
        $rs = $db->fetchAll($sql);
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $breadcrumb = '';
        if ($rs) {
            $rijndael = new NepalAdvisor_Rijndael_Decrypt();
            $tp = count($rs);
            foreach ($rs as $key => $content) {
                $heading = str_replace('?', '', $content->heading);
                $cipher = $rijndael->encrypt($content->content_id);
                $breadcrumb .= sprintf("<span><a class=\"bc-guide-ln\" href=\"%s/guide/%s/%s.html\">%s</a></span>", $viewRenderer->view->siteUrl(), $heading, $cipher, $content->heading);
                if ($key < ($tp - 1)) {
                    $breadcrumb .= '<span class="bc-sept">&nbsp;&raquo;&nbsp;</span>';
                }
            }
        }
        $output = array(
            'pcid' => $cids,
            'breadcrumb' => $breadcrumb
        );
        return $output;
    }

}
