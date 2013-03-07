<?php

class Default_InspirationController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("comment","json")
                    ->addActionContext("spam-report","json")
                    ->initContext();
        $uploadDir = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'upload_dir');
        $this->view->UploadDir = $uploadDir->package;
        $this->view->url = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
    }

    public function indexAction()
    {        
        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $this->_helper->block->add('floatingnav');
        $inspireModel = new Package_Model_Mapper_NadInspire();
        $this->view->inspirationList = $inspireModel->getInspirationsPageListById($id);
        $packageModel = new Package_Model_Mapper_NadPackageMst();
        $this->view->relatedPackage = $packageModel->getRelatedPackage('', '', $id);
        $inspireModel = new Package_Model_Mapper_NadInspire();
        $this->view->relatedInspiration = $inspireModel->getRelatedInspirationsByPackageId($id);
        $reviewInspireModel = new Package_Model_ReviewInspiration();
        $this->view->comments = $reviewInspireModel->getComments($id);
    }

    public function storyAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout()->disableLayout();
            //$form = new Default_Form_storyForm();
            //$this->view->form = $form;
        }
    }
    public function commentAction()
    {
        $params = $this->view->rijndael->decrypt($this->_getParam("q"));
        $arr = array();
        $str = parse_str($params, $arr);
        $formData['inspire_id'] = $arr['inspire_id'];
        $formData['title'] = $this->_getParam("title");
        $formData['description'] = $this->_getParam("description");
        $reviewInspireModel = new Package_Model_ReviewInspiration();
        $this->view->isSubmited = $reviewInspireModel->add($formData);
        //$this->view->html = $this->view->render("inspiration/comment.phtml");      
    }
    public function spamReportAction()
    {
        $reviewId = $this->_getParam("q");
        $reviewInspireModel = new Package_Model_ReviewInspiration();
        $this->view->isSubmited = $reviewInspireModel->reportSpam($reviewId,"Y");
    }

}

