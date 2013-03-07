<?php

class Company_Model_FeatureMap
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
            $this->setDbTable('Company_Model_DbTable_FeatureMap');
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
        if (0 == $formData['upper_feature_id']) {
            $formData['level'] = 1;
        } else {
            $formData['level'] = $this->getLevel($formData['upper_feature_id']);
        }
        $data = $formData;
        $last_id = $this->getDbTable()->insert($data);
        if (!$last_id) {
            throw new Zend_Db_Exception('Cannot insert data.');
        }
        return $last_id;
    }

    public function getLevel($parent_id)
    {
        $featureMap = $this->getDbTable()->fetchRow('company_feature_id = ' . $parent_id);
        $level = $featureMap->level + 1;
        return $level;
    }

    public function getDetailById($id)
    {
        $row = $this->getDbTable()->fetchRow('company_feature_id = ' . $id);
        return $row;
    }

    public function update($formData, $id)
    {
        if (0 == $formData['upper_feature_id']) {
            $formData['level'] = 1;
        } else {
            $formData['level'] = $this->getLevel($formData['upper_feature_id']);
        }
        $data = $formData;
        $this->getDbTable()->update($data, 'company_feature_id = ' . $id);
    }

    public function delete($id)
    {
        $this->getDbTable()->delete('company_feature_id = ' . $id);
    }

    public function listAll()
    {
        $sql = "select f.company_feature_id, f.name, f.short_name,f.feature_type,f.status from nad_company_feature as f inner join nad_element as e on e.element_id=f.element_id";

        $features = $this->getDbTable()->getAdapter()->fetchAll($sql);
        return $features;
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

    public function getParents($feature_id)
    {
        static $parents = array();
        array_push($parents, $feature_id);
        $data = $this->getDbTable()->fetchRow('company_feature_id=' . $feature_id);
        if (!$data->upper_feature_id == 0) {
            $parents = $this->getParents($data->upper_feature_id);
        }
        return $parents;
    }

//    public function fetchHierarchy()
//    {
//        mysql_pconnect("localhost", "root", "itravel");
//        mysql_select_db('nad');
//        $allResult = $this->expandTree();
//        return $allResult;
//    }
//
//    public function expandTree($companyFeature=null)
//    {
//        static $str;
//        if ($companyFeature) {
//            //$str[$companyFeature['company_feature_id']] = str_repeat('--', $companyFeature['level'] - 1) . $companyFeature['name'];
//            $companyFeature['name'] = str_repeat('--', $companyFeature['level'] - 1) . $companyFeature['name'];
//            $str[] = $companyFeature;
//            $companyFeatures = $this->getChildren($companyFeature['company_feature_id']);
//        } else {
//            $companyFeatures = $this->getChildren(0);
//        }
//        foreach ($companyFeatures as $companyFeature) {
//            $this->expandTree($companyFeature);
//        }
//        return $str;
//    }
//
//    public function getChildren($company_feature_id)
//    {
//        $sql = "SELECT f.company_feature_id, f.name,f.short_name,f.feature_type,f.status,e.name as element_type, fm.level\n"
//                . "FROM nad_company_feature AS f\n"
//                . "INNER JOIN nad_element as e on e.element_id=f.element_id\n"
//                . "INNER JOIN nad_company_feature_map as fm on fm.company_feature_id=f.company_feature_id where fm.upper_feature_id='$company_feature_id'";
//        $results = array();
//        $query = mysql_query($sql);
//        while ($all = mysql_fetch_array($query)) {
//            $results[] = $all;
//        }
//        return $results;
//    }
}
?>

