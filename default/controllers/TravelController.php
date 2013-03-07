<?php

class Default_TravelController extends Zend_Controller_Action
{

    private $id;
    private $limit;

    public function init()
    {
        $this->_helper->block->add('loggedin');
        $this->_helper->block->add('floatingnav');
        $this->_helper->block->add('topsellingholidayslider');
        $configPath = APPLICATION_PATH.DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini";
        $config = new Zend_Config_Ini($configPath, 'element_id');
        $this->id = $this->view->id = $config->travels->id;
        $this->to = isset($config->from->id) ? $config->from->id : '';
        $this->from = isset($config->to->id) ? $config->to->id : '';
        $limitConfig = new Zend_Config_Ini($configPath, 'tag_limit');
        $this->limit = $limitConfig->limit;
        $this->view->thumbPath = '/uploads/images/Element/thumbnails/images/70x70/';
        $this->view->url = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
    }

    public function indexAction()
    {
        $this->view->contentClass = 'main-right-separator';
        $session = Zend_Registry::get('defaultsession');
        $travelLike = $session->selection[$this->id];
        $eleDtlModel = new Package_Model_ElementDetail();
        $elementDetail = array();
        if($travelLike){
            $fromContentIds = '';
            $toContentIds = '';
            if(array_key_exists($this->from,$travelLike)){
                $fromContentIds = $travelLike[$this->from];
            }
            if(array_key_exists($this->to, $travelLike)){
                $toContentIds = $travelLike[$this->to];    
            }
            $elementDetail = $eleDtlModel->fetchElementDtlByFromAndToIds($fromContentIds,$toContentIds, $this->limit);    
        }
        if(!$elementDetail){
            $elementDetail = $eleDtlModel->fetchBookDtlByElementId($this->id, $this->limit);    
        }
        $id = $this->id;
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->element = $eleCatModel->getTaggedContentElementWise($id, $this->limit);
        $this->view->id = $id;
        $this->view->elementDetail = $elementDetail;
    }

    public function detailAction()
    {
        $this->view->contentClass = 'main-right-separator';

        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $cat = isset($ids[1]) ? $ids[1] : '';

        $this->_helper->block->add('footerouterwrapper');
        $objModel = new Content_Model_ContentMapper;
        $this->view->results = $objModel->getContentDetails($id, $this->id);
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->element = $eleCatModel->getReferencedElementCategory($this->id, $id, $cat);
        $ratingModel = new ReviewRatings_Model_CommonRating();
        $ratingData = $ratingModel->getRatingByPackageId('nad_content_rating', $value = $id, $fieldName = 'content_id');
        $this->view->rating = $ratingData;
        $contentReviewModel = new ReviewRatings_Model_CommonReview();
        $this->view->review = $contentReviewModel->getAllReviewByTypeId('nad_content_review', $id, 'content_id');
    }

    public function likeAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $cat = isset($ids[1]) ? $ids[1] : '';
        $contentTagModel = new Content_Model_NadContentTagMapMapper;
        if ($id) {
            $session = Zend_Registry::get('defaultsession');
            if (!array_key_exists($id, $session->userlikecontent[$this->id])) {
                $content = $contentTagModel->getTagsByContentId($id);
                $liked = $this->r_implode(",", $content);
                $session->userlikecontent[$this->id][$id] = $content;
            }
            if (!array_key_exists($cat, $session->selection[$this->id])) {
                $session->selection[$this->id][$cat] = "$id";
            } else {
                $ids = explode(',', $session->selection[$this->id][$cat]);
                $ids[] = $id;
                $ids = array_unique($ids);
                $session->selection[$this->id][$cat] = implode(',', $ids);
            }
            $travelLike = $session->selection[$this->id];
            $eleDtlModel = new Package_Model_ElementDetail();
            $elementDetail = array();
            if($travelLike){
                $fromContentIds = '';
                $toContentIds = '';
                if(array_key_exists($this->from,$travelLike)){
                    $fromContentIds = $travelLike[$this->from];
                }
                if(array_key_exists($this->to, $travelLike)){
                    $toContentIds = $travelLike[$this->to];    
                }
                $elementDetail = $eleDtlModel->fetchElementDtlByFromAndToIds($fromContentIds,$toContentIds, $this->limit);    
            }
            if(!$elementDetail){
                $elementDetail = $eleDtlModel->fetchBookDtlByElementId($this->id, $this->limit);    
            }
            $this->view->elementDetail = $elementDetail;
            $suggestions = $this->view->render("travel/like.phtml");
            if ($identity) {
                $contentLikeModel = new Content_Model_NadUserContentLikes;
                $data['user_id'] = $identity->user_id;
                $data['content_id'] = $id;
                $data['element_id'] = $this->id;
                $contentLikeModel->add($data);
            }
            $value = array('result' => 'success');
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $likedContent = $this->view->renderLikedContent();
            $isRemoved = isset($session->userlikecontent[$this->id][$id]) ? true : false;
            print json_encode(array('success' => $isRemoved, 'html' => $likedContent,'suggestions'=>$suggestions,'size'=>sizeof($elementDetail)));
            exit;
        }

