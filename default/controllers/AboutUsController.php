<?php

class Default_AboutUsController extends Zend_Controller_Action
{

    private $_cid = 274;

    public function init()
    {
        $this->_helper->block->add('floatingnav');
        $this->_helper->block->add('loggedin');
        $this->_helper->block->add('attractions');
      //  $this->_helper->block->add('topsellingholidayslider');
    }

    public function indexAction()
    {
        //throw new Exception("Customized message");
        $this->_helper->block->add('suggestedholidays');
        $this->_helper->block->add('peoplesay');
        //$this->_helper->block->add('videostories');
       // $this->_helper->block->add('peoplesay');
        $this->_helper->layout->setLayout("two_column_layout");
    }
}

