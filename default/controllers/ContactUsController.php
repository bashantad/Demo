<?php

class Default_ContactUsController extends Zend_Controller_Action
{

    private $_cid = 274;

    public function init()
    {
        $this->_helper->block->add('floatingnav');
        $this->_helper->block->add('loggedin');
        $this->_helper->block->add('attractions');
        $this->_helper->block->add('topsellingholidayslider');

        $this->view->dfController = 'Contact Us';
    }

    public function indexAction()
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'application.ini', 'production');
        $form = new Default_Form_ContactUsForm();
        if ($this->getRequest()->isPost()) {
            try {
                $formData = $this->getRequest()->getPost();
                if ($form->isValid($formData)) {
                    
                    $to = $config->contact->email->info;
                    $from = $formData['email'];
                    $message = $formData['message'];
                    $subject = $formData['subject'];
                    $emailParams = array('subject' => $subject);
                    
                    $mailer = Zend_Registry::get('mailer');
                    $mailer->setTokens($emailParams);
                    $mailer->setTo(array($to => 'NepalAdvisor'));
                    $mailer->setTemplate($message);
                    $mailer->setFrom($from);
                    
                    $mailer->send();
                    $this->view->message = "You message has been sent successfully. We will contact you sortly.";
                }
            } catch (Exception $e) {
                
            }
        }
        $this->view->contentClass = 'main-right-separator';
        $this->_helper->block->add('suggestedholidays');
        $this->_helper->block->add('suggestedinspirations');
        //$this->_helper->block->add('videostories');
        // $this->_helper->block->add('peoplesay');
        $this->_helper->layout->setLayout("two_column_layout");

        $this->view->form = $form;
        $this->view->contact = $config->contact;
    }

}

