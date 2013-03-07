<?php

class Default_Model_MetaModel extends Zend_Db_Table_Abstract
{

    public function getContentMetas()
    {
        $params = Zend_Controller_Front::getInstance()->getRequest()->getParam('id');
        $controllerName = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $rijndael = new NepalAdvisor_Rijndael_Encrypt();
        $ids = explode('-', $rijndael->decrypt($params));
        $id = $ids[0];
        if (empty($id)) {
            return;
        }
        $controllers = array("hotels", "travel", "activities", "places");
        if ($controllerName == "holidays") {
            $sql = "SELECT keyword_tag as keywords,title_tag as title,desc_tag as description From nad_package_mst where package_id = {$id}";
        } else if (in_array($controllerName, $controllers)) {
            $sql = "SELECT keyword as keywords,title_tag as title,desc_tag as description From nad_content where content_id = {$id}";
        }else if ($controllerName == "inspiration") {
            $sql = "SELECT keyword as keywords,title,desc_tag as description From nad_inspire where inspire_id = {$id}";
        }
        else{
            return;
        }

        $results = $this->getAdapter()->fetchAll($sql);
        $output = array();
        foreach ($results as $res) {
            $output = (array) $res;
        }
        return $output;
    }

}

?>
