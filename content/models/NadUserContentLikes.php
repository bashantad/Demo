
<?php

class Content_Model_NadUserContentLikes
{

    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Content_Model_DbTable_NadUserContentLikes');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $elements = $this->getDbTable()->fetchAll();
        return $elements;
    }

    public function add($data)
    {
        try {
            $data['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $data['likes_date'] = date('Y-m-d');
            $last_id = $this->getDbTable()->insert($data);
            if (!$last_id) {
                throw new Zend_Db_Exception('Cannot insert data.');
            }
            return TRUE;
        } catch (Exception $sql) {
            if ($sql->getCode() == 23000) {
                return TRUE;
            } else {
                throw new Zend_Db_Statement_Mysqli_Exception("Mysqli prepare error: " . $sql->error);
            }
        }
    }

    public function delete($id, $uid)
    {
        $this->getDbTable()->delete(array('content_id = ?' => $id, 'user_id = ?' => $uid));
    }

    public function getDetailById($id)
    {
        $element = $this->getDbTable()->fetchRow('likes_id = ' . $id);
        return $element;
    }

    public function find($id)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        return $row;
    }

    public function getPreviousLikes($elementId)
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $user = $identity->user_id;
        $sql = "select B.* ,CONCAT('Your Selected Likes') as name
            from nad_user_content_likes as A
            join nad_content As B ON A.content_id = B.content_id            
            WHERE A.user_id = {$user} AND A.element_id = {$elementId}
            ";

        $results = $this->getDbTable()->getAdapter()->fetchAll($sql);
        $output = array();
        foreach ($results as $res) {
            $output[] = (array) $res;
        }
        return $output;
    }

}

?>
