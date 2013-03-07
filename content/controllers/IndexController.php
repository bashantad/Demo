<?php

class Content_IndexController extends Zend_Controller_Action
{

    private $_rootContentElements = array();

    public function init()
    {
        $module = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();

        $this->_rootContentElements = array(
            '161' => array('element' => 'Hotels', 'id' => 2),
            '163' => array('element' => 'Places', 'id' => 9),
            '230' => array('element' => 'Activities', 'id' => 3),
            '252' => array('element' => 'Travel', 'id' => 6),
            '274' => array('element' => 'Guide', 'id' => ''),
        );

        $this->view->path = "/{$module}/{$controller}";
        $this->view->tab = $this->changeTabbar();
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('view', 'json')
                ->addActionContext('add', 'json')
                ->addActionContext('edit', 'json')
                ->addActionContext('delete', 'json')
                ->addActionContext('update', 'json')
                ->addActionContext('upload', 'json')
                ->addActionContext('watermark', 'json')
                ->addActionContext('tag', 'json')
                ->addActionContext('change', 'json')
                ->addActionContext('status', 'json')
                ->addActionContext('approve', 'json')
                ->addActionContext('validateform', 'json')
                ->addActionContext('change-order', 'json')
                ->addActionContext('check-unique-name', 'json')
                ->addActionContext('leaf-filter', 'json')
                ->addActionContext('sort', 'html')
                ->initContext();
        $this->view->placeholder('title')->set('Content');

        $options = array(
            'mapper' => new Content_Model_ContentMapper,
            'label' => 'heading',
            'method' => 'fetchHierarchy',
            'search' => true
        );
        // Generate tree of the corresponding content_id
        $this->tree = new NepalAdvisor_Tree_Controller_Plugin_Tree($options);
    }

    public function indexAction()
    {
//        $path = PUBLIC_PATH.DIRECTORY_SEPARATOR."package".DIRECTORY_SEPARATOR."thumbnails".DIRECTORY_SEPARATOR."700x400".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR;
//        foreach(scandir($path) as $file)
//            if($file=='.'||$file=='..'){
//                continue;
//            }else{
//                echo $this->_helper->waterMark($file,$path);
//            }
//        exit;
        $this->renderScript('index/dashboard.phtml');
    }

    public function dashboardAction()
    {
        
    }

    public function changeOrderAction()
    {
        $ids = $this->_getParam('data');
        $content_ids = explode(',', $ids);
        $parent_id = $this->_getParam('parent_id');
        $contentMapMapper = new Content_Model_ContentMapMapper();
        $orderNos = $contentMapMapper->getOrderOfSiblings($parent_id);
        $contentMapper = new Content_Model_ContentMapper();
        foreach ($content_ids as $key => $content_id) {
            $data['order_no'] = $orderNos[$key];
            $msg = $contentMapper->updateOrderbyId($data, $content_id);
        }
        $this->view->html = $msg;
    }

    public function viewAction($cid = 0)
    {
        try {
            $treeTitle = 'Hierarchy Tree';
            if ($this->getRequest()->isXmlHttpRequest()) {
                $this->_helper->layout->disableLayout();
                $treeTitle = '';
            }

            $pCid = (int) $this->getRequest()->getParam('active_id');
            $pCid = $pCid ? $pCid : (int) $this->getRequest()->getParam('id');
            $cid = $cid ? $cid : $pCid;

            if (!$cid) {
                throw new Zend_Exception('Invalid Request.');
            }
            $contentMapper = new Content_Model_ContentMapper;
            $contentMapper->setDbTable('Content_Model_DbTable_Content');
            $content = $contentMapper->find($cid);
            if (null === $content) {
                throw new Zend_Exception('Content could not be found.');
            }

            if ($content) {
                // Generate tree of the corresponding content_id
                $items = $this->tree->getHierarchyTree();
                $attributes = array(
                    'id' => 'browser',
                    'class' => 'filetree treeview'
                );

                $treeHtml = $this->tree->themeHierarchyItemList($items, 'Hierarchy Tree', 'ul', $attributes);
                $this->view->content = $content->toArray();
                $this->view->term_id = $content->content_id;
                $this->view->tree = $treeHtml;
                $this->view->tab = $this->changeTabbar();
                $this->view->html = $this->view->render('index/view.ajax.phtml');
            }
        } catch (Zend_Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }

    public function statusAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }

