<?php

class Default_TermsAndConditionsController extends Zend_Controller_Action
{

    private $_cid = 274;

    public function init()
    {
        $this->_helper->block->add('floatingnav');
        $this->_helper->block->add('loggedin');
        $this->_helper->block->add('attractions');
        $this->_helper->block->add('topsellingholidayslider');
    }

    public function indexAction()
    {
        $this->_helper->block->add('suggestedholidays');
        $this->_helper->block->add('suggestedinspirations');
        $this->_helper->layout->setLayout("two_column_layout");
    }
}

