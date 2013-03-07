<?php

class Company_Model_Feature
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
            $this->setDbTable('Company_Model_DbTable_Feature');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $allData = $this->getDbTable()->fetchAll();
        return $allData;
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
        $this->getDbTable()->update($data, 'company_feature_id = ' . $id);
    }

    public function setValues($formData)
    {
        $data = array(
            'name' => $formData['name'],
            'short_name' => $formData['short_name'],
            'element_id' => $formData['element_id'],
            'feature_type' => $formData['feature_type'],
            'defined_id' => $formData['defined_id'],
        );
        return $data;
    }

    public function delete($id)
    {
        $this->getDbTable()->delete('company_feature_id = ' . $id);
    }

    public function getDetailById($company_feature_id)
    {
        $sql = "SELECT f. * , e.name AS company_type,e.defined_id as element_defined_id, fm.upper_feature_id\n" //,upper.name as parent_feature, upper.defined_id as upper_defined_id\n"
                . "FROM nad_company_feature AS f\n"
                . "INNER JOIN nad_company_feature_map AS fm ON fm.company_feature_id = f.company_feature_id\n"
                . "INNER JOIN nad_element AS e ON e.element_id = f.element_id\n"
                . "WHERE f.company_feature_id ='$company_feature_id' ";
        $feature = $this->getDbTable()->getAdapter()->fetchRow($sql);
        return $feature;
    }

    public function changeStatus($company_feature_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT if(STATUS = 'D', 'E', 'D' ) as status FROM nad_company_feature WHERE company_feature_id ='$company_feature_id'";
        $row = $db->fetchRow($sql);
        $data = array(
            'status' => $row->status,
            'checked_by' => Zend_Auth::getInstance()->getIdentity()->username,
            'checked' => 'Y',
            'checked_dt' => date('Y-m-d'),
        );
        $this->getDbTable()->update($data, 'company_feature_id = ' . $company_feature_id);
        return $row->status;
    }

    public function fetchHierarchy($element_id=null)
    {
        $allResult = $this->expandTree(null, $element_id);
        return $allResult;
    }

    public function expandTree($companyFeature=null, $element_id)
    {
        static $str;
        if ($companyFeature) {
            $companyFeature['name'] = str_repeat("--", $companyFeature['level'] - 1) . $companyFeature['name'];
            $str[] = $companyFeature;
            $companyFeatures = $this->getChildren($companyFeature['company_feature_id'], $element_id);
        } else {
            $companyFeatures = $this->getChildren(0, $element_id);
        }
        foreach ($companyFeatures as $companyFeature) {
            $this->expandTree($companyFeature, $element_id);
        }
        return $str;
    }

    public function getChildren($company_feature_id, $element_id)
    {
        $where = '';
        if ($element_id) {
            $where = " AND f.element_id='$element_id'";
        }
        $sql = "SELECT f.company_feature_id, f.name,f.short_name,f.defined_id,f.feature_type,f.status,e.name as element_type, fm.level\n"
                . "FROM nad_company_feature AS f\n"
                . "INNER JOIN nad_element as e on e.element_id=f.element_id\n"
                . "INNER JOIN nad_company_feature_map as fm on fm.company_feature_id=f.company_feature_id where fm.upper_feature_id='$company_feature_id'" . $where;
        $results = array();
        $db = Zend_Db_Table::getDefaultAdapter();
        $results = $db->fetchAll($sql);
        $all = array();
        foreach ($results as $result) {
            $all[] = (array) $result;
        }
        return $all;
    }

    public function getFeatures($element_id)
    {
        $allData = $this->getDbTable()->fetchAll('element_id=' . $element_id);
        return $allData->toArray();
    }

    public function fetchFeatureOption($element_id=null)
    {
        $features = $this->fetchHierarchy($element_id);
        $arr = array();
        array_push($arr, '--Select--');
        foreach ($features as $feature) {
            $arr[$feature['company_feature_id'] . "::" . $feature['defined_id']] = $feature['name'];
        }
        return $arr;
    }

}
?>

