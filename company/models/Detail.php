<?php

class Company_Model_Detail
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
            $this->setDbTable('Company_Model_DbTable_Detail');
        }
        return $this->_dbTable;
    }

    public function fetchAll($company_id)
    {
        $allData = $this->getDbTable()->fetchAll('company_id=' . $company_id);
        return $allData;
    }

    public function add($formData)
    {
        $last_id = $this->getDbTable()->insert($formData);
        if (!$last_id) {
            throw new Zend_Db_Exception('Cannot insert data.');
        }
        return $last_id;
    }

//    public function doExists($data)
//    {
//        $row = $this->getDbTable()->fetchRow('company_feature_id=' . $data['company_feature_id'] . ' AND company_id=' . $data['company_id']);
//        if ($row) {
//            $this->update($data, $row['company_dtl_id']);
//            return true;
//        } else {
//            return false;
//        }
//    }
    public function delete($company_id)
    {
        $profiler = Zend_Db_Table::getDefaultAdapter()->getProfiler()->setEnabled(true);
        $this->getDbTable()->delete('company_id=' . $company_id);
    }

    public function getDetailById($id)
    {
        $element = $this->getDbTable()->fetchRow('company_dtl_id = ' . $id);
        return $element;
    }

    public function update($data, $id)
    {
        if ($data['feature_desc'] == '') {
            $this->getDbTable()->delete('company_dtl_id = ' . $id);
        } else {
            $this->getDbTable()->update($data, 'company_dtl_id = ' . $id);
        }
    }

    public function listAll()
    {
        $sql = "SELECT company_dtl_id, name, short_name,order_no, status FROM `nad_company_dtl`";

        $allData = $this->getDbTable()->getAdapter()->fetchAll($sql);
        return $allData;
    }

}
?>

