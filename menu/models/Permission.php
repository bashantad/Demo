<?php

class Menu_Model_Permission
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
            $this->setDbTable('Menu_Model_DbTable_Permission');
        }
        return $this->_dbTable;
    }

    //public function fetchAll
    public function addMenuPermission($usertype_id, $childrens)
    {
        // $profiler = Zend_Db_Table::getDefaultAdapter()->getProfiler()->setEnabled(true);
        foreach ($childrens as $children):
            $data = array(
                'menu_id' => $children,
                'usertype_id' => $usertype_id,
            );
        if(!$this->doesExists($data)){
            $isInserted = $this->getDbTable()->insert($data);
        }
        endforeach;

        if ($isInserted) {
            return $hasPermission = 'true';
        } else {
            throw new Exception("can't insert data");
        }
        //  var_dump($profiler->getLastQueryProfile()->getQuery());
    }

    public function deleteMenuPermission($usertype_id, $childrens)
    {
        foreach ($childrens as $children):
            $hasPermission = $this->getDbTable()->delete('menu_id =' . $children . ' AND usertype_id=' . $usertype_id);
        endforeach;
        if ($hasPermission) {
            return $hasPermission = 'false';
        } else {
            throw new Exception("can't delete data");
        }
    }
    
    public function doesExists($data){
        $isExists = $this->getDbTable()->fetchRow('menu_id ='.$data['menu_id']. ' AND usertype_id= '. $data['usertype_id']);
        if(null!=$isExists){
            return true;
        }
        return false;
    }

    public function fetchAll($menu_id)
    {
        $sql = "SELECT ut.usertype_id, ut.name, m.menu_id, m.label, m.link_url
                FROM nad_userperm_sec AS up
                INNER JOIN nad_usertype AS ut ON ut.usertype_id = up.usertype_id
                LEFT JOIN nad_menu AS m ON m.menu_id = up.menu_id
                WHERE m.menu_id ='$menu_id'
                ORDER BY ut.usertype_id";
        $userpermissions = $this->getDbTable()->getAdapter()->fetchAll($sql);
        return $userpermissions;
    }
    public function deleteAllChildren($childrens){
        foreach ($childrens as $children):
            $this->getDbTable()->delete('menu_id =' . $children);
        endforeach;
    }

}

/*
 * Person A: Microsoft bought skype for 8.5 billion dollars...haha.. idiots they could have downloaded it for free..
 * Person B: that's what people like you says... and company like microsoft does..
 * 
 */
?>



