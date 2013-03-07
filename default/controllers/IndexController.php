<?php

class Default_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->_helper->block->add('footerouterwrapper');
        $this->_helper->block->add('floatingnav');
        $this->_helper->block->add('loggedin');
        $this->_helper->block->add('banner');
        $this->_helper->block->add('yourpreferences');
        $this->_helper->block->add('subfooter');
        $this->_helper->block->add('topsellingholidayslider');
        $this->_helper->layout->setLayout("home");
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function inspirationAction()
    {
       // $this->_helper->layout()->disableLayout();
        $packageModel = new Package_Model_Mapper_NadPackageMst();
        $this->view->relatedPackage = $packageModel->getRelatedPackage('','1');  
    }

}

