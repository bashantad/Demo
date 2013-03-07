<?php

class Company_DetailController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('delete', 'json')
                ->addActionContext('status', 'json')
                ->initContext();
    }

    public function viewAction()
    {
        $company_id = $this->_getParam('id');
        $companyModel = new Company_Model_Company();
        $company = $companyModel->getDetailById($company_id);
        $element_id = $company['element_id'];
        $this->view->company_name = $company['name'];
        $featureModel = new Company_Model_Feature();
        $features = $featureModel->fetchHierarchy($element_id);
        $this->view->features = $features;
        $companyDetailModel = new Company_Model_Detail();
        $companyDetails = $companyDetailModel->fetchAll($company_id);
        $this->view->companyDetails = $companyDetails;
        $this->view->id = $company_id;
    }

    public function addAction()
    {
        $company_id = $this->_getParam('id');
        $companyModel = new Company_Model_Company();
        $company = $companyModel->getDetailById($company_id);
        $element_id = $company['element_id'];
        $this->view->company_name = $company['name'];
        $featureModel = new Company_Model_Feature();
        $features = $featureModel->fetchHierarchy($element_id);
        $this->view->features = $features;
        $companyDetailModel = new Company_Model_Detail();
        $companyDetails = $companyDetailModel->fetchAll($company_id);
        $featureMapModel = new Company_Model_FeatureMap();
        $this->view->companyDetails = $companyDetails;
        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            $companyDetailModel->delete($company_id);
            static $allFeatures = array();
            foreach ($formData['desc'] as $key => $value) {
                if ($value != '') {
                    $parents = $featureMapModel->getParents($key);
                    foreach ($parents as $parent) {
                        array_push($allFeatures, $parent);
                    }
                }
            }
            $allFeatures = array_unique($allFeatures);
            foreach ($allFeatures as $feature) {
                $data = array();
                $data['company_feature_id'] = $feature;
                $data['feature_desc'] = isset($formData['desc'][$feature]) ? $formData['desc'][$feature] : 'Yes';
                $data['company_id'] = $company_id;
                $companyDetailModel->add($data);
            }
            $url = '/company/detail/view/id/' . $company_id;
            $this->_redirect($url, array('prependBase' => false));
        }
    }

}

?>
