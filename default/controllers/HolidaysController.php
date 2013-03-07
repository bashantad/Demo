<?php

class Default_HolidaysController extends Zend_Controller_Action
{

    private $id;
    private $limit;
    private $offset;
    private $config;

    public function init()
    {
        /* Initialize action controller here */
//var_dump($_SESSION['defaultsession']['userlikedpackage']);  
        /* $identity = Zend_Auth::getInstance()->getIdentity();
          if ($identity) {
          $user = $identity->user_id;
          $session = Zend_Registry::get('defaultsession');
          $contentLikeModel = new Package_Model_NadUserPackageLikes();
          $list = $contentLikeModel->fetchAll("where user_id = {$user}");
          foreach ($list as $item) {
          $session->userlikedpackage[$item->package_id] = "package-liked";
          }
          } */
        $this->_helper->block->add('floatingnav');
        $this->_helper->block->add('loggedin');
        $this->_helper->block->add('footerouterwrapper');

        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'element_id');
        $this->id = $config->activities->id;
        $this->config = $config;
        $uploadDir = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "element.ini", 'upload_dir');
        $this->view->UploadDir = $uploadDir->package;

        $bootstrapConfig = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "application.ini", 'production');
        $this->limit = $bootstrapConfig->pagination->limit;
        $this->offset = $bootstrapConfig->pagination->offset;
    }

    private function _decryptTag($formdata)
    {
        if (isset($formdata['place'])) {
            if (!empty($formdata['place'])) {
                $p_array = array();
                foreach (explode(',', $formdata['place']) as $pid) {
                    $p_array[] = $this->view->rijndael->decrypt($pid);
                }
                $formdata['place'] = implode(',', $p_array);
            }
        }
        if (isset($formdata['activities'])) {
            if (!empty($formdata['activities'])) {
                $a_array = array();
                foreach (explode(',', $formdata['activities']) as $pid) {
                    $a_array[] = $this->view->rijndael->decrypt($pid);
                }
                $formdata['activities'] = implode(',', $a_array);
            }
        }
        return $formdata;
    }

    public function selectedAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout()->disableLayout();
        }
        $id = $this->_getParam('cid');
        $defaultTag = $this->_getParam('defaultTag') ? $this->_getParam('defaultTag') : '';
        if ($defaultTag) {
            $this->view->heading = "change";
        }
        $packageMstModel = new Package_Model_NadPackageMst();
        $rijndael = new NepalAdvisor_Rijndael_Decrypt();
        $contentId = $rijndael->decrypt($id);
        $this->view->id = $contentId;
        $datas = $packageMstModel->getMapper()->getRelatedPackageByContentId(array('cid' => $contentId, 'offset' => (int) $this->offset, 'default_tag' => $defaultTag));
        $this->view->offset = $this->limit;
        $this->view->datas = $datas;
    }

    public function indexAction()
    {
        $session = Zend_Registry::get('defaultsession');
        $this->view->contentClass = 'main-right-separator';
        $packageMstModel = new Package_Model_NadPackageMst();
        if ($_POST) {
            $formdata = $this->_decryptTag($this->trim_r($_POST));
            if (array_key_exists('tag', $formdata)) {
                if (array_key_exists($this->config->places->id, $formdata['tag'])) {
                    foreach ($formdata['tag'][$this->config->places->id] as $title => $val) {
                        if ((trim($title))) {
                            $session->selection[$this->config->places->id][$title]['tid'] = $this->view->rijndael->decrypt($val['tid']);
                        }
                    }
                }
                if (array_key_exists($this->config->activities->id, $formdata['tag'])) {
                    foreach ($formdata['tag'][$this->config->activities->id] as $title => $val) {
                        if ((trim($title))) {
                            $session->selection[$this->config->activities->id][$title]['tid'] = $this->view->rijndael->decrypt($val['tid']);
                        }
                    }
                }
            }
            if (isset($session->selection[$this->config->places->id]) && !empty($session->selection[$this->config->places->id])) {
                foreach ($session->selection[$this->config->places->id] as $key => $val) {
                    if (!in_array($val['tid'], explode(',', $formdata['place']))) {
                        unset($session->selection[$this->config->places->id][$key]);
                    }
                }
            }
            if (isset($session->selection[$this->config->activities->id]) && !empty($session->selection[$this->config->activities->id])) {
                foreach ($session->selection[$this->config->activities->id] as $key => $val) {
                    if (!in_array($val['tid'], explode(',', $formdata['activities']))) {
                        unset($session->selection[$this->config->activities->id][$key]);
                    }
                }
            }

            unset($formdata['tag']);

            $oldSessionData = $session->userdata;
            $session->userdata = $formdata;
            $session->userdata['field'] = isset($oldSessionData['field']) ? $oldSessionData['field'] : '';
            $session->userdata['operation'] = isset($oldSessionData['operation']) ? $oldSessionData['operation'] : '';
//$datas = $packageMstModel->getMapper()->getFilteredPackageDetails(array('offset' => (int) $this->offset));
        }
//        elseif ($session->userdata['duration'] && $session->userdata['budget'] || $session->userdata['place'] || $session->userdata['activities']) {
//            $datas = $packageMstModel->getMapper()->getFilteredPackageDetails(array('offset' => (int) $this->offset));
//        } else {
//            $datas = $packageMstModel->getMapper()->getPackageDetails(array('offset' => (int) $this->offset));
//            //$datas['recommended'] = $packageMstModel->getMapper()->fetchList(null, null, 5, 0);
//        }
        $datas = $packageMstModel->getMapper()->getFilteredPackageDetails(array('offset' => (int) $this->offset));

        $offerModel = new Package_Model_Offer();
        $offers = $offerModel->listAll();
        $this->view->offers = $offers;

        $this->_helper->block->add('yourpreferences');
        $this->_helper->block->add('peoplesay');
        $this->_helper->block->add('videostories');
        $this->_helper->layout->setLayout("two_column_layout");
        $this->view->offset = $this->limit;
        $this->view->limit = $this->limit;
        $this->view->datas = $datas;
    }

    public function detailAction()
    {
        $this->view->contentClass = 'main-right-separator';

        $this->view->url = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        $id = $this->_getParam('id');
        if ($this->_getParam('title')) {
            $id = $this->view->rijndael->decrypt($id);
        }
        $this->_helper->block->add('topsellingholidayslider');
        $packageMstModel = new Package_Model_NadPackageMst;
        $packageExists = $packageMstModel->getMapper()->find($id, NULL);
        $packageOverview = array();
        if ($packageExists) {
            $packageOverview = $packageMstModel->getMapper()->find($id, NULL)->toArray();
        } else {
            throw new NepalAdvisor_Exception('Invalid Package. Page you requested is not available.');
        }
        $packageMediaModel = new Package_Model_NadPackageMedia;
        $packageOfferModel = new Package_Model_Offer();
        $packageOfferModel->getActiveOfferByPackageId($id);
        $packagefixedModel = new Package_Model_NadPackageFixed;
        $dates = $packagefixedModel->getDetailById($id);
        if ($dates) {
            $this->view->fixedDepartures = $dates;
        }
        $this->view->offer = $packageOfferModel->getActiveOfferByPackageId($id);
        $this->view->overview = $packageOverview;
        $this->view->photos = $packageMediaModel->getMapper()->getMediaDetail($id);
        $packageModel = new Package_Model_Mapper_NadPackageMst();
        $this->view->relatedPackage = $packageModel->getRelatedPackage('', $id);
        $packageReviewModel = new ReviewRatings_Model_CommonReview();
        $packageLikeModel = new Package_Model_NadUserPackageLikes();
        $this->view->otherlikes = $packageLikeModel->getOtherLikes($id);
//var_dump($packageReviewModel->getAllReviewByTypeId('nad_package_review', $id, 'package_id'));exit();
        $this->view->review = $packageReviewModel->getAllReviewByTypeId('nad_package_review', $id, 'package_id');
        $objModel = new Package_Model_Mapper_NadPackageContent;
        $this->view->results = $objModel->getPackageContentDetails($id);
    }

    public function filteredDetailAction()
    {
        $formdata = $this->_decryptTag($this->trim_r($_GET));
        $session = Zend_Registry::get('defaultsession');
        $contentModel = new Content_Model_ContentMapper;
        if (isset($formdata['title'])) {
            if (($title = trim($formdata['title']))) {
                $tag = $this->view->rijndael->decrypt($formdata['id']);
                $content = $contentModel->getContentIdByElementCategoryId($tag);
                $session->selection[$formdata['element']][$title]['tid'] = $tag;
                $id = $content['content_id'];
                $count = 0;
                if (array_key_exists($id, $session->userlikecontent[$formdata['element']])) {
                    $count = (!count($session->userlikecontent[$formdata['element']][$id])) ? count($session->userlikecontent[$formdata['element']][$id]) : count($session->userlikecontent[$formdata['element']][$id]) + 1;
                }
                $session->userlikecontent[$formdata['element']][$id][$count] = array('element_category_id' => $tag);
            }
            unset($formdata['title']);
            unset($formdata['id']);
            unset($formdata['element']);
        }
        $queryResult = $this->getRequest()->getParam('query');
//        $session->userlikecontent[$this->id] = $formdata['place'];

        unset($formdata['query']);
        $oldSessionData = $session->userdata;
        $session->userdata = $formdata;
        $session->userdata['field'] = isset($oldSessionData['field']) ? $oldSessionData['field'] : '';
        $session->userdata['operation'] = isset($oldSessionData['operation']) ? $oldSessionData['operation'] : '';

        $offerModel = new Package_Model_Offer();
        $offers = $offerModel->listAll();

        if (!$queryResult) {
            $packageMstModel = new Package_Model_NadPackageMst();
            $datas = $packageMstModel->getMapper()->getFilteredPackageDetails(array('offset' => (int) $this->offset));
//$datas = $packageMstModel->getMapper()->getFilteredPackageDetails(array('limit'=>(int)$this->start));

            $result = array();
            $count = isset($datas["total"]) ? $datas["total"] : 0;

            if (is_array($datas["search"]))
                $count = $count ? $count : count($datas["search"]);
            else if (is_array($datas["recommended"]))
                $count = $count ? $count : count($datas["recommended"]);

            $result['count'] = $count;
            if ($count) {
                $result['html'] = $this->view->partial('holidays/list-holidays.phtml', array('datas' => $datas, 'offers' => $offers));
            } else {
                $result['html'] = $this->view->partial('holidays/list-holidays.phtml', array('datas' => $datas, 'offers' => $offers));
            }

            $this->view->result = json_encode($result);
        }
        $this->_helper->layout->disableLayout();
    }

    public function sortedDetailAction()
    {
        $session = Zend_Registry::get('defaultsession');
        $formdata = $this->_request->getParams();
        $sort = 0;
        if (key_exists('sort', $formdata)) {
            $sort = 1;
        }

        $session->userdata['field'] = isset($formdata['field']) ? $formdata['field'] : '';
        $session->userdata['operation'] = isset($formdata['operation']) ? $formdata['operation'] : '';
        $packageMstModel = new Package_Model_NadPackageMst();
        $request = isset($formdata['request']) ? $formdata['request'] : '';
        if ($request == 'previous-likes') {
// $datas = $packageMstModel->getMapper()->getRelatedPackageByContentId($formdata);           
            $datas = $packageMstModel->getMapper()->getPreviousLikes($formdata);
        } else {
            $datas = $packageMstModel->getMapper()->getFilteredPackageDetails($formdata);
        }

        $offerModel = new Package_Model_Offer();
        $offers = $offerModel->listAll();
        $this->view->offers = $offers;
        $this->view->sort = $sort;
        $this->view->datas = $datas;
        $this->view->clicks = isset($formdata['clicks']) ? $formdata['clicks'] : '';
        $this->view->isXmlHttpRequest = true;
        $this->_helper->layout->disableLayout();
    }

    public function clearpreferenceAction()
    {
        $session = Zend_Registry::get('defaultsession');
//        $session->userlikecontent = array(
        $session->userdata['duration'] = '';
        $session->userdata['duration-alias'] = '';
        $session->userdata['budget'] = '';
        $session->userdata['date'] = '';
        $session->userdata['place'] = '';
        $session->userdata['activities'] = '';
//        );

        $session->selection[$this->config->places->id] = array();
        $session->selection[$this->config->activities->id] = array();
        $session->selection[$this->config->travels->id] = array();

        $session->userlikecontent[$this->config->places->id] = array();
        $session->userlikecontent[$this->config->activities->id] = array();
        $session->userlikecontent[$this->config->hotels->id] = array();
        $session->userlikecontent[$this->config->travels->id] = array();

        $this->_helper->redirector('index');
    }

    public function addselectedtagAction()
    {
        $session = Zend_Registry::get('defaultsession');
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $formdata = $_GET;
            $formdata['id'] = $this->view->rijndael->decrypt($formdata['id']);
            $session->selection[$this->config->places->id][$formdata['title']]['tid'] = $formdata['id'];
            print json_encode(array('success' => true, 'session' => $_SESSION));
            exit;
        }
    }

    public function getdeparturedatesAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $formdata = $_GET;
            $packageid = $this->view->rijndael->decrypt($formdata['id']);
            $html = "<a class='close-fixed' href='javascript:void(0);'>x</a><ul>";
            $packagefixedModel = new Package_Model_NadPackageFixed;
            $dates = $packagefixedModel->getDetailById($packageid);
            foreach ($dates as $val) {
                $html .= "<li>" . date('d-M-Y', strtotime($val->date)) . "</li>";
            }
            $html .= '</ul>';
            print $html;
            exit;
        }
    }

    public function itineraryAction()
    {
        $package_id = $this->_getParam('id');
        $packageMapper = new Package_Model_Mapper_NadPackageMst();
        $package = $packageMapper->getItineraryDetail($package_id);
        $this->view->package = $package;
        $this->_helper->layout->disableLayout();
    }

    public function trim_r($array)
    {
        if (is_string($array)) {
            return trim($array);
        } else if (!is_array($array)) {
            return '';
        }
        $keys = array_keys($array);
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            if (is_array($array[$key])) {
                $array[$key] = $this->trim_r($array[$key]);
            } else if (is_string($array[$key])) {
                $array[$key] = trim($array[$key]);
            }
        }
        return $array;
    }

    public function likeAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $id = $this->view->rijndael->decrypt($this->_getParam('id'));
        if ($id) {
            $session = Zend_Registry::get('defaultsession');
            if ($identity) {
                $contentLikeModel = new Package_Model_NadUserPackageLikes();
                $data['user_id'] = $identity->user_id;
                $data['package_id'] = $id;
                $contentLikeModel->add($data);
            }
            $session->userlikedpackage[$id] = "package-liked";
            $value = array('result' => 'success');
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $likedContent = $this->view->renderLikedContent();
            $isRemoved = isset($session->userlikedpackage[$id]) == "package-liked" ? true : false;
            print json_encode(array('success' => $isRemoved, 'html' => $likedContent));
            exit;
        }
        $this->_helper->redirector('index');
    }

    public function removelikeAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $id = $this->view->rijndael->decrypt($this->_getParam('id'));
        if ($id) {
            $session = Zend_Registry::get('defaultsession');
            if (isset($session->userlikedpackage[$id]) == "package-liked") {
                unset($session->userlikedpackage[$id]);
            }
            if ($identity) {
                $contentLikeModel = new Package_Model_NadUserPackageLikes;
                $contentLikeModel->delete($id, $identity->user_id);
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $isRemoved = !isset($session->userlikepackage[$id]) ? true : false;
            $likedContent = $this->view->renderLikedContent();
            print json_encode(array('success' => $isRemoved, 'html' => $likedContent));
            exit;
        }

        $this->_helper->redirector('index');
    }

    public function previousLikesAction()
    {
        $contentLikeModel = new Package_Model_Mapper_NadPackageMst();
        $datas = $contentLikeModel->getPreviousLikes(array('request' => 'previous-likes', 'offset' => (int) $this->offset));
        $this->view->datas = $datas;
        $this->view->offset = $this->limit;
        $this->render('selected');
    }

    public function tellafriendAction()
    {
        if ($this->_helper->hasHelper('Layout')) {
            $this->_helper->layout->disableLayout();
        }
        $user = '';
        $sent = '';
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $user = Zend_Auth::getInstance()->getIdentity()->full_name;
        }
        $this->view->user = $user;
        if ($this->getRequest()->isPost()) {
            $modelNotification = new Notification_Model_EmailSettings();
            $formData = $this->getRequest()->getPost('email');
            $siteUrl = $this->getRequest()->getPost('site_url');
            $senderName = $this->getRequest()->getPost('sender_name');
            $senderEmail = $this->getRequest()->getPost('sender_email');
            $msg = $this->getRequest()->getPost('msg');
            $id = $this->view->rijndael->decrypt(end(explode('/', $siteUrl)));
            $packageModel = new Package_Model_Mapper_NadPackageMst();
            $id = intval(preg_replace("/[^0-9]/", "", $id));
            $packageContent = $packageModel->getPackageEmailDtl($id);
            $text = $this->view->wordLimiter(strip_tags($packageContent->description), 250);
            $emailParams = array(
                'sender' => $user ? $user : $senderName,
                'receiver' => "Friend",
                'site_url' => $siteUrl,
                'content_heading' => $packageContent->title,
                'image_url' => $this->view->baseUrl() . "/package/thumbnails/190x105/images/" . $packageContent->file_name,
                'site_content' => $text['text']
            );
            if ($msg) {
                $emailParams = array_merge($emailParams, array('sender_msg' => "Special message from your friend: " . $msg));
            }
            if (strstr($formData, ',')) {
                $formData = explode(',', $formData);
                foreach ($formData as $value) {
                    $errors = $this->_validate();
                    if (!empty($errors)) {
                        $this->view->errors = $errors;
                        $this->view->post = array(
                            'email' => $formData,
                            'site_url' => $siteUrl,
                            'sender_name' => $senderName,
                            'sender_email' => $senderEmail,
                            'msg' => $msg
                        );
                    } else {
                        $sent = $modelNotification->sendEmail($emailParams, 'tell_a_friend', $value);
                    }
                }
            } else {
                $errors = $this->_validate();
                if (!empty($errors)) {
                    $this->view->errors = $errors;
                    $this->view->post = array(
                        'email' => $formData,
                        'site_url' => $siteUrl,
                        'sender_name' => $senderName,
                        'sender_email' => $senderEmail,
                        'msg' => $msg
                    );
                } else {
                    $sent = $modelNotification->sendEmail($emailParams, 'tell_a_friend', $formData);
                }
            }
            if ($sent) {
                echo "Your message has been successfully sent";
                exit;
            } else {
                echo "Problem Sending Email.";
            }
        }
    }

    private function _validate()
    {
        $errors = array();
        $post = $this->getRequest()->getPost();
        $email = $this->getRequest()->getPost('sender_email');
        $validator = new Zend_Validate_EmailAddress();
        if (!$validator->isValid($email) && trim($email)) {
            $errors['sender_email'] = "Please enter a valid email";
        }
        if (!trim($post['email'])) {
            $errors['email'] = "Value is required and can't be empty.";
        }

        if (!$validator->isValid($post['email'])) {
            $errors['email'] = "Please enter a valid email";
        }
        return $errors;
    }

    public function printAction()
    {
        $id = $this->_getParam('id');
        $title = $this->_getParam('title');
        if ($title) {
            $id = $this->view->rijndael->decrypt($id);
        }

        if (!$id) {
            throw new Exception('Package id is not specified.');
            exit;
        }

        $packageModel = new Package_Model_Mapper_NadPackageMst();
        $data = $packageModel->getpackagedataforprint($id);

        $html = $this->view->partial('pdf/package_detail_print.phtml', array('data' => $data));

        $this->view->data = $html;

        $this->_helper->layout()->disableLayout();
    }

}