        try {
            $postData = $this->getRequest()->getPost();
            $model = new Content_Model_ContentMapper();
            $update = $model->updateStatus($postData['content_id']);
            $status = ($update == "E") ? "Enabled" : "Disabled";
            if ($status == "Disabled") {
                $img = '<img src="/themes/admin/default/images/others/cross-on-white.gif" alt="unchecked"/>';
            } else {
                $img = '<img src="/themes/admin/default/images/others/tick-on-white.gif" alt="Checked"/>';
            }
            $this->view->img = $img;
            $this->view->status = $status;
            $this->view->tab = $this->changeTabbar();
            $this->view->html = $this->view->render('index/status.phtml');
        } catch (Exception $e) {
            $this->view->errors = $e->getMessage();
        }
    }

    public function approveAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
        try {
            $postData = $this->getRequest()->getPost();
            $model = new Content_Model_ContentMapper();
            $update = $model->updateApproveStatus($postData['content_id']);
            $status = ($update == "Y") ? "Approved" : "DisApproved";
            if ($status == "DisApproved") {
                $img = '<img src="/themes/admin/default/images/others/cross-on-white.gif" alt="unchecked"/>';
            } else {
                $img = '<img src="/themes/admin/default/images/others/tick-on-white.gif" alt="Checked"/>';
            }
            $this->view->img = $img;
            $this->view->status = $status;
            $this->view->tab = $this->changeTabbar();
            $this->view->html = $this->view->render('index/approve.phtml');
        } catch (Exception $e) {
            $this->view->errors = $e->getMessage();
        }
    }

    public function changeTabbar()
    {
        return '';
        $tabs = '';
        ob_start();
        include(APPLICATION_PATH . '/layouts/partials/drupal/tabsPrimary.phtml');
        $tabs = ob_get_contents();
        ob_end_clean();
        return $tabs;
    }

    /**
     * Add Action for content
     * @todo: form validation
     */
    public function addAction()
    {
        $this->view->placeholder('title')->set('Create Content');
        $eleCatModel = new Package_Model_ElementCategory();
        $elementHierarchy = $eleCatModel->getElementHierarchy('', '', 'H');
        $this->view->elementHierarchy = $elementHierarchy;
        $type = $this->_getParam('type');
        $this->view->leafType = $type ? $type : 'N';
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        } else {
            $items = $this->tree->getHierarchyTree();
            $attributes = array(
                'id' => 'browser',
                'class' => 'filetree treeview'
            );
            $treeHtml = $this->tree->themeHierarchyItemList($items, 'Hierarchy Tree', 'ol', $attributes);
            $this->view->treeHtml = $treeHtml;
        }
        $activeId = $this->getRequest()->getParam('active_id');
        if ($activeId) {
            $this->browseUrl($this->getRequest()->getParam('active_id'));
        } else {
            $this->browseUrl('');
        }

        $request = $this->getRequest();
        $form = new Content_Form_ContentForm();
        // $form->setAction($this->view->baseUrl() . '/content/index/add');
        $this->view->setAction = $this->view->baseUrl() . '/content/index/add';
        // Check to see if this action has been POST'ed to.
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('submit')) {
            try {
                $contentId = $this->_saveAction('insert');
                if ($contentId) {
                    $this->viewAction($contentId);
                    $this->createUploadDir($contentId, 'add');
                }
            } catch (Zend_Db_Exception $e) {
                $this->view->error .= $e->getMessage();
            } catch (NepalAdvisor_Exception $e) {
                $this->view->msg .= $e->getMessage();
            } catch (Exception $e) {
                $this->view->error .= $e->getMessage();
            }
            //var_dump($this->view->error ,$this->view->msg);exit;
        } else {
            $this->view->setDefaultTag = true;
            $this->view->form = $form;
            $this->view->html = $this->view->render('index/add.phtml');
        }

        $this->view->tab = $this->changeTabbar();
        $this->view->action = $this->getRequest()->getActionName();
    }

    /**
     * @todo: form validation
     */
    public function editAction()
    {
        $this->view->placeholder('title')->set('Edit Content');
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        } else {
            $items = $this->tree->getHierarchyTree();
            $attributes = array(
                'id' => 'browser',
                'class' => 'filetree treeview'
            );
            $treeHtml = $this->tree->themeHierarchyItemList($items, 'Hierarchy Tree', 'ul', $attributes);
            $this->view->treeHtml = $treeHtml;
        }

        $form = new Content_Form_ContentForm();
        $this->view->setAction = $this->view->baseUrl() . '/content/index/edit';
        // Check to see if this action has been POST'ed to.
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('submit')) {
            try {
                $contentId = $this->_saveAction('update');
                if ($contentId) {
                    $oldFolderName = $this->getRequest()->getPost('oldHeading');
                    $this->viewAction($contentId);
                    $this->createUploadDir($contentId, 'edit', $oldFolderName);
                }
            } catch (Zend_Db_Exception $e) {
                $this->view->error = $e->getMessage();
            } catch (NepalAdvisor_Exception $e) {
                $this->view->msg = $e->getMessage();
            } catch (Exception $e) {
                $this->view->error = $e->getMessage();
            }
        } else {
            $cid = (int) $this->_getParam('id', 0);
            if ($cid) {
                $this->browseUrl($cid);
                $contentMapper = new Content_Model_ContentMapper();
                $contentResultSet = $contentMapper->find($cid);
                if (!$contentResultSet) {
                    print 'Content Not Found.';
                    return;
                }
                $contentTagMapper = new Content_Model_NadContentTagMapMapper();
                $contentResult = $contentResultSet->toArray();
                $this->view->content_img_link = $contentResult['content_image_link'];
                $this->view->leafType = $contentResult['is_leaf'];
                $form->populate($contentResult);
                $contentTagResultSet = $contentTagMapper->find($cid);
                if ($contentTagResultSet) {
                    $this->view->tags = $contentTagResultSet->toArray();
                }
                $eleCatModel = new Package_Model_ElementCategory();
                // $this->view->elementHierarchy = $eleCatModel->getTagElementWise('', '', 'H');
                $elementHierarchy = $eleCatModel->getElementHierarchy('', '', 'H');
                $this->view->elementHierarchy = $elementHierarchy;
                $form->oldHeading->setValue(str_replace(' ', '', $contentResultSet['heading']));
                $this->view->default_ecid = $contentResult['element_category_id'];
                $this->view->form = $form;
                $this->view->html = $this->view->render('index/add.phtml');
            }
        }
        $this->view->form = $form;
        $this->view->action = $this->_getParam('action');
        $this->view->tab = $this->changeTabbar();

        $this->renderScript('index/add.phtml');
    }

    public function uploadAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        } else {
            $items = $this->tree->getHierarchyTree();
            $attributes = array(
                'id' => 'browser',
                'class' => 'filetree treeview'
            );
            $treeHtml = $this->tree->themeHierarchyItemList($items, 'Hierarchy Tree', 'ul', $attributes);
            $this->view->treeHtml = $treeHtml;
        }


        $id = (int) $this->_getParam('id', 0);
        $this->view->id = $id;

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('is_video')) {
                // Save the data to db
                $post = $this->getRequest()->getPost();
                foreach ($post['embed_code'] as $key => $ebc) {
                    $contentFileMapper = new Content_Model_NadContentFileMapper();
                    $media = array();
                    $ebc = trim($ebc);
                    $media['embed_code'] = $ebc;
                    if ($post['content_file_id'][$key]) {
                        // Update the data                      
                        $content_file_id = $post['content_file_id'][$key];
                        /*
                         * Delete the record if embed code is empty
                         * Otherwise update
                         */
                        if (!$ebc) {
                            $contentFileMapper->getDbTable()->delete("content_file_id={$content_file_id}");
                        } else {
                            $contentFileMapper->getDbTable()->update($media, "content_file_id={$content_file_id}");
                        }
                    } else {
                        if ($ebc) {
                            $media['content_id'] = $id;
                            $media['content_file_type_id'] = 2;
                            $media['status'] = 'E';
                            $media['entered_dt'] = date('Y-m-d');
                            $media['approved'] = 'Y';
                            $insertId = $contentFileMapper->getDbTable()->insert($media);
                        }
                    }
                }
            }
        }

        $contentFileMapper = new Content_Model_NadContentFileMapper();
        $vMedia = $contentFileMapper->getContentVideo($id);
        $this->view->vMedia = $vMedia;

        $contentMapperModel = new Content_Model_ContentMapper();
        $heading = $contentMapperModel->getContentName($id);
        $this->view->heading = $heading;
        $model = new Content_Model_UploadHandler(array('content_id' => $id, 'heading' => $heading));
        $uploadPath = $model->getFullUrl();
        $this->view->uploadPath = $uploadPath;
    }

    public function renderAction()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $postData = $this->getRequest()->getPost();
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id = $this->_getParam('id');
            $postData = array('content_id' => $id);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $id = $_REQUEST['url_id'];
            $postData = array('content_id' => $id);
        }
        $upload_handler = new Content_Model_UploadHandler($postData);
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-Disposition: inline; filename="files.json"');
        header('X-Content-Type-Options: nosniff');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                break;
            case 'HEAD':
            case 'GET':
                $content = $upload_handler->get();
                break;
            case 'POST':
                if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
                    $upload_handler->delete();
                } else {
                    $data = $upload_handler->post();
                }
                break;
            case 'DELETE':
                $upload_handler->delete();
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
    }

    public function watermarkAction()
    {
        $id = $this->_getParam('id');
        $model = new Content_Model_NadContentFileMapper();
        if ($id) {
            $list = $model->find($id);
            $path = PUBLIC_PATH . $list->file_path . "thumbnails" . DS . "700x400" . DS . "images" . DS;
            if ($list->copyright == 'Y') {
                unlink($path . $list->file_name);
                $image = new Zend_Image(PUBLIC_PATH . $list->file_path . "images" . DS . $list->file_name, new Zend_Image_Driver_Gd());
                $transform = new Zend_Image_Transform($image, new Zend_Image_Driver_Gd);
                $transform->resizeResample(700, 333)
                        ->save($path . $list->file_name);
                $this->view->msg = $model->changeCopyRight($id, 'N');
            } else {
                $this->_helper->waterMark($list->file_name, $path);
                $this->view->msg = $model->changeCopyRight($id, 'Y');
            }
        }
        $ntchkId = $this->_getParam('ntchkId');
        if (is_array($ntchkId)) {
            foreach ($ntchkId as $id) {
                $list = $model->find($id);
                $path = PUBLIC_PATH . $list->file_path . "thumbnails" . DS . "700x400" . DS . "images" . DS;
                unlink($path . $list->file_name);
                $image = new Zend_Image(PUBLIC_PATH . $list->file_path . "images" . DS . $list->file_name, new Zend_Image_Driver_Gd());
                $transform = new Zend_Image_Transform($image, new Zend_Image_Driver_Gd);
                $transform->resizeResample(700, 333)
                        ->save($path . $list->file_name);
                $this->view->msg = $model->changeCopyRight($id, 'N');
            }
        }
        $chkId = $this->_getParam('chkId');
        if (is_array($chkId)) {
            foreach ($chkId as $id) {
                $list = $model->find($id);
                $path = PUBLIC_PATH . $list->file_path . "thumbnails" . DS . "700x400" . DS . "images" . DS;
                $this->_helper->waterMark($list->file_name, $path);
                $this->view->msg = $model->changeCopyRight($id, 'Y');
            }
        }
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
        try {
            $contentId = $this->getRequest()->getParam('content_id');
            if ($contentId == '') {
                $contentId = $this->getRequest()->getPost('id');
            }
            $contentMapper = new Content_Model_ContentMapper;
            $content = $contentMapper->find($contentId);
            $this->view->content = $content->toArray();
            $this->view->content_id = $contentId;
            if (!$contentId) {
                throw new Exception('Invalid ID');
            }

            $attrib = $this->getRequest()->getPost('del');
            $id = $this->getRequest()->getPost('id');
            if ($attrib == "Yes") {
                // Delete the nad_content_map data
                //  $contentMapModel = new Content_Model_ContentMap(array('content_id' => $id));
                // $contentMapMapper = new Content_Model_ContentMapMapper();
                // $contentMapMapper->delete($contentMapModel);

                $contentModel = new Content_Model_Content(array('content_id' => $id));
                $contentMapper = new Content_Model_ContentMapper();
                // Fetch the content data
                $contentToDelete = $contentMapper->find($id);
                $deletedContent = $contentToDelete->toArray();
                $contentMapper->delete($contentModel);
                $this->view->msg = sprintf("Content \"<em>%s</em>\" has been successfully deleted", $deletedContent['heading']);
                $this->view->html = $this->view->render('index/view.phtml');
            } elseif ($attrib == "No") {
                $this->view->html = $this->view->render('index/view.phtml');
            } else {
                $this->view->html = $this->view->render('index/delete.phtml');
                $this->view->msg = "Delete Page";
            }

            $items = $this->tree->getHierarchyTree(1, 0);
            $attributes = array(
                'id' => 'browser',
                'class' => 'filetree treeview'
            );
            $this->view->tree = $this->tree->themeHierarchyItemList($items, 'Hierarchy Tree', 'ul', $attributes);
            //$this->view->tab = $this->changeTabbar();
        } catch (Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }

    protected function _getForm($form, $action = null)
    {
        $form = new $form();
        if (null !== $action) {
            $form->setAction($this->view->baseUrl() . $action);
        }
        return $form;
    }

    protected function _saveAction($action)
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }

        /**
         * Check the action(insert/update) to perform during save.
         * Determine the $parentId of the term accordingly
         */
        $parentId = 0;
        $msgKeyword = '';
        switch ($action) {
            case 'insert' :
                $parentId = (int) $this->getRequest()->getPost('active_id');
                $msgKeyword = 'inserted';
                break;
            case 'update' :
                $parentId = (int) $this->getRequest()->getPost('parent_id');
                $msgKeyword = 'updated';
                break;
            default:
                throw new Exception('Invalid Action!!!');
        }

        $form = new Content_Form_ContentForm();
        $form->getElement('content_image_link')->setRequired(false)->clearValidators();
        $form->getElement('one_para_desc')->setRequired(false)->clearValidators();
        $form->getElement('one_line_desc')->setRequired(false)->clearValidators();
        $form->getElement('two_line_desc')->setRequired(false)->clearValidators();
        $form->getElement('three_line_desc')->setRequired(false)->clearValidators();
        $form->getElement('short_desc')->setRequired(false)->clearValidators();
        $form->getElement('desc_tag')->setRequired(false)->clearValidators();
        $form->getElement('title_tag')->setRequired(false)->clearValidators();
        $form->getElement('keyword')->setRequired(false)->clearValidators();
        // Check to see if this action has been POST'ed to.
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('submit')) {
            if ($form->isValid($this->getRequest()->getPost())) {
                try {
                    $ecid = $this->getRequest()->getPost('default_ecid');

                    /**
                     * Insert in element category and map the newly create element category in content tag map as default; 
                     */
                    $setDefaultTag = $this->getRequest()->getPost('set_default_tag');
                    if ($setDefaultTag) {
                        if ($setDefaultTag == 'Y') {
                            $rootParent = $this->getRequest()->getPost('root_parent');
                            $elementCategoryData = array();
                            if (array_key_exists($rootParent, $this->_rootContentElements)) {
                                $elementCategoryData['element_id'] = $this->_rootContentElements[$rootParent]['id'] ? $this->_rootContentElements[$rootParent]['id'] : NULL;
                            }
                            $eleCatModel = new Package_Model_ElementCategory();
                            $title = trim($this->getRequest()->getPost('heading'));
                            $ecData = $eleCatModel->checkName($title);
                            if (!$ecData && !$ecid) {
                                // Insert the default title element category tag
                                $elementCategoryData += array(
                                    'parent_id' => null,
                                    'type' => 'T',
                                    'name' => strtolower($title),
                                    'short_name' => ucwords($title),
                                    'weight' => 0,
                                    'entered_by' => Zend_Auth::getInstance()->getIdentity()->username,
                                    'status' => 'E',
                                    'entered_dt' => date('Y-m-d'),
                                );
                                $ecid = $eleCatModel->add($elementCategoryData);
                                if (! $ecid) {
                                    throw new Exception("Default tag could not created.");
                                }
                            } else {
                                // Update the default title element category tag
                                $ecid = isset($ecData->element_category_id) && $ecData->element_category_id ? $ecData->element_category_id : $ecid;
                                $elementCategoryData += array(
                                    'name' => strtolower($title),
                                    'short_name' => ucwords($title)
                                );

                                $eleCatModel->updateTagName($elementCategoryData, $ecid);
                            }
                        }
                    }
                    // Since we now know the form validated, we can now
                    // start integrating that data sumitted via the form
                    // into our model:
                    $content_image_link = explode(DIRECTORY_SEPARATOR, $this->getRequest()->getPost('content_image_link'));
                    //$file_name = trim(basename(stripslashes(end($content_image_link))), ".\x00..\x20");
                    $file_name = urldecode(end($content_image_link));
                    $contentData = array_merge($this->getRequest()->getPost(), array('entered_dt' => date('Y-m-d H:i:s'), 'file_path' => "/uploads/", 'file_name' => $file_name));
                    if ($this->getRequest()->getParam('root_parent')) {
                        $rpid = $this->getRequest()->getParam('root_parent');
                        $contentData += array('element_id' => $this->_rootContentElements[$rpid]['id']);
                    }

                    $contentModel = new Content_Model_Content($contentData);
                    $contentModel->setElementCategory_id($ecid);
                    $contentMapper = new Content_Model_ContentMapper();
                    $contentId = $contentMapper->$action($contentModel);

                    /**
                     * Insert into nad_content_map table
                     */
                    $contentMapMapper = new Content_Model_ContentMapMapper();
                    // Set the default level field in nad_content_map table to 1
                    $level = 1;
                    $rowResultSet = $contentMapMapper->find($parentId);
                    if ($rowResultSet) {
                        $row = $rowResultSet->toArray();
                        $level = ((int) $row['level']) + 1;
                    }

                    $contentMapData = array(
                        'content_id' => $contentId,
                        'parent_id' => $parentId,
                        'level' => $level
                    );
                    /**
                     * Map the default heading tag with the corresponding tag
                     * Insert the newly inserted element category id to map table 
                     * or update the existing element_category_id in map table
                     */
                    if ($ecid) {
                        $ctmData = array(
                            'content_id' => $contentId,
                            'element_category_id' => $ecid
                        );

                        $ctmModel = new Content_Model_NadContentTagMap($ctmData);
                        $ctmMapper = new Content_Model_NadContentTagMapMapper();
                        $isMapped = $hasDefaultTag = $ctmMapper->hasDefaultTag($contentId, $ecid);

                        if ('Y' == $setDefaultTag) {
                            $ctmData['default_tag'] = 'Y';
                            $ctmModel->setDefault_tag('Y');
                            if (! $hasDefaultTag) {
                                $ctmMapper->insert($ctmModel);
                            }
                            else {
                                $ctmMapper->update($ctmModel);
                            }
                        }
                        else {
                            if (! $hasDefaultTag) {
                                $ctmMapper->insert($ctmModel);
                            }
                            else {
                                $ctmMapper->update($ctmModel);
                            }
                        }
                    }
                    
                    /**
                     * Heading tags insert/update 
                     */
                    $contentMapModel = new Content_Model_ContentMap($contentMapData);
                    $contentMapId = $contentMapMapper->$action($contentMapModel);
                    if (array_key_exists('element_category_id', $contentData)) {
                        $contentTagMapData = array(
                            'content_id' => $contentId,
                            'element_category_id' => $contentData['element_category_id']
                        );
                        $contentTagMapModel = new Content_Model_NadContentTagMap($contentTagMapData);
                        $contentTagMapMapper = new Content_Model_NadContentTagMapMapper();
                        $contentTagResultSet = $contentTagMapMapper->find($contentId);
                        if ($action == "update") {
                            if ($contentTagResultSet) {
                                $contentTagMapMapper->delete($contentTagMapModel, 'H');
                            }
                        }
                        $contentTagMapMapper->insert($contentTagMapModel);
                    } elseif ($action == "update" && !array_key_exists('element_category_id', $contentData)) {
                        $contentTagMapData = array(
                            'content_id' => $contentId,
                        );
                        $contentTagMapModel = new Content_Model_NadContentTagMap($contentTagMapData);
                        $contentTagMapMapper = new Content_Model_NadContentTagMapMapper();
                        $contentTagMapMapper->delete($contentTagMapModel, 'H');
                    }

                    $this->view->msg = sprintf("Content \"<em>%s</em>\" has been %s successfully", $this->getRequest()->getPost('heading'), $msgKeyword);
                    
                    return $contentId;
                } catch (Zend_Db_Exception $e) {
                    throw new Zend_Db_Exception($e->getMessage());
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            } else {
                $errors = $form->getMessages();
                $this->view->error = $errors;
            }
        }
    }

    public function createUploadDir($id, $action, $oldFolderName = null)
    {
        $uploadPath = UPLOAD_PATH;
        if (!is_dir($uploadPath)) {
            $me = mkdir($uploadPath, 0777, true);
            if (!$me) {
                throw new NepalAdvisor_Exception("You don't have write permission.");
            }
        }
        /* $mapper = new Content_Model_ContentMapper();
          $upload = $mapper->getBreadCrumb($id);
          if ($action == 'add') {
          $path = implode('/', $upload);
          $uploadPath = UPLOAD_PATH . '/' . $path;
          if (!is_dir($uploadPath)) {
          $me = mkdir($uploadPath, 0777, true);
          if (!$me) {
          throw new NepalAdvisor_Exception("You don't have write permission.");
          }
          } else {
          throw new NepalAdvisor_Exception("Folder already exists");
          }
          } elseif ($action == 'edit') {
          $folderName = $upload[sizeof($upload) - 1];
          array_pop($upload);
          $path = implode('/', $upload);
          $uploadPath = UPLOAD_PATH . '/' . $path;
          if (is_dir($uploadPath . '/' . $oldFolderName)) {
          rename($uploadPath . '/' . $oldFolderName, $uploadPath . '/' . $folderName);
          } elseif (is_dir($uploadPath . '/' . $folderName)) {
          throw new NepalAdvisor_Exception('Folder already exists');
          } else {
          mkdir($uploadPath . '/' . $folderName, 0777, true);
          }
          } */
    }

    public function browseUrl($id = null)
    {
        $basepath = '';
        if (strstr($this->view->basePath(), 'public')) {
            $basepath = '/public';
        }
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "application.ini", 'production');
        $uploadUrl = sprintf("%s/uploads/", $basepath);
        Zend_Session::namespaceUnset('KCFINDER');
        $ns = new Zend_Session_Namespace('KCFINDER');
        $ns->uploadURL = $uploadUrl;
        $ns->uploadDir = "";
        $ns->dbInsertId = $id;
        $ns->controller = "content";
        $ns->host = $config->resources->db->params->host;
        $ns->dbname = $config->resources->db->params->dbname;
        $ns->username = $config->resources->db->params->username;
        $ns->password = $config->resources->db->params->password;
    }

    protected function _listContentTerms($vid = 1, $tid = 0)
    {
        static $items = array(), $sno = 1;
        $terms = $this->tree->getHierarchyTerms($vid, $tid, -1, 1);

        foreach ($terms as $i => $term) {
            $item_data = sprintf(
                    "%s \n" .
                    "<a class=\"tabledrag-handle\" href=\"#\" title=\"Drag to re-order\">" .
                    "<div class=\"handle\">&nbsp;</div>" .
                    "</a> \n" .
                    "<a href='%s/admin/content/view/%s'>%s</a>", str_repeat('<div class="indentation">&nbsp;</div>', ((int) $term->level) - 1), $this->view->baseUrl(), $term->tid, $term->heading);
            $term->heading = $item_data;
            foreach ($term as $key => $value) {
                if ($key != 'heading' && $key != 'tid') {
                    unset($term->$key);
                }
            }
            $items[] = (array) $term;
            //$item_data = $term->heading;
            $term_items = $this->_listContentTerms($vid, $term->tid);

            //$items[] = array("term" => $term, "data" => $item_data, "children" => $term_items, "expand" => FALSE);
        }
        return $items;
    }

    public function listAction()
    {
        $grid = Bvb_Grid::factory('Table');
        $data = $this->_listContentTerms();
        $source = new Bvb_Grid_Source_Array($data);
        $grid->setSource($source);

        $grid->updateColumn('heading', array(
            'decorator' => '{{heading}}',
            'escape' => true
        ));
        $grid->updateColumn('tid', array('hidden' => true));
        $grid->setRowAltClasses('odd draggable', 'even draggable');
        $grid->setRecordsPerPage(50);
        $grid->setPaginationInterval(array(
            5 => 5,
            20 => 20,
            50 => 50,
            100 => 100
        ));
        $this->view->grid = $grid->deploy();
        return;
    }

    public function searchAction()
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        if ($this->_getParam('_exportTo')) {
            $dir = $config->deploy->excel->dir;
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        $query_string = $this->getRequest()->getPost('search_key');
        $objModel = new Content_Model_ContentMapper();
        $data = $objModel->fetchSearchGridData($query_string);
        $grid = Bvb_Grid::factory('Table', $config, $id = '');
        $source = new Bvb_Grid_Source_Array($data);
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('date', array('render' => 'date'));
        $grid->addFilters($filters);
        $grid->setSource($source);
        //$grid->updateColumn('description',array('decorator'=>'{{description}}'));       

        $grid->setExport(array('print', 'word', 'csv', 'excel', 'pdf'));
        $grid->setImagesUrl('/grid/');
        $grid->updateColumn('date', array('title' => 'Entered Date'));
        $grid->updateColumn('description', array('hidden' => true));
        $grid->updateColumn('content_id', array('hidden' => true));
        $grid->updateColumn('content_length', array('hidden' => true));
        $viewColumn = new Bvb_Grid_Extra_Column();
        $viewColumn->setPosition('left')->setName('View')->setDecorator('<a href="/content/index/view/id/{{content_id}}"><img border="0" src="/grid/detail.png"></a>');
        $viewColumn->setPosition('right')->setName('Content_length')->setDecorator('{{content_length}}');
        $grid->addExtraColumns($viewColumn);
        $grid->setShowFiltersInExport(true);
        $grid->setRecordsPerPage(10);
        $grid->setPaginationInterval(array(
            5 => 5,
            20 => 20,
            50 => 50,
            100 => 100
        ));
        // $grid->updateColumn('date',array('searchType'=>'>'));
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('date', array('render' => 'date'));
        $grid->addFilters($filters);
        $this->view->grid = $grid->deploy();
        //$this->view->html = $this->view->render('index/search.phtml');


        /* $this->createIndexFile();
          if($this->_request->isPost())
          {
          $query_string = $this->getRequest()->getPost('keyword');
          $query_string = trim($query_string);
          if(strlen($query_string)<3)

          {
          echo "Please enter more characters for the SEARCH.";
          }

          $this->view->vwSearchStr = $query_string;
          $path = APPLICATION_PATH."/docs/lucene";
          $index = Zend_Search_Lucene::open($path);
          $results = $index->find($query_string.'*');
          $this->view->results = $results;
          $this->view->html = $this->view->render('index/search.phtml');
          } */
    }

    public function createIndexFile()
    {
        $data = "";
        $objModel = new Content_Model_ContentMapper();
        $results = $objModel->fetchSearchGridData();
        $path = APPLICATION_PATH . "/docs/lucene";
        $index = Zend_Search_Lucene::create($path);
        $doc = new Zend_Search_Lucene_Document();
        foreach ($results as $data) {
            $doc->addField(Zend_Search_Lucene_Field::text('heading', $data['heading']));
            $doc->addField(Zend_Search_Lucene_Field::text('keyword', $data['keyword']));
            $doc->addField(Zend_Search_Lucene_Field::text('desc_tag', $data['desc_tag']));
            $doc->addField(Zend_Search_Lucene_Field::text('title_tag', $data['title_tag']));
            $doc->addField(Zend_Search_Lucene_Field::text('description', $data['description']));
            $doc->addField(Zend_Search_Lucene_Field::unIndexed('content_id', $data['content_id']));
            $doc->addField(Zend_Search_Lucene_Field::text('one_para_desc', $data['one_para_desc']));
            $doc->addField(Zend_Search_Lucene_Field::text('three_line_desc', $data['three_line_desc']));
            $index->addDocument($doc);
        }
        $index->commit();
        $index->optimize();
    }

    public function validateformAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $f = new Content_Form_ContentForm();
        $f->isValid($this->_getAllParams());
        $id = $this->_getParam('id');
        $json = $f->getMessages();
        $this->view->errors = $json[$id];
    }

    public function tagAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        } else {
            $items = $this->tree->getHierarchyTree();
            $attributes = array(
                'id' => 'browser',
                'class' => 'filetree treeview'
            );
            $treeHtml = $this->tree->themeHierarchyItemList($items, 'Hierarchy Tree', 'ul', $attributes);
            $this->view->treeHtml = $treeHtml;
        }

        $id = (int) $this->_getParam('id', 0);
        $this->view->id = $id;
        $contentMapperModel = new Content_Model_ContentMapper();
        $title = $contentMapperModel->getContentName($id);
        $this->view->title = $title;
        $eleCatModel = new Package_Model_ElementCategory();
        $this->view->elementHierarchy = $eleCatModel->getElementHierarchy('', '', 'T');
