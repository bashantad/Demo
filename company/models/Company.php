<?php

class Company_Model_Company
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
            $this->setDbTable('Company_Model_DbTable_Company');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $elements = $this->getDbTable()->fetchAll();
        return $elements;
    }

    public function add($formData)
    {
        $data = $this->setValues($formData);
        $data += array(
            'language_id' => '1',
            'entered_by' => Zend_Auth::getInstance()->getIdentity()->username,
            'status' => 'D',
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
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select();
        $select->from(array('c' => 'nad_company_mst'), array('*'))
        ->join(array('e' => 'nad_element'), 'c.element_id = e.element_id', array('defined_id as element_defined-id'))
        ->joinLeft(array('l' => 'nad_location_mst'), 'l.location_id = c.location_id', array('defined_id as location_defined-id'))
        ->where('c.company_id=' . $id);
        $row = $db->fetchRow($select);
        return (array) $row;
    }

    public function getDetailByContentId($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = "SELECT c.company_id
                   FROM nad_company_mst AS c
                   WHERE c.content_id = {$id}";
        $row = $db->fetchRow($select);
        return (array) $row;
    }

    public function update($formData, $id)
    {
        if (!is_array($formData)) {
            $data['defined_id'] = $id;
        } else {
            $data = $this->setValues($formData);
        }
        $data += array(
            'checked_by' => Zend_Auth::getInstance()->getIdentity()->username,
            'checked' => 'Y',
            'checked_dt' => date('Y-m-d'),
        );
        $this->getDbTable()->update($data, 'company_id = ' . $id);
    }

    public function setValues($formData)
    {
        $data = array(
            'name' => $formData['name'],
            'short_name' => $formData['short_name'],
            'element_id' => $formData['element_id'],
            'content_id' => (int)$formData['content_id'] ? $formData['content_id'] : null,
            'address'=>$formData['address'],
            'location_id' => $formData['location_id'],
        );
        return $data;
    }

    public function listAll()
    {
        $sql = "SELECT c.company_id, e.name AS company_type, c.name, c.short_name, c.address, l.name as location, c.status
                FROM nad_company_mst AS c
                INNER JOIN nad_element AS e ON e.element_id = c.element_id
                LEFT JOIN nad_location_mst as l on c.location_id=l.location_id";

        $companies = $this->getDbTable()->getAdapter()->fetchAll($sql);
        return $companies;
    }

    public function changeStatus($company_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT if(STATUS = 'D', 'E', 'D' ) as status FROM nad_company_mst WHERE company_id ='$company_id'";
        $row = $db->fetchRow($sql);
        $data = array(
            'status' => $row->status,
            'checked_by' => Zend_Auth::getInstance()->getIdentity()->username,
            'checked' => 'Y',
            'checked_dt' => date('Y-m-d'),
        );
        $this->getDbTable()->update($data, 'company_id = ' . $company_id);
        return $row->status;
    }

    public function getCompanies()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
        ->from('nad_company_mst', array('company_id', 'name', 'defined_id'))
        ->order('name');
        $allData = $this->getDbTable()->getAdapter()->fetchAll($select);
        $elements = array();
        array_push($elements, '--Select--');
        foreach ($allData as $data) {
            $elements[$data->company_id."::".$data->defined_id]=$data->name;
        }
        return $elements;
    }

    public function getCompaniesByElementId($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
        ->from('nad_company_mst', array('company_id', 'name', 'defined_id'))
        ->where('element_id = ?', $id);
        $allData = $this->getDbTable()->getAdapter()->fetchAll($select);
        $elements = array();
        foreach ($allData as $data) {
            $elements[$data->company_id."::".$data->defined_id]=$data->name;
        }
        return $elements;
    }
    public function delete($id)
    {
        $this->getDbTable()->delete("company_id=".$id);
    }
    public function getCompanyMappedContentIds()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT content_id from nad_company_mst WHERE content_id is not NULL";
        $results = $db->fetchAll($sql);
        $contentIds = array();
        foreach($results as $result){
            array_push($contentIds,$result->content_id);
        }
        return implode(",",$contentIds);
    }
}
?>

