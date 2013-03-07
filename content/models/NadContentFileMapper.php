<?php

class Content_Model_NadContentFileMapper
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
            $this->setDbTable('Content_Model_DbTable_NadContentFile');
        }
        return $this->_dbTable;
    }

    /**
     *
     * @param Content_Model_Content $content
     * @return type 
     */
    /*  public function insert(Content_Model_Content $content)
      {
      $contentData = array(
      'content_id' => $content->getContent_id(),
      'language_id' => '1',
      'heading' => $content->getHeading(),
      'keyword' => $content->getKeyword(),
      'one_line_desc' => $content->getOne_line_desc(),
      'two_line_desc' => $content->getTwo_line_desc(),
      'three_line_desc' => $content->getThree_line_desc(),
      'one_para_desc' => $content->getOne_para_desc(),
      'description' => $content->getDescription(),
      'short_desc' => $content->getShort_desc(),
      'metatag' => $content->getMetatag(),
      'entered_dt' => date('Y-m-d H:i:s'));
      // Unset the primary keys
      unset($contentData['content_id']);
      $isInserted = $pkId = $this->getDbTable()->insert($contentData);
      if (!$isInserted) {
      throw new Zend_Db_Exception('Cannot insert data.');
      }

      return $pkId; //$this->getDbTable()->getAdapter()->lastInsertId();
      }

      public function update(Content_Model_Content $content)
      {
      $contentData = array(
      'content_id' => $content->getContent_id(),
      'language_id' => '1',
      'heading' => $content->getHeading(),
      'keyword' => $content->getKeyword(),
      'one_line_desc' => $content->getOne_line_desc(),
      'two_line_desc' => $content->getTwo_line_desc(),
      'three_line_desc' => $content->getThree_line_desc(),
      'one_para_desc' => $content->getOne_para_desc(),
      'description' => $content->getDescription(),
      'short_desc' => $content->getShort_desc(),
      'metatag' => $content->getMetatag(),
      'entered_dt' => date('Y-m-d H:i:s'));

      $cid = $content->getContent_id();
      $isUpdated = $rowsAffected = $this->getDbTable()->update($contentData, array('content_id = ?' => $cid));
      //        if (!$isUpdated) {
      //            throw new Zend_Db_Exception('Cannot update data.');
      //        }

      return $cid;
      }

      public function delete(Content_Model_Content $content)
      {
      $cid = (int) $content->getContent_id();
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
    public function getAdditionalData($url)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = $db->select()
                ->from(array('nad_content_file'), array('caption', 'description')
                )
                ->where('file_path="' . $url . '"');
        $select = $db->query($sql);
        $result = $select->fetchAll();
        //print_r($result);exit;
        return $result;
        //print_r($url);exit;
        // $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        //$profiler = $db->getProfiler()->setEnabled(true);
        //$resultSet = $this->getDbTable()->fetchAll();
        //$resultSet = $this->getDbTable()->fetchAll()->where('file_path="'. $url .'"');
        //print_r($profiler->getLastQueryProfile()->getQuery()) ;exit;
        /* $entries = array();
          foreach ($resultSet as $row) {
          $entry = new Content_Model_NadContentFile();
          $entry->setCaption($row->caption);
          $entry->setDescription($row->description);
          $entries [] = $entry;
          }
          print_r($entries[0]);exit;
          return $entries[0]; */
    }

    /*  public function fetchAll()
      {
      $resultSet = $this->getDbTable()->fetchAll();
      $entries = array();
      foreach ($resultSet as $row) {
      $entry = new Content_Model_Content ();
      $entry->setContent_id($row->content_id)->setHeading($row->heading)->setKeyword($row->keyword)->setOne_line_desc($row->one_line_desc)->setTwo_line_desc($row->two_line_desc)->setThree_line_desc($row->three_line_desc)->setOne_para_desc($row->one_para_desc)->setDescription($row->description)->setShort_desc($row->short_desc)->setMetatag($row->metatag);
      $entries [] = $entry;
      }
      return $entries;
      }

      public function ddlContentTypeList()
      {
      $resultSet = $this->getDbTable()->fetchAll($this->getDbTable()->select()->from('nad_content', array('key' => 'content_id', 'value' => 'heading')))->toArray();
      return $resultSet;
      }

      public function getDetailById($id)
      {
      $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
      ->setIntegrityCheck(false)
      ->joinLeft('nad_content_map', 'nad_content_map.content_id = nad_content.content_id', 'parent_id as content_type_id')
      ->where('nad_content.content_id = ' . $id);
      $resultSet = $this->getDbTable()->fetchAll($select)->toArray();
      return $resultSet[0];
      }

      public function fetchHierarchy($parent = NULL)
      {

      $db = Zend_Db_Table::getDefaultAdapter();
      $select = new Zend_Db_Select($db);
      $sql = 'SELECT t.content_id AS tid,
      h.parent_id AS parent,
      t.*
      FROM nad_content t
      INNER JOIN nad_content_map h
      ON t.content_id = h.content_id WHERE 1';
      //        if ($parent) {
      //            $sql .= sprintf(' AND h.parent_id = %d', $parent);
      //        }

      $resultSet = $db->fetchAll($sql);
      //        Zend_Debug::dump($resultSet);
      return $resultSet;
      }
     */

    public function changeCopyRight($id, $status)
    {
        $data = array(
            'copyright' => $status
        );
        $this->getDbTable()->update($data, 'content_file_id = ' . $id);
        return $status;
    }
    
    /**
     * Fetch the data for embed code according to content id;
     * @param type $cid 
     */
    public function getContentVideo($cid) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT * FROM nad_content_file WHERE content_id={$cid} AND content_file_type_id=2";
        $media = $db->fetchAll($sql);
        
        return $media;
    }

}
