<?php

class Default_PackageController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->block->add('footerouterwrapper');
        $this->_helper->block->add('floatingnav');
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("add-detail", "json")
                    ->initContext();
        $this->_helper->block->add('loggedin');
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
    }

    public function indexAction()
    {
        try{
            $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
            $eventModel = new Package_Model_Event();
            $customizedPackage = $eventModel->fetchPackagesByUserId($user_id);
            $this->view->package = $customizedPackage;
    
            //top selling holidays
            $packageMstModel = new Package_Model_NadPackageMst();
            $datas = $packageMstModel->getMapper()->getTopPackageDetails(8);
            $this->view->relatedServices = $datas;
        }catch(Exception $e){
            $this->_helper->FlashMessenger->addMessage(array('alert' => $e->getMessage()));
        }
    }

    public function editAction()
    {
        try{
            $params = $this->view->rijndael->decrypt($this->_getParam('q'));
            $arr = array();
            $str = parse_str($params, $arr);
            $eventId = $arr['event_id'];
            $customPackage = '';
            $userId = Zend_Auth::getInstance()->getIdentity()->user_id;
            $eventModel = new Package_Model_Event();
            $customizedPackage = $eventModel->fetchPackagesByUserId($userId, $eventId);
            if ($customizedPackage) {
                $customPackage = $customizedPackage[0];
                $customPackage->start_dt = $this->_helper->dateFromMysql($customPackage->start_dt);
            }
            $eventDetailModel = new Package_Model_EventDetail();
            $eventDetails = $eventDetailModel->getAllByEventId($eventId);
            $this->view->package = $customPackage;
            $this->view->packageDetail = $eventDetails;
        }catch(Exception $e){
            $this->_helper->FlashMessenger->addMessage(array('alert' => $e->getMessage()));
        }
    }

    public function customizeAction()
    {
        
        $packageSession = Zend_Registry::get('packagesession');
        $this->view->formData = $packageSession->formData;
        $q = $this->_getParam("q");
        $params = $this->view->rijndael->decrypt($q);
        $arr = array();
        $str = parse_str($params, $arr);
        $pid = $arr['package_id'];
        if (!$pid) {
            throw new Exception('Unable to determine package');
        }
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $uriSession = Zend_Registry::get('request_uri');
            $uriSession->request_uri = $this->view->siteUrl()."/default/package/customize/q/".$q;
            $this->_helper->redirector('login', 'users');
        }
        $packageMstModel = new Package_Model_NadPackageMst();
        $package = $packageMstModel->find($pid)->toArray();
        // Get all the package details of the corresponding package
        $packageDtlModel = new Package_Model_NadPackageDtl();
        $packageDetailResultSet = $packageDtlModel->getMapper()->fetchPackageDtlByElementDtlIdNew($pid);
        $package['adult']='';
        $package['child']='';
        if($packageSession->formData){
            $data = $packageSession->formData;
            $package['total_cost'] = $data["total_price"];
            $package['child'] = $data["child"];
            $package['adult'] = $data["adult"];
            foreach($data['post'] as $key=>$value){
                $packageDetailResultSet[$key]->element_dtl_id = $value['element_dtl_id'];
                $packageDetailResultSet[$key]->name = $value['element_name'];
                $packageDetailResultSet[$key]->service_name = $value['service_name'];
                $packageDetailResultSet[$key]->service_class = $value['service_class'];
            }
        }
        $this->view->package = $package;
        $this->view->packageDetail = $packageDetailResultSet;
        $packageSession->formData = '';
    }

    public function customizeTransportAction()
    {
        try {
            $rijndael = new NepalAdvisor_Rijndael_Decrypt();
            $params = $rijndael->decrypt($this->getRequest()->getParam('q'));
            $arr = array();
            $str = parse_str($params, $arr);
            $this->view->element_dtl_id = $arr['element_dtl_id'];
            $elementDetailModel = new Package_Model_ElementDetail();
            $results = array();
            $results = $elementDetailModel->fetchElementDetailForTransportMeans($arr);
            $this->view->results = $results;
        }
        catch(Exception $e) {
            $this->view->message = $e->getMessage();
        }
    }

    public function detailAction()
    {
        $customPackage = '';
        $userId = Zend_Auth::getInstance()->getIdentity()->user_id;
        $params = $this->view->rijndael->decrypt($this->_getParam('q'));
        $arr = array();
        $str = parse_str($params, $arr);
        $eventId = $arr['event_id'];
        $eventModel = new Package_Model_Event();
        $customizedPackage = $eventModel->fetchPackagesByUserId($userId, $eventId);
        if ($customizedPackage) {
            $customPackage = $customizedPackage[0];
        }
        $eventDetailModel = new Package_Model_EventDetail();
        $eventDetails = $eventDetailModel->getAllByEventId($eventId);
        $this->view->package = $customPackage;
        $this->view->packageDetail = $eventDetails;
    }

    public function customizeHotelAction()
    {
        try {
            $rijndael = new NepalAdvisor_Rijndael_Decrypt();
            $params = $rijndael->decrypt($this->getRequest()->getParam('q'));
            $arr = array();
            $str = parse_str($params, $arr);
            $this->view->element_dtl_id = $arr['element_dtl_id'];
            $elementDetailModel = new Package_Model_ElementDetail();
            $results = $elementDetailModel->fetchElementDetailBySearchParams($arr);
            $this->view->results = $results;
        } catch (Exception $e) {
            $this->view->message = $e->getMessage();
        }
    }

    public function saveAction()
    {
        try {
            if ($this->getRequest()->isPost()) {
                $formData = $this->getRequest()->getPost();
                $currentUri = "/default/package/customize/?q=" . $this->view->rijndael->encrypt("package_id={$formData['package_id']}");
                if ($formData['start_dt'] == '') {
                    $this->_helper->FlashMessenger->addMessage(array('alert' => "Please enter your start date"));
                    $packageSession = Zend_Registry::get('packagesession');
                    $packageSession->formData = $formData;
                    $this->_redirect($currentUri);
                } 
            }else{
                $this->_helper->redirector("index","package","default");
            }
            $eventModel = new Package_Model_Event();
            $formData['start_dt'] = $this->_helper->dateToMysql($formData['start_dt']);
            $eventId = $eventModel->add($formData);
            $parameters = sprintf("event_id=%s&encryption=%s", $eventId, rand());
            $cipherQuery = $this->view->rijndael->encrypt($parameters);
            //$params = array("q"=>$cipherQuery);
            $detailUrl = "/default/package/detail/q/".$cipherQuery;
            $this->_redirect($detailUrl);
            //$this->_helper->redirector("detail","package","default",$params);
        } catch (Exception $e) {
            $this->_helper->FlashMessenger->addMessage(array('alert' => $e->getMessage()));
            $this->_redirect($currentUri);
        }
    }

    public function updateAction()
    {
        try{
            if ($this->getRequest()->isPost()) {
                $formData = $this->getRequest()->getPost();
                $parameters = sprintf("event_id=%s&encryption=%s", $formData['event_id'], rand());
                $cipherQuery = $this->view->rijndael->encrypt($parameters);
                $params = array("q"=>$cipherQuery);
                if ($formData['start_dt'] == '') {
                    $packageSession = Zend_Registry::get('packagesession');
                    $packageSession->formData = $formData;
                    $this->_helper->FlashMessenger->addMessage(array('alert' => "Please enter your start date"));
                    $this->_helper->redirector("edit","package","default",$params);
                } else {
                    $eventModel = new Package_Model_Event();
                    $formData['start_dt'] = $this->_helper->dateToMysql($formData['start_dt']); 
                    $update = $eventModel->update($formData, $formData['event_id']);
                    if ($update) {
                        $this->_helper->redirector("detail","package","default",$params);
                    } else {
                        $this->view->message = "couldn't update";
                        $this->_helper->redirector("edit","package","default",$params);
                    }
                }
            }else{
                $this->_helper->redirector("index","index","default");
            }
        }catch(Exception $e){
            $this->_helper->FlashMessenger->addMessage(array('alert' => $e->getMessage()));
            exit;
        }
    }
    public function addDetailAction()
    {
        $child = $this->_getParam("child");
        $adult = $this->_getParam("adult");
        $this->view->adult = $adult;
        $number = $child + $adult;
        $form = new Default_Form_BookingDetailForm($number);
        $this->view->form = $form;
        $this->view->html = $this->view->render("package/add-detail.phtml");
    }
}