//        $this->view->elementHierarchy = $eleCatModel->getElementTags(null, 'T');
        $contentTagMapper = new Content_Model_NadContentTagMapMapper();
        $contentTagResultSet = $contentTagMapper->getTagsByContentId($id);
        if ($contentTagResultSet) {
            $this->view->tags = $contentTagResultSet;
        }
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('submit')) {
            $formData = $this->getRequest()->getPost();
            $type = $this->getRequest()->getPost('type') ? $this->getRequest()->getPost('type') : 'H';
            if (isset($formData['element_category_id'])) {
                if (is_array($formData['element_category_id'])) {
                    try {
                        $contentTagMapData = array(
                            'content_id' => $formData['active_id'],
                            'element_category_id' => $formData['element_category_id']
                        );
                        $contentTagMapModel = new Content_Model_NadContentTagMap($contentTagMapData);
                        $contentTagMapMapper = new Content_Model_NadContentTagMapMapper();
                        $contentTagResultSet = $contentTagMapMapper->find($formData['active_id']);
                        if ($contentTagResultSet) {
                            $contentTagMapMapper->delete($contentTagMapModel, $type);
                        }
                        $contentTagMapMapper->insert($contentTagMapModel);
                        $this->view->msg = sprintf("Content \"<em>Tags</em>\" has been successfully inserted");
                    } catch (Zend_Db_Exception $e) {
                        throw new Zend_Db_Exception($e->getMessage());
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                }
            } else {
                try {
                    $contentTagMapData = array(
                        'content_id' => $formData['active_id']
                    );
                    $contentTagMapModel = new Content_Model_NadContentTagMap($contentTagMapData);
                    $contentTagMapMapper = new Content_Model_NadContentTagMapMapper();
                    $contentTagResultSet = $contentTagMapMapper->find($formData['active_id']);
                    if ($contentTagResultSet) {
                        $contentTagMapMapper->delete($contentTagMapModel, $type);
                    }
                    $this->view->msg = sprintf("Content \"<em>Tags</em>\" has been successfully updated");
                } catch (Zend_Db_Exception $e) {
                    throw new Zend_Db_Exception($e->getMessage());
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
        }
        $this->view->html = $this->view->render('index/tag.phtml');
    }

    public function checkUniqueNameAction()
    {
        $heading = $this->_getParam('heading');
        $contentModel = new Content_Model_ContentMapper();
        if ($contentModel->checkName($heading)) {
            $this->view->check = true;
            $this->view->msg = "Name $heading already exists";
        } else {
            $this->view->check = false;
        }
    }

    public function sortAction()
    {
        $id = $this->_getParam('id');
        $this->view->id = $id;
        $eleCatModel = new Package_Model_ElementCategory();
        if ($id) {
            $this->view->element = $eleCatModel->getTaggedContentElementWise($id, '');
        } else {
            $this->view->element = $eleCatModel->getTaggedContentElementWise('', '');
        }
        $elementModel = new Package_Model_Element();
        $elements = $elementModel->fetchAll();
        $this->view->elements = $elements;
    }
    
    public function unlinkImageAction()
    {
        /**
         * Get all the image from nad_package_media 
         */
        $mediaModel = new Content_Model_NadContentFileMapper();
        $rs = $mediaModel->getDbTable()->fetchAll();
        $dbImages = array();
        foreach($rs as $media) {
            $dbImages[] = $media['file_name'];
        }
        
        $dir = APPLICATION_PATH . '/../public/uploads/thumbnails/40x40/images';
        $dirFiles = scandir($dir);
        unset($dirFiles[0]);
        unset($dirFiles[1]);
        
        $unlinkImages = array_diff($dirFiles, $dbImages);
        print"<pre>";
        var_dump($unlinkImages);
        foreach($unlinkImages as $filename) {
            unlink($dir.'/'.$filename);
        }
        
        exit;
    }

}