        $this->_helper->redirector('index');
    }

    public function removelikeAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $cat = isset($ids[1]) ? $ids[1] : '';
        if ($id) {
            $session = Zend_Registry::get('defaultsession');
            if (array_key_exists($id, $session->userlikecontent[$this->id])) {
                unset($session->userlikecontent[$this->id][$id]);
            }
            if (array_key_exists($cat, $session->selection[$this->id])) {
                $ids = explode(',', $session->selection[$this->id][$cat]);
                $ids = array_unique($ids);
                $position = array_search($id, $ids);
                unset($ids[$position]);
                $session->selection[$this->id][$cat] = implode(',', $ids);
            }
        }
        if ($identity) {
            $contentLikeModel = new Content_Model_NadUserContentLikes;
            $contentLikeModel->delete($id, $identity->user_id);
        }
        $elementDetail = array();
        $travelLike = $session->selection[$this->id];
        $eleDtlModel = new Package_Model_ElementDetail();
        if($travelLike){
            $fromContentIds = '';
            $toContentIds = '';
            if(array_key_exists($this->from,$travelLike)){
                $fromContentIds = $travelLike[$this->from];
            }
            if(array_key_exists($this->to, $travelLike)){
                $toContentIds = $travelLike[$this->to];    
            }
            $elementDetail = $eleDtlModel->fetchElementDtlByFromAndToIds($fromContentIds,$toContentIds, $this->limit);    
        }
        if(!$elementDetail){
            $elementDetail = $eleDtlModel->fetchBookDtlByElementId($this->id, $this->limit);    
        }
        $this->view->elementDetail = $elementDetail;
        $suggestions = $this->view->render("travel/like.phtml");
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $likedContent = $this->view->renderLikedContent();
            $isRemoved = isset($session->userlikecontent[$this->id][$id]) ? false : true;
            print json_encode(array('success' => $isRemoved, 'html' => $likedContent,'suggestions'=>$suggestions,'size'=>sizeof($elementDetail)));
            exit;
        }

        $this->_helper->redirector('index');
    }

    function r_implode($glue, $pieces)
    {
        foreach ($pieces as $r_pieces) {
            if (is_array($r_pieces)) {
                $retVal[] = $this->r_implode($glue, $r_pieces);
            } else if (is_object($r_pieces)) {
                $r_pieces = (array) $r_pieces;
                $retVal[] = $this->r_implode($glue, $r_pieces);
            } else {
                $retVal[] = $r_pieces;
            }
        }
        return implode($glue, $retVal);
    }

    public function viewAllAction()
    {
        $this->view->contentClass = 'main-right-separator';
        $ecid = $this->_getParam('ecid');
        $rijndael = new NepalAdvisor_Rijndael_Decrypt();
        $elementCategoryId = $rijndael->decrypt($ecid);
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->element = $eleCatModel->getTaggedContentElementCategoryIdWise($elementCategoryId);
        
        $eleDtlModel = new Package_Model_ElementDetail();
        $this->view->elementDetail = $eleDtlModel->fetchBookDtlByElementId($this->id, $this->limit);
    }

    public function previousLikesAction()
    {
        $CatModel = new Content_Model_NadUserContentLikes();
        $cat = $CatModel->getPreviousLikes($this->id);
        $this->view->element = $cat;
        $this->render('view-all');
    }

}
