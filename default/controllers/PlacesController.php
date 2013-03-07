<?php

class Default_PlacesController extends Zend_Controller_Action
{

    private $id;
    private $limit;

    public function init()
    {
        $this->_helper->block->add('floatingnav');
        $this->_helper->block->add('loggedin');
        $this->_helper->block->add('topsellingholidayslider');
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'element_id');
        $this->id = $this->view->id = $config->places->id;
        $limitConfig = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'tag_limit');
       // $this->limit = $limitConfig->limit;
        $this->limit = '3';
        $this->view->url = $this->view->siteUrl . Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
    }

    public function indexAction()
    {
        $id = $this->id;
        $eleCatModel = new Package_Model_ElementCategory();
        //$this->view->element = $eleCatModel->getTaggedContentElementWise($id, $this->limit);
        $this->view->element = $eleCatModel->getTaggedContentElementWise($id, $this->limit);
        $this->view->id = $id;
    }

    public function detailAction()
    {
        $this->view->contentClass = 'main-right-separator';
        // var_dump($results);
        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $cat = isset($ids[1]) ? $ids[1] : '';
        $chid = (isset($ids[2]) && $ids[2]) ? explode(',', $ids[2]) : array();
        $this->_helper->block->add('footerouterwrapper');
        $objModel = new Content_Model_ContentMapper;
        $this->view->results = $objModel->getContentDetails($id, $this->id);
        $eleCatModel = new Package_Model_ElementCategory();
        if (strstr($cat, 'others')) {
            $cat = explode('::', $cat);
            $this->view->element = $eleCatModel->getReferencedElementCategory($this->id, $id, $cat[1]);
        } /* elseif ($cat == 'destinations') {
          if ($cat) {
          if (!is_array($cat)) {
          $cat = (array) $cat;
          }
          $cat = array_merge($cat, $chid);
          } else {
          $cat = array();
          }
          $this->view->element = $eleCatModel->getReferencedElementCategory($this->id, $id, $cat);
          } */ else {
            $this->view->element = $eleCatModel->getReferencedElementCategory($this->id, $id, $cat);
        }
        $ratingModel = new ReviewRatings_Model_CommonRating();
        $ratingData = $ratingModel->getRatingByPackageId('nad_content_rating', $value = $id, $fieldName = 'content_id');
        $this->view->rating = $ratingData;
        $contentReviewModel = new ReviewRatings_Model_CommonReview();
        $this->view->review = $contentReviewModel->getAllReviewByTypeId('nad_content_review', $id, 'content_id');

        $packageMstModel = new Package_Model_NadPackageMst();
        $datas = $packageMstModel->getMapper()->getRelatedPackageByContentId(array('cid' => $id, 'limit' => (int) $this->limit));

        $this->view->datas = $datas;        
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
            $contentModel = new Content_Model_ContentMapper;
            $contentDetail = $contentModel->find($id);
            if (!array_key_exists($id, $session->userlikecontent[$this->id])) {
                //$content = $contentTagModel->getTagsByContentId($id);
                //$liked = $this->r_implode(",", $content);
                $count = 0;
                if (array_key_exists($id, $session->userlikecontent[$this->id])) {
                    $count = (!count($session->userlikecontent[$this->id][$id])) ? count($session->userlikecontent[$this->id][$id]) : count($session->userlikecontent[$this->id][$id]) + 1;
                }
                $session->userlikecontent[$this->id][$id][$count] = array('element_category_id' => $contentDetail['element_category_id']);
                $session->userdata['place'] = ($session->userdata['place']) ? $session->userdata['place'] . "," . $contentDetail['element_category_id'] : $contentDetail['element_category_id'];
                if ($identity) {
                    $contentLikeModel = new Content_Model_NadUserContentLikes;
                    $data['user_id'] = $identity->user_id;
                    $data['content_id'] = $id;
                    $data['element_id'] = $this->id;
                    $contentLikeModel->add($data);
                }
            }
            if (!array_key_exists($contentDetail['heading'], $session->selection[$this->id])) {
                //$session->selection[$this->id][$contentDetail['heading']]['cid'] = $id;
                $session->selection[$this->id][$contentDetail['heading']]['tid'] = $contentDetail['element_category_id'];
            } else {
                if (!array_key_exists('cid', $session->selection[$this->id][$contentDetail['heading']])) {
                    //$session->selection[$this->id][$contentDetail['heading']]['cid'] = $id;
                    $session->selection[$this->id][$contentDetail['heading']]['tid'] = $contentDetail['element_category_id'];
                }
            }
            $value = array('result' => 'success');
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $likedContent = $this->view->renderLikedContent();
            $isRemoved = isset($session->userlikecontent[$this->id][$id]) ? true : false;
            print json_encode(array('success' => $isRemoved, 'html' => $likedContent));
            exit;
        }

        $this->_helper->redirector('index');
    }

    public function removelikeAction()
    {
        //var_dump('sdfasdfsda');exit();
        $identity = Zend_Auth::getInstance()->getIdentity();
        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $cat = isset($ids[1]) ? $ids[1] : '';
        //$id = $this->getRequest()->isXmlHttpRequest() ? $this->_getParam('id') : $this->view->rijndael->decrypt($this->_getParam('id'));
        if ($id) {
            $contentModel = new Content_Model_ContentMapper;
            $contentDetail = $contentModel->find($id);
            $session = Zend_Registry::get('defaultsession');
            if (array_key_exists($id, $session->userlikecontent[$this->id])) {
                unset($session->userlikecontent[$this->id][$id]);
            }
            if (array_key_exists($contentDetail['heading'], $session->selection[$this->id])) {
                unset($session->selection[$this->id][$contentDetail['heading']]);

                $placesArray = explode(',', $session->userdata['place']);
                $key = array_search($id, $placesArray);
                unset($placesArray[$key]);
                $session->userdata['place'] = implode(',', $placesArray);
            }
            if ($identity) {
                $contentLikeModel = new Content_Model_NadUserContentLikes;
                $contentLikeModel->delete($id, $identity->user_id);
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $likedContent = $this->view->renderLikedContent();
            $isRemoved = isset($session->userlikecontent[$this->id][$id]) ? false : true;
            print json_encode(array('success' => $isRemoved, 'html' => $likedContent));
            exit;
        }

        $this->_helper->redirector('index');
    }

    public function removeselectionAction()
    {
        $title = $this->_getParam('title');
        $session = Zend_Registry::get('defaultsession');
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $likedContent = $this->view->renderLikedContent();
            if (key_exists($title, $session->selection[$this->id])) {
                $cid = null;
                if (array_key_exists('cid', $session->selection[$this->id][$title])) {
                    if ($session->selection[$this->id][$title]['cid']) {
                        $cid = $session->selection[$this->id][$title]['cid'];
                        unset($session->userlikecontent[$this->id][$cid]);
                    }
                }
                $tid = null;
                if (array_key_exists('tid', $session->selection[$this->id][$title])) {
                    if ($session->selection[$this->id][$title]['tid']) {
                        $tid = $session->selection[$this->id][$title]['tid'];
                        $placesArray = explode(',', $session->userdata['place']);
                        $key = array_search($tid, $placesArray);
                        unset($placesArray[$key]);
                        $session->userdata['place'] = implode(',', $placesArray);

                        $contentModel = new Content_Model_ContentMapper;
                        $contentDetail = $contentModel->getContentIdByElementCategoryId($tid);
                        unset($session->userlikecontent[$this->id][$contentDetail['content_id']]);
                    }
                }
                unset($session->selection[$this->id][$title]);
            }

            $isRemoved = isset($session->selection[$this->id][$title]) ? false : true;
            print json_encode(array('success' => $isRemoved, 'asd' => $_SESSION));
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
        //$this->view->contentClass = 'main-right-separator';
        $ecname = $this->_getParam('ecname');
        $ecid = $this->_getParam('ecid');
        $rijndael = new NepalAdvisor_Rijndael_Decrypt();
        $elementCategoryId = $rijndael->decrypt($ecid);
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->elementName = $ecname;
        $this->view->element = $eleCatModel->getTaggedContentElementCategoryIdWise($elementCategoryId,$this->limit);
    }

    public function previousLikesAction()
    {
        $CatModel = new Content_Model_NadUserContentLikes();
        $cat = $CatModel->getPreviousLikes($this->id);
        $this->view->element = $cat;
        $this->render('view-all');
    }

}
