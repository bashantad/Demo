<?php

class Menu_Model_MenuMap
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
            $this->setDbTable('Menu_Model_DbTable_MenuMap');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $menumap = $this->getDbTable()->fetchAll();
        return $menumap;
    }

    public function addMenuMap($mapData)
    {
        $menuId = $isMapinserted = $this->getDbTable()->insert($mapData);
        if (!$isMapinserted) {
            throw new Zend_Db_Exception("Can't insert data");
        }
        return $menuId;
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

    public function getDetailById($id)
    {
        //$profiler = Zend_Db_Table::getDefaultAdapter()->getProfiler()->setEnabled(true);
        $menu = $this->getDbTable()->fetchRow('menu_id = ' . $id);
        // $profiler->getLastQueryProfile()->getQuery();

        return $menu->toArray();
    }

    public function updateMenuMap($formData, $id)
    {
        $data = Array(
            'language_id' => '1',
            'short_name' => $formData['short_name'],
            'label' => $formData['label'],
            'link_url' => $formData['link_url'],
            'entered_by' => Zend_Auth::getInstance()->getIdentity()->username,
            'status' => 'E',
            'entered_dt' => date('Y-m-d'),
            'checked_by' => Zend_Auth::getInstance()->getIdentity()->username,
            'checked' => 'Y',
            'checked_dt' => date('Y-m-d'),
        );
        $this->getDbTable()->update($data, 'menu_id = ' . $id);
    }

    public function DeleteMenuMap($childrens)
    {
        foreach($childrens as $children):
            $this->getDbTable()->delete('menu_id = ' . $children);
        endforeach;
    }

    public function getChildrens($id)
    {
        static $allChildren = array();
        $sql = "select menu_id from nad_menu_map where parent_id=$id";
        $childrens = $userpermissions = $this->getDbTable()->getAdapter()->fetchAll($sql);
        if (null != $childrens) {
            foreach ($childrens as $children) {
                array_push($allChildren, $children->menu_id);
            }
            foreach ($childrens as $children):
                $this->getChildrens($children->menu_id);
            endforeach;
        }
        return $allChildren;
    }

}