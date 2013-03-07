<?php

class Menu_PermissionController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('role', 'json')->initContext();
    }

    public function indexAction()
    {
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }

        try {
            //usertypes 
            $usertype = new Admin_Model_DbTable_Usertypes();
            $this->view->usertypes = $usertype->fetchAll();
            //permission action
            $permissiontype = new Menu_Model_PermissionSecurity();
            $this->view->permissions = $permissiontype->fetchAll();
            //Permission to individual menu 
            $menupermission = new Menu_Model_Permission();
            if (isset($_POST['menu_id'])) {
                $menu_id = $_POST['menu_id'];
                $this->view->menupermission = $menupermission->fetchAll($menu_id);
                $this->view->menu_id = $menu_id;
                if ($this->getRequest()->isPost() && $this->getRequest()->getPost('permitted')) {
                    $permData = $this->getRequest()->getPost();
                    $usertype_id = $permData['usertype_id'];
                    $perm_id = $permData['perm_id'];
                    //to get children call children function
                    $childrens = $this->children($menu_id);
                    $permission = new Menu_Model_Permission();
                    $ispermitted = $permData['permitted'];
                    if ('true' == $ispermitted) {
                        $hasPermission = $permission->deleteMenuPermission($usertype_id, $perm_id, $childrens);
                        $permittedClass = "notpermitted";
                    } elseif ('false' == $ispermitted) {
                        $hasPermission = $permission->addMenuPermission($usertype_id, $perm_id, $childrens);
                        $permittedClass = "permitted";
                    }
                    $this->view->hasPermission = $hasPermission;
                    $this->view->permittedClass = $permittedClass;
                }
            }
            $this->view->tab = $this->changeTabbar();
            $this->view->html = $this->view->render('permission/role.phtml');
        } catch (Exception $e) {
            $this->view->errors = $e->getMessage();
        }
    }

    public function roleAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }

        try {
            //usertypes 
            $usertype = new Admin_Model_DbTable_Usertypes();
            $this->view->usertypes = $usertype->fetchAll();
            //permission action
            $permissiontype = new Menu_Model_PermissionSecurity();
            $this->view->permissions = $permissiontype->fetchAll();
            //Permission to individual menu 
            $menupermission = new Menu_Model_Permission();
            if (isset($_POST['menu_id'])) {
                $menu_id = $_POST['menu_id'];
                $this->view->menupermission = $menupermission->fetchAll($menu_id);
                $this->view->menu_id = $menu_id;
                if ($this->getRequest()->isPost() && $this->getRequest()->getPost('permitted')) {
                    $permData = $this->getRequest()->getPost();
                    $usertype_id = $permData['usertype_id'];
                    //to get children call children function
                    $childrens = $this->children($menu_id);
                    $permission = new Menu_Model_Permission();
                    $ispermitted = $permData['permitted'];
                    if ('true' == $ispermitted) {
                        $hasPermission = $permission->deleteMenuPermission($usertype_id, $childrens);
                        $permittedClass = "notpermitted";
                    } elseif ('false' == $ispermitted) {
                        $hasPermission = $permission->addMenuPermission($usertype_id, $childrens);
                        $permittedClass = "permitted";
                    }
                    $this->view->hasPermission = $hasPermission;
                    $this->view->permittedClass = $permittedClass;
                }
            }
            $this->view->tab = $this->changeTabbar();
            $this->view->html = $this->view->render('permission/role.phtml');
        } catch (Exception $e) {
            $this->view->errors = $e->getMessage();
        }
    }

    public function children($menu_id)
    {
        $menumap = new Menu_Model_MenuMap();
        $childrens = array();
        $childrens = $menumap->getChildrens($menu_id);
        array_push($childrens, $menu_id);
        return $childrens;
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

}
