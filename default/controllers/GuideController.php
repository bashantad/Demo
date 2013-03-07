<?php

class Default_GuideController extends Zend_Controller_Action
{

    private $_cid = 274;

    public function init()
    {
        $this->_helper->block->add('loggedin');
        //  $this->_helper->block->add('floatingnav');
        $this->_helper->block->add('topsellingholidayslider');
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'element_id');
        $this->id = $this->view->id = $config->travels->id;
        $limitConfig = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'tag_limit');
        $this->limit = $limitConfig->limit;
        $this->view->thumbPath = '/uploads/images/Element/thumbnails/images/70x70/';
        $this->view->dfController = $this->getRequest()->getControllerName();
    }

    public function indexAction()
    {
        $this->view->contentClass = 'main-right-separator';
        $id = $this->_getParam('id');
        $rijndael = new NepalAdvisor_Rijndael_Decrypt();
        $id = $rijndael->decrypt($id);
        //$id = $id ? $id : '';
        $model = new Content_Model_ContentMapper();
        $guideDetails = $model->fetchHierarchyByParentId('274');
        $this->view->guideDetails = $guideDetails;
        if ($id) {
            $contentDetails = $model->getDetailById($id);
            $this->view->contentDetails = $contentDetails;
        }

        $model = new Content_Model_ContentMapper();
        // Fetch all the parent contends ids corresponding to the cid
        $pcid = $model->findParentsByContentId($id, 2);
        $breadcrumb = $model->fetchContentBreadCrumb($pcid);
        /*
         * If no parent content ids found, breadcrumb won't be generated 
         * So, assume that the current parms id as parent id
         */
        if (!$pcid) {
            $breadcrumb['pcid'] = array($id);
            $subMenuDetails = $model->fetchHierarchyByParentId($id);
            $this->view->subMenuDetails = $subMenuDetails;
            //var_dump($subMenuDetails);
        }
        $this->view->breadcrumb = $breadcrumb;

        $this->_helper->block->add('peoplesay');
        //$this->_helper->block->add('suggestedholidays');
        $this->_helper->layout->setLayout("two_column_layout");
    }

    /*
    protected function _sendPost($url, $data)
    {
        $ch = curl_init(); // initialize curl handle 
        curl_setopt($ch, CURLOPT_URL, $url); // set url to post to 
        curl_setopt($ch, CURLOPT_FAILONERROR, 1); //Fail on error
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable 
        curl_setopt($ch, CURLOPT_POST, 1); // set POST method 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // add POST fields
        $result = curl_exec($ch); // run the whole process 
        curl_close($ch);
        
        return $result;
    }
     * 
     */

}

