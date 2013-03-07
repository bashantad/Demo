<?php

class Default_HotelsController extends Zend_Controller_Action
{

    private $id;
    private $limit;

    public function init()
    {
        /* $ajaxContext = $this->_helper->getHelper('AjaxContext');
          $ajaxContext->addActionContext('filtered', 'json')
          ->initContext(); */
        $this->_helper->block->add('loggedin');
        $this->_helper->block->add('topsellingholidayslider');
        $this->_helper->block->add('floatingnav');
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'element_id');
        $this->id = $this->view->id = $config->hotels->id;
        $limitConfig = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'tag_limit');
        $this->limit = $limitConfig->limit;
        $this->view->thumbPath = '/uploads/images/Element/thumbnails/images/70x70/';
        $this->view->url = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
    }

    public function indexAction()
    {
        $session = Zend_Registry::get('defaultsession');
        $destinations = isset($session->selection[$this->id]['destinations']) ? $session->selection[$this->id]['destinations'] : '';
        $this->view->contentClass = 'main-right-separator';
        $id = $this->id;
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->element = $eleCatModel->getHotelContentElementWise($id, $this->limit, "$destinations");
        $this->view->locations = $eleCatModel->getHotelLocations();
        $this->view->tags = $eleCatModel->getHotelTags($this->id);
        $this->view->id = $id;
        $elementDtlModel = new Package_Model_ElementDetail();
        $session = Zend_Registry::get('defaultsession');
        $contentIds = array_keys($session->userlikecontent[$this->id]);
        $contentIds = implode(",", $contentIds);
        $elementDetail = array();
        if ($contentIds) {
            $elementDetail = $elementDtlModel->filterElementDtlByContentIds($contentIds);
        }
        if (!$elementDetail) {
            $elementDetail = $elementDtlModel->fetchBookDtlByElementId($this->id, $this->limit);
        }
        $this->view->elementDetail = $elementDetail;
    }

    public function detailAction()
    {
        $this->view->contentClass = 'main-right-separator';
        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $cat = (isset($ids[1]) && $ids[1]) ? (int) $ids[1] : array();
        $chid = (isset($ids[2]) && $ids[2]) ? explode(',', $ids[2]) : array();
        $element_id = $this->id;
        if ($chid) {
            $element_id = 9;
        }
        if ($cat) {
            if (!is_array($cat)) {
                $cat = (array) $cat;
            }
        } else {
            $cat = array();
        }
        $cat = array_merge($cat, $chid);
        $this->_helper->block->add('footerouterwrapper');
        $objModel = new Content_Model_ContentMapper;
        $this->view->results = $objModel->getContentDetails($id, $this->id);
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->element = $eleCatModel->getReferencedElementCategory($element_id, $id, $cat);
        $companyModel = new Company_Model_Company();
        $coID = $companyModel->getDetailByContentId($id);
        $this->view->company = $coID;
        $packageReviewModel = new ReviewRatings_Model_CommonReview();
        if (isset($coID['company_id'])) {
            $this->view->review = $packageReviewModel->getAllReviewByTypeId('nad_company_review', $coID['company_id'], 'company_id');
        }else{
            $this->view->review = '';
        }
    }

    public function likeAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $cat = isset($ids[1]) ? $ids[1] : "destinations";
        $contentTagModel = new Content_Model_NadContentTagMapMapper;
        $elementDetail = array();
        if ($id) {
            $session = Zend_Registry::get('defaultsession');
            $session->userlikecontent[$this->id];
            if (!array_key_exists($id, $session->userlikecontent[$this->id])) {
                $content = $contentTagModel->getTagsByContentId($id);
                $liked = $this->r_implode(",", $content);
                $session->userlikecontent[$this->id][$id] = $content;
            }
            $cat = isset($cat) ? $cat : "destinations";
            if (!array_key_exists($cat, $session->selection[$this->id])) {
                $session->selection[$this->id][$cat] = "$id";
            } else {
                $ids = explode(',', $session->selection[$this->id][$cat]);
                $ids[] = $id;
                $ids = array_unique($ids);
                $session->selection[$this->id][$cat] = implode(',', $ids);
            }
            $contentIds = array_keys($session->userlikecontent[$this->id]);
            $contentIds = implode(",", $contentIds);
            $elementDtlModel = new Package_Model_ElementDetail();
            $elementDetail = $elementDtlModel->filterElementDtlByContentIds($contentIds);
        }
        if (!$elementDetail) {
            $elementDetail = $elementDtlModel->fetchBookDtlByElementId($this->id, $this->limit);
        }

        $value = array('result' => 'success');
        $this->view->elementDetail = $elementDetail;
        $suggestions = $this->view->render("hotels/like.phtml");

        $destinations = isset($session->selection[$this->id]['destinations']) ? $session->selection[$this->id]['destinations'] : '';
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->contentClass = 'main-right-separator';
        $this->view->element = $eleCatModel->getHotelContentElementWise($this->id, $this->limit, "$destinations");
        $this->view->id = $this->id;
        $filterLocation = $this->view->render("hotels/index.phtml");

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $likedContent = $this->view->renderLikedContent();
            $isRemoved = isset($session->userlikecontent[$this->id][$id]) ? true : false;
            print json_encode(array('success' => $isRemoved, 'html' => $likedContent, 'suggestions' => $suggestions, 'size' => sizeof($elementDetail), 'type' => $cat, 'filterLocation' => $filterLocation));
            exit;
        }

        $this->_helper->redirector('index');
    }

    public function removelikeAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $cat = isset($ids[1]) ? $ids[1] : 'destinations';
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

            if ($identity) {
                $contentLikeModel = new Content_Model_NadUserContentLikes;
                $contentLikeModel->delete($id, $identity->user_id);
            }
        }
        $contentIds = array_keys($session->userlikecontent[$this->id]);
        $contentIds = implode(",", $contentIds);
        $elementDtlModel = new Package_Model_ElementDetail();
        $elementDetail = array();
        if ($contentIds) {
            $elementDetail = $elementDtlModel->filterElementDtlByContentIds($contentIds);
        }
        if (!$elementDetail) {
            $elementDetail = $elementDtlModel->fetchBookDtlByElementId($this->id, $this->limit);
        }
        $this->view->elementDetail = $elementDetail;
        $suggestions = $this->view->render("hotels/like.phtml");

        $destinations = isset($session->selection[$this->id]['destinations']) ? $session->selection[$this->id]['destinations'] : '';
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->contentClass = 'main-right-separator';
        $this->view->element = $eleCatModel->getHotelContentElementWise($this->id, $this->limit, "$destinations");
        $this->view->id = $this->id;
        $filterLocation = $this->view->render("hotels/index.phtml");
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $likedContent = $this->view->renderLikedContent();
            $isRemoved = isset($session->userlikecontent[$this->id][$id]) ? false : true;
            print json_encode(array('success' => $isRemoved, 'html' => $likedContent, 'suggestions' => $suggestions, 'size' => sizeof($elementDetail), 'type' => $cat, 'filterLocation' => $filterLocation));
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

    public function filteredAction()
    {
        $options = $this->_getAllParams();

        $limitConfig = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'star_rating');
        $ratings = $limitConfig->star->rating->value->toArray();

        if (key_exists('tags', $options)) {
            if ($options['tags'] != '') {
                $options['tags'] = $ratings[$options['tags'] - 1];
            }
        }

        $this->view->id = $this->id;
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->element = $eleCatModel->getHotelContentElementWise($this->id, $this->limit, $options);
        $filtered = $this->view->render('hotels/filtered.phtml');

        $elementDtlModel = new Package_Model_ElementDetail();
        $elementDetail = $elementDtlModel->fetchBookDtlByElementId($this->id, $this->limit, $options);
        $size = sizeof($elementDetail);
        if(!$elementDetail){
            $elementDetail = $elementDtlModel->fetchBookDtlByElementId($this->id, $this->limit);
        }
        $this->view->elementDetail = $elementDetail;
        $suggestions = $this->view->render("hotels/like.phtml");
        if ($this->getRequest()->isXmlHttpRequest()) {
            print json_encode(array('html' => $filtered, 'size' => $size, 'suggestions' => $suggestions));
            exit;
        }
    }

    public function suggestedAction()
    {
        $options = $this->_getAllParams();

        $this->view->id = $this->id;
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->element = $eleCatModel->getHotelContentElementWise($this->id, $this->limit, $options);
        $filtered = $this->view->render('hotels/filtered.phtml');
        if ($this->getRequest()->isXmlHttpRequest()) {
            print json_encode(array('html' => $filtered));
            exit;
        }
    }

}

