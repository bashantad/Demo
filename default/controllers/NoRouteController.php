<?php

class Default_NoRouteController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_helper->layout->setLayout("404");
    }
}

