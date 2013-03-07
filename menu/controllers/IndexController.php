<?php

class Menu_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $module = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();

        $this->view->path = "/{$module}/{$controller}";
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('view', 'json')
                ->addActionContext('add', 'json')
                ->addActionContext('edit', 'json')
                ->addActionContext('delete', 'json')
                ->initContext();

        $this->view->placeholder('title')->set('Menu');

        $options = array(
            'mapper' => new Menu_Model_Menu,
            'label' => 'heading',
            'method' => 'fetchHierarchy',
            'search' => false
        );
        // Generate tree of the corresponding content_id
        $this->tree = new NepalAdvisor_Tree_Controller_Plugin_Tree($options);
    }

    public function indexAction()
    {
        $items = $this->tree->getHierarchyTree(1, 0);
        $attributes = array(
            'id' => 'browser',
            'class' => 'filetree treeview'
        );
        $treeHtml = $this->tree->themeHierarchyItemList($items, 'Menu Tree', 'ul', $attributes);
        $this->view->treeHtml = $treeHtml;
        
    }

    public function viewAction($menu_id = 0)
    {
        try {
            if ($this->getRequest()->isXmlHttpRequest()) {
                $this->_helper->layout->disableLayout();
            }

            $pCid = (int) $this->getRequest()->getParam('active_id');
            $pCid = $pCid ? $pCid : (int) $this->getRequest()->getParam('id');
            $menu_id = $menu_id ? $menu_id : $pCid;

            if (!$menu_id) {
                throw new Zend_Exception('Invalid Request.');
            }
            $menuModel = new Menu_Model_Menu();
            $menu = $menuModel->getDetailById($menu_id);
            
            if (null === $menu) {
                throw new Zend_Exception('Menu could not be found.');
            }

            if ($menu) {
                $items = $this->tree->getHierarchyTree();
                $attributes = array(
                    'id' => 'browser',
                    'class' => 'filetree treeview'
                );

                $treeHtml = $this->tree->themeHierarchyItemList($items, NULL, 'ul', $attributes);
                $this->view->menu = $menu;
                $this->view->term_id = $menu['menu_id'];
                $this->view->tree = $treeHtml;
                $this->view->tab = $this->changeTabbar();
                $this->view->html = $this->view->render('index/view.ajax.phtml');
            }
        } catch (Zend_Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }

    public function addAction()
    {
        $this->view->placeholder('title')->set('Create Menu');
        try {
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
            $request = $this->getRequest();
            $form = new Menu_Form_MenuForm();
            $form->setAction($this->view->baseUrl() . '/menu/index/add');

            $parent_id = (int) $this->getRequest()->getParam('active_id');
            if ($this->getRequest()->isPost() && $this->getRequest()->getPost('submit')) {
                if ($form->isValid($request->getPost())) {
                    $menu = new Menu_Model_Menu();
                    $menu_id = $menu->addMenu($form->getValues());
                    $menumap = new Menu_Model_MenuMap();
                    $menurow = $menumap->find($parent_id);
                    $level = 1;
                    if ($menurow) {
                        $level = ((int) $menurow['level']) + 1;
                    }
                    $mapData = array(
                        'parent_id' => $parent_id,
                        'menu_id' => $menu_id,
                        'level' => $level,
                    );
                    $menu_id = $menumap->addMenuMap($mapData);

                    $this->viewAction($menu_id);
                }
                else {
                    $this->view->error = $form->getMessages();
                }
            } else {
                $this->view->action = $this->getRequest()->getActionName();
                $this->view->form = $form;      
                $this->view->html = $this->view->render('index/add.phtml');
            }
            
            $this->view->tab = $this->changeTabbar();
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    public function changeTabbar()
    {

        $tabs = '';
        ob_start();
        include(APPLICATION_PATH . '/layouts/admin/_tabsPrimary.phtml');
        $tabs = ob_get_contents();
        ob_end_clean();
        return $tabs;
    }

    public function editAction()
    {
        $this->view->placeholder('title')->set('Edit Menu');
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

        $request = $this->getRequest();
        $form = new Menu_Form_MenuForm();
        $form->setAction($this->view->baseUrl() . '/menu/index/edit');
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('submit')) {
            if ($form->isValid($request->getPost())) {
                if (isset($_POST['menu_id'])) {
                    $menu = new Menu_Model_Menu();
                    $menu->updateMenu($form->getValues(), $_POST['menu_id']);
                    $this->viewAction($_POST['menu_id']);
                }
            }
            else {
                $this->view->error = $form->geMessages();
            }
            //return $this->_helper->redirector('index');
        } else {
            $id = $this->_getParam('id', 0);
            $this->view->id = $id;
            if ($id > 0) {
                $menu = new Menu_Model_Menu();
                $formData = $menu->getDetailById($id);
                $form->populate($formData);
            }
            
            $this->view->form = $form;
            $this->view->html = $this->view->render('index/add.phtml');
            $this->renderScript('index/add.phtml');
        }
//        $this->view->form = $form;
//        $this->view->tab = $this->changeTabbar();
//         $this->view->html = $this->view->render('index/edit.phtml');
//         

        
        $this->view->tab = $this->changeTabbar();       
        
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
        try {
            $menu_id = $this->_getParam('content_id', 0);
            if ($menu_id > 0) {
                //delete all permissions of that menu_id
                $menumap = new Menu_Model_MenuMap();
                $childrens = $menumap->getChildrens($menu_id);
                array_push($childrens, $menu_id);
                $permission = new Menu_Model_Permission();
                $permission->deleteAllChildren($childrens);
                //delets all menu_map info
                $menumap->DeleteMenuMap($childrens);
                //deletes that menu and it's childrens..
                $menu = new Menu_Model_Menu();
                $deletedMenu = $menu->getDetailById($menu_id);
                $menu->deleteMenu($childrens);
                $this->view->msg = sprintf("Menu \"<em>%s</em>\" has been successfully deleted", $deletedMenu['label']);

                $items = $this->tree->getHierarchyTree(1, 0);
                $attributes = array(
                    'id' => 'browser',
                    'class' => 'filetree treeview'
                );
                $treeHtml = $this->tree->themeHierarchyItemList($items, null, 'ul', $attributes);
                $this->view->tree = $treeHtml;
                $this->view->tab = $this->changeTabbar();
            }
        } catch (Exception $e) {
            $this->view->errors = $e->getMessage();
        }
    }

    public function childrenAction($menu_id=2)
    {
        $menumap = new Menu_Model_MenuMap();
        $childrens = $menumap->getChildrens($menu_id);
        array_push($childrens, $menu_id);
        $this->view->childrens = $childrens;
    }

    protected function _listMenuTerms($vid=1, $tid=0)
    {
        $items = array();
        $terms = $this->tree->getHierarchyTerms($vid, $tid, -1, 1);
        foreach ($terms as $i => $term) {
            if (!$term->tid && !$term->parent) {
                continue;
            }

            $item_data = sprintf("<td>" .
                    "%s \n" .
                    "<a class=\"tabledrag-handle\" href=\"#\" title=\"Drag to re-order\">" .
                    "<div class=\"handle\">&nbsp;</div>" .
                    "</a> \n" .
                    "<a href='%s/admin/content/view/%s'>%s</a>" .
                    "</td>", str_repeat('<div class="indentation">&nbsp;</div>', ((int) $term->level) - 1), $this->view->baseUrl(), $term->tid, $term->heading);
            $item_data .= sprintf("<td>%s</td>", $term->status ? 'enabled' : 'disabled');
            $item_data .= sprintf("<td><a href='%s/admin/build/menu/edit/%d'>edit</a></td>", $this->view->baseUrl(), $term->tid);
            $term_items = $this->_listMenuTerms($vid, $term->tid);
            $items[] = array("term" => $term, "data" => $item_data, "children" => $term_items, "expand" => FALSE);
        }
        return $items;
    }

    public function listAction()
    {
        $items = $this->_listMenuTerms();
        $contentRowHtml = $this->tree->themeHierarchyItemTr($items, NULL, '', array('class' => 'draggable'));
        $this->view->tableBodyRow = $contentRowHtml;
    }

    protected function _listNavigationTerms($vid=1, $tid=0)
    {
        $items = array();
        $terms = $this->tree->getHierarchyTerms($vid, $tid, -1, 1);
        foreach ($terms as $i => $term) {
            if (!$term->tid && !$term->parent) {
                continue;
            }
            $term_items = $this->_listNavigationTerms($vid, $term->tid);
            //$items[] = array("term" => $term, "data" => $item_data, "children" => $term_items, "expand" => FALSE);
            foreach ($term as $key => $value) {
                if ($key != 'label') {
                    unset($term->$key);
                }
            }
            $data = (array) $term;
            //$data['uri'] = '/';
            $data['module'] = 'content';
            $data['controller'] = 'index';
            $data['action'] = 'index';
            if (!empty($term_items)) {
                $data['pages'] = $term_items;
            }
            $items[] = array($data);
        }
        return $items;
    }

    public function listnavigationAction()
    {
        $items = $this->_listNavigationTerms();

        $items = array(
            array(
                'label' => 'test',
                'module' => 'content',
                'controller' => 'index',
                'action' => 'index',
            ),
            array(
                'label' => 'testa',
                'module' => 'content',
                'controller' => 'index',
                'action' => 'index',
            )
        );

        $container = new Zend_Navigation();
        $this->view->navigation($container);
        $container->addPage($items);
        print $this->view->navigation()->menu();
        exit;
    }
    
    public function statusAction() {
        $menuId = $this->getRequest()->getParam('mid');
        $menuModel = new Menu_Model_Menu();
        $menu = $menuModel->getDetailById($menuId);
        
        $menuMap = new Menu_Model_MenuMap();
        $children = $menuMap->getChildrens($menuId);
//        $profiler = Zend_Db_Table_Abstract::getDefaultAdapter()->getProfiler()->setEnabled(true);
        $flag = ($menu['status'] == 'E') ? 'D' : 'E';
        
        $where_in = implode(', ', (array_merge(array($menuId), $children)));        
        $success = $menuModel->getDbTable()->update(array('status' => $flag), "menu_id IN ({$where_in})");
//        var_dump($profiler->getQueryProfiles());
        
        if ($success) 
        {
            print json_encode(array(
                'title' => 'Click the icon to ' . ($flag == 'E' ? 'disable' : 'enable'),
                'cls' => ($flag == 'E' ? 'stat-en' : 'stat-dis')
            ));
        }
        exit;
    }
    
    public function visibilityAction() {
        $menuId = $this->getRequest()->getParam('mid');
        $menuModel = new Menu_Model_Menu();
        $menu = $menuModel->getDetailById($menuId);
        $menuMap = new Menu_Model_MenuMap();
        $children = $menuMap->getChildrens($menuId);
        $profiler = Zend_Db_Table_Abstract::getDefaultAdapter()->getProfiler()->setEnabled(true);
        $flag = ($menu['visible'] == 'Y') ? 'N' : 'Y';
//        var_dump($menu['visible']);
        $where_in = implode(', ', (array_merge(array($menuId), $children)));        
        $success = $menuModel->getDbTable()->update(array('visible' => $flag), "menu_id IN ({$where_in})");
//        var_dump($profiler->getQueryProfiles());
        if ($success) 
        {
            print json_encode(array(
                'title' => 'Click the icon to make ' . ($flag == 'Y' ? 'invisible' : 'visible'),
                'cls' => ($flag == 'Y' ? 'stat-en' : 'stat-dis')
            ));
        }
        exit;
    }

}
