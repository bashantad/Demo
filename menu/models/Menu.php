<?php

class Menu_Model_Menu
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
            $this->setDbTable('Menu_Model_DbTable_Menu');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $menu = $this->getDbTable()->fetchAll();
        return $menu;
    }

    public function fetchHierarchy($parent = NULL)
    {

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = new Zend_Db_Select($db);

        $sql = 'SELECT  m.*, mm.*,
                        m.menu_id as tid, 
                        mm.parent_id as parent, 
                        m.label as heading
                FROM nad_menu AS m
                INNER JOIN nad_menu_map mm ON m.menu_id = mm.menu_id WHERE 1';
        if ($parent) {
            $sql .= sprintf(' AND mm.parent_id = %d', $parent);
        }

        $resultSet = $db->fetchAll($sql);
        return $resultSet;
    }

    public function addMenu($formData)
    {
        $data = array(
            'language_id' => '1',
            'short_name' => $formData['short_name'],
            'label' => $formData['label'],
            'link_url' => $formData['link_url'],
            'link_alias' => $formData['link_alias'],
            'entered_by' => Zend_Auth::getInstance()->getIdentity()->username,
            'status' => 'E',
            'entered_dt' => date('Y-m-d'),
        );
        $last_id = $this->getDbTable()->insert($data);
        if (!$last_id) {
            throw new Zend_Db_Exception('Cannot insert data.');
        }
        return $last_id;
    }

    public function getDetailById($id)
    {
        $menu = $this->getDbTable()->fetchRow('menu_id = ' . $id);
        return $menu->toArray();
    }

    public function updateMenu($formData, $id)
    {
        $data = Array(
            'language_id' => '1',
            'short_name' => $formData['short_name'],
            'label' => $formData['label'],
            'link_url' => $formData['link_url'],
            'link_alias' => $formData['link_alias'],
            'entered_by' => Zend_Auth::getInstance()->getIdentity()->username,
        );
        $this->getDbTable()->update($data, 'menu_id = ' . $id);
    }

    public function DeleteMenu($childrens)
    {
        foreach ($childrens as $children):
            $this->getDbTable()->delete('menu_id = ' . $children);
        endforeach;
    }

    public function getMenuNavigation($parent = NULL)
    {
        $sql = 'SELECT  m.menu_id as tid, 
                        m.label, m.link_url, m.link_alias, 
                        mm.level, mm.parent_id as parent, 
                        m.label as heading
                FROM nad_menu AS m
                INNER JOIN nad_menu_map mm ON m.menu_id = mm.menu_id WHERE 1';

        $resultSet = $this->getDbTable()->getAdapter()->fetchAll($sql);

        return $resultSet;
    }

    public function getMenuNavigationArray($vid=1, $tid=0)
    {
        $items = array();
        $options = array(
            'mapper' => $this,
            'label' => 'label',
            'method' => 'getMenuNavigation'
        );
        static $tree;
        if (!isset($tree)) {
            $tree = new NepalAdvisor_Tree_Controller_Plugin_Tree($options);
        }
        $terms = $tree->getHierarchyTerms($vid, $tid, -1, 1);
        
        foreach ($terms as $i => $term) {
            $term_items = $this->getMenuNavigationArray($vid, $term->tid);
            $mca = explode('/', $term->link_url);
            $term->module = $mca[0];
            $term->controller = $mca[1];
            $term->action = $mca[2];
            $term->route = $term->link_alias;
            $term->pages = $term_items;
            
            unset($term->tid);
            unset($term->level);
            unset($term->heading);
            unset($term->link_url);
            unset($term->link_alias);
            unset($term->depth);
            unset($term->children);
            unset($term->parents);
            $items[$term->label] = (array) $term;
        }

        return $items;
    }
    
     public function fetchHierarchyByParentId($parentId=0)
    {
        $allResult = $this->expandTree(null, $parentId);
        return $allResult;
    }

    public function expandTree($currentData=null, $parentId)
    {
        static $str;
        if ($currentData) {
            $currentData['label'] = str_repeat("--", $currentData['level'] - 5) . $currentData['label'];
            $str[] = $currentData;
            $currentDatas = $this->getChildren($currentData['menu_id']);
        } else {
            $currentDatas = $this->getChildren($parentId);
        }
        foreach ($currentDatas as $currentData) {
            $this->expandTree($currentData, $parentId);
        }
        return $str;
    }

    public function getChildren($parentId)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = 'SELECT  m.*, mm.*,
                        m.menu_id, 
                        mm.parent_id as parentId, 
                        m.label as heading
                FROM nad_menu AS m
                INNER JOIN nad_menu_map mm ON m.menu_id = mm.menu_id WHERE 1';
        if ($parentId) {
            $sql .= sprintf(' AND mm.parent_id = %d', $parentId);
        }
        $results = array();
        $results = $db->fetchAll($sql);
        $all = array();
        foreach ($results as $result) {
            $all[] = (array) $result;
        }
        return $all;
    }
    
}