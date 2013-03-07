<?php

class Booking_TransactionController extends Zend_Controller_Action
{

    private $config;

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('delete', 'json')
                ->addActionContext('status', 'json')
                ->addActionContext('parent', 'json')
                ->addActionContext('check-unique-name', 'json')
                ->initContext();
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "transaction.ini", 'production');
    }

    public function indexAction()
    {
        $this->_redirect('booking/transaction/list');
    }

    public function addAction()
    {
        
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');
        $elementModel = new Package_Model_ElementCategory();
        $elementModel->delete($id);
    }

    public function editAction()
    {
        $this->view->placeholder('title')->set('Edit Transaction');
        $invoiceId = $this->getRequest()->getParam('id');

        $modelTransaction = new Payment_Model_Transaction();
        $transaction = $modelTransaction->getTansactionDetail(array('invoice' => $invoiceId));
        $transaction = $transaction[0];

        $form = new Booking_Form_TransactionForm();
        $form->populate($transaction);

        if ($_POST) {
            if ($form->isValid($_POST)) {
                $transactionUpdate['received_amount'] = $_POST['gross_amount'];
                $transactionUpdate = array_merge($transactionUpdate, $_POST);
                unset($transactionUpdate['submit']);
                $result = $modelTransaction->update($transactionUpdate, $invoiceId);
                if ($result) {
                    $this->_helper->FlashMessenger->addMessage(array('message' => "Transaction has been sucessfully Updated."));
                    $this->_helper->redirector("list", "transaction", "booking");
                } else {
                    $this->_helper->FlashMessenger->addMessage(array('alert' => "Some error has occured. Please fill details again."));
                }
            } else {
                $form->populate($_POST);
            }
        }

        $this->view->form = $form;
        $this->view->transaction = $transaction;
    }

    public function listAction()
    {
        $this->view->placeholder('title')->set('Transactions');
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'grid.ini', 'production');
        $grid = Bvb_Grid::factory('Table', $config);
        $data = $this->_listdata();
        $source = new Bvb_Grid_Source_Array($data);
        $grid->setSource($source);
        $grid->setImagesUrl('/grid/');
        $editColumn = new Bvb_Grid_Extra_Column();
        $editColumn->setPosition('right')->setName('Edit')->setDecorator("<a href=\"/booking/transaction/edit/id/{{invoice}}\">Edit</a><input class=\"address-id\" name=\"address_id[]\" type=\"hidden\" value=\"{{invoice}}\"/>");
//        $deleteColumn = new Bvb_Grid_Extra_Column();
//        $deleteColumn->setPosition('right')->setName('Delete')->setDecorator("<a class=\"delete-data\" href=\"/booking/transaction/delete/id/{{invoice}}\">Delete</a>");
//        $grid->addExtraColumns($editColumn, $deleteColumn);
        $grid->addExtraColumns($editColumn);

        $grid->updateColumn('invoice', array('hidden' => true));
        $grid->updateColumn('Description', array('decorator' => '{{Description}}', 'escape' => true));
        $grid->updateColumn('Status', array('decorator' => '{{Status}}', 'escape' => true));
        $grid->setExport(array('print', 'word', 'csv', 'excel', 'pdf'));
        $grid->setRecordsPerPage(20);
        $grid->setPaginationInterval(array(
            5 => 5,
            10 => 10,
            20 => 20,
            30 => 30,
            40 => 40,
            50 => 50,
            100 => 100
        ));
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('status', array('values' => array('E' => 'Enabled', 'D' => 'Disabled'), 'class' => 'form-select'));
        $grid->addFilters($filters);
        if ($this->_getParam('_exportTo') == null) {
            $grid->addClassCellCondition('status', "'{{status}}'=='E'", "permitted", "notpermitted");
        }
        $this->view->grid = $grid->deploy();
    }

    public function _listdata()
    {
        $rows = array();
        $modelTransaction = new Payment_Model_Transaction();
        $allData = $modelTransaction->alldata();
        $i = 1;
        foreach ($allData as $element):
            $data = array();

            $description = "";

            if ($element['element_dtl_id']) {
                $params = sprintf("company_id=%s&element_dtl_id=%s", $element['company_id'], $element['element_dtl_id']);
                $cipherQuery = $this->view->rijndael->encrypt($params);
                $url = $this->view->siteUrl() . "/serviceprovider/detail/?q=$cipherQuery";
                $description = "<a href='{$url}'>" . ucfirst($element['item_name']) . "</a>";
            } elseif ($element['package_id']) {
                $url = sprintf("%s/holidays/%s/%s.html", $this->view->siteUrl(), urlencode($element['package_title']), $this->view->rijndael->encrypt($element['package_id']));
                $description = "<a href='{$url}'>" . ucfirst($element['item_name']) . "</a>";
            } elseif ($element['event_id']) {
                $params = sprintf("event_id=%s", $element['event_id']);
                $cipherQuery = $this->view->rijndael->encrypt($params);
                $url = $this->view->siteUrl() . "/default/package/detail/?q=$cipherQuery";
                $description = "<a href='{$url}'>" . ucfirst($element['item_name']) . "</a>";
            }

            $data['sn'] = $i++;
            $data['invoice'] = $element['invoice'];
            $data['User'] = ($element['user']) ? $element['user'] : $element['email'];
            $data['Transaction Date'] = date('d-M-Y', strtotime($element['date_creation']));
            $data['Description'] = $description;
            $data['Amount'] = $element['gross_amount'];
            $data['Debit Credit'] = $element['debit_credit'];
            $data['payment_type'] = $element['payment_type'];
            $data['Status'] = (($element['payment_status'] == $this->config->paymentstatus->pending && $element['checked'] == 'N') || $element['payment_status'] == $this->config->paymentstatus->processing) ? "<a href='{$this->view->siteUrl()}/booking/transaction/confirm/id/{$element['invoice']}' alt='To Confirm your Payment.'>" . ucfirst(strtolower($element['payment_status'])) . "</a>" : ucfirst(strtolower($element['payment_status']));
            $rows[] = $data;
        endforeach;
        return $rows;
    }

    public function confirmAction()
    {
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        $invoiceId = $this->getRequest()->getParam('id');
        $modelTransactionFile = new Payment_Model_TransactionFile();
        $modelTransaction = new Payment_Model_Transaction();
        $bookingModel = new Default_Model_Booking();
        $userModel = new User_Model_User();
        
        $user = $userModel->getDetailByInvoice($invoiceId);

        $transaction = $modelTransaction->getTansactionDetail(array('invoice' => $invoiceId));
        $transaction = $transaction[0];
        $transactionfiles = '';
        $form = '';
        if ($transaction['payment_status'] == $this->config->paymentstatus->pending && $transaction['checked'] == 'N') {
            $form = new Booking_Form_ConfirmAmountForm();
            $form->populate(array('grossamount' => $transaction['gross_amount']));
            if ($_POST) {
                if ($form->isValid($_POST)) {
                    $bookingData = $bookingModel->getCompleteDetailById($transaction['booking_id']);
                    
                    $transactionUpdate['gross_amount'] = $_POST['grossamount'];
                    $transactionUpdate['checked'] = 'Y';
                    $transactionUpdate['checked_by'] = $identity->full_name;
                    $transactionUpdate['checked_dt'] = date('Y-m-d');
                    $result = $modelTransaction->update($transactionUpdate, $invoiceId);
                    switch($bookingData[0]->booking_type){
                        case "PACKAGE":
                            $packageModel = new Package_Model_Mapper_NadPackageMst();
                            $package = $packageModel->getpackagedataforprint($bookingData[0]->package_id);
                            break;
                        case "EVENT":
                            $eventModel = new Package_Model_Event();
                            $package = $eventModel->getDetailById($bookingData[0]->event_id);
                            break;
                        case "ELEMENT":
                            $elementDtlModel = new Package_Model_ElementDetail();
                            $package = $elementDtlModel->fetchElementDetailById($bookingData[0]->element_dtl_id);
                            break;
                    }
                    if ($result) {
                        $transactionNew = $modelTransaction->getTansactionDetail(array('invoice' => $invoiceId));
                        $transactionNew = $transactionNew[0];
                        $emailParams = array(
                            'username' => ($user->username) ? $user->username : $user->email,
                            'package' => $transaction['item_name'],
                            'cost' => $transactionNew['gross_amount'],
                            'duration' => $package['overview']['duration'],
                            'count'=> $transactionNew['quantity'],
                            'status'=> $transactionNew['payment_status']
                        );

                        $pdfResult = $bookingModel->createPdfAttachment($transaction['booking_id']);
                        
                        if ($pdfResult) {
                            $attachment = $this->view->siteUrl() . '/public/bookingpdf/NepalAdvisor-' . $bookingData[0]->defined_id . ".pdf";
                        } else {
                            $attachment = '';
                        }

                        $modelNotification = new Notification_Model_EmailSettings();
                        $modelNotification->sendEmail($emailParams, 'booking_amt_confirmation', $transaction['email'],  $attachment,'booking');

                        $this->_helper->FlashMessenger->addMessage(array('message' => "Payment Amount has been Confirmed."));
                        $this->_helper->redirector("list", "transaction", "booking");
                    } else {
                        $this->_helper->FlashMessenger->addMessage(array('alert' => "Some error has occured. Please fill details again."));
                    }
                } else {
                    $form->populate($_POST);
                }
            }
        } elseif ($transaction['payment_status'] == $this->config->paymentstatus->processing) {
            $form = new Booking_Form_ConfirmPaymentForm;

            if ($_POST) {
                if ($form->isValid($_POST)) {
                    $transactionUpdate['gross_amount'] = $_POST['grossamount'];
                    $transactionUpdate['payment_status'] = $this->config->paymentstatus->completed;
                    $transactionUpdate['payment_date'] = date("Y-m-d");
                    $transactionUpdate['payer_email'] = $transaction['email'];
                    $result = $modelTransaction->update($transactionUpdate, $invoiceId);
                    $bookingUpdate = '';
                    if ($result) {
                        $bookingUpdate['bought'] = 'Y';
                        $bookingModel->update($bookingUpdate, $transaction['booking_id']);
                        $transaction = $modelTransaction->getTansactionDetail(array('invoice' => $invoiceId));
                        $transaction = $transaction[0];
                        $emailParams = array(
                            'package' => $transaction['item_name'],
                            'username' => $user->username
                        );
                        $pdfResult = $bookingModel->createPdfAttachment($transaction['booking_id']);
                        if ($pdfResult) {
                            $attachment = $this->view->siteUrl() . '/public/bookingpdf/NepalAdvisor-' . $bookingData[0]->defined_id . ".pdf";
                        } else {
                            $attachment = '';
                        }
                        $modelNotification = new Notification_Model_EmailSettings();
                        $modelNotification->sendEmail($emailParams, 'payment_success', $user->email, $attachment,'booking');

                        $this->_helper->FlashMessenger->addMessage(array('message' => "Payment Amount has been Confirmed."));
                        $this->_helper->redirector("list", "transaction", "booking");
                    } else {
                        $this->_helper->FlashMessenger->addMessage(array('alert' => "Some error has occured. Please fill details again."));
                    }
                } else {
                    $form->populate($_POST);
                }
            }

            if ($transaction['payment_status'] == $this->config->paymentstatus->processing) {
                $transactionfiles = $modelTransactionFile->getTransarctionFileByInvoice($invoiceId);
            }
        } else {
            $this->_helper->FlashMessenger->addMessage(array('message' => "All the confirmation process has been successfully completed for this transaction."));
            $this->_helper->redirector("list", "transaction", "booking");
        }
        $this->view->form = $form;
        $this->view->transaction = $transaction;
        $this->view->transactionfile = $transactionfiles;
    }

    public function statusAction()
    {
        $this->view->placeholder('title')->set('Status Transaction');
        $invoiceId = $this->getRequest()->getParam('id');

        $modelTransaction = new Payment_Model_Transaction();
        $transaction = $modelTransaction->getTansactionDetail(array('invoice' => $invoiceId));
        $transaction = $transaction[0];

        $form = new Booking_Form_TransactionStatusForm();
        $form->populate($transaction);

        if ($_POST) {
            if ($form->isValid($_POST)) {
                $transactionUpdate['received_amount'] = $_POST['gross_amount'];
                $transactionUpdate = array_merge($transactionUpdate, $_POST);
                unset($transactionUpdate['submit']);
                $result = $modelTransaction->update($transactionUpdate, $invoiceId);
                if ($result) {
                    $this->_helper->FlashMessenger->addMessage(array('message' => "Transaction has been sucessfully Updated."));
                    $this->_helper->redirector("list", "transaction", "booking");
                } else {
                    $this->_helper->FlashMessenger->addMessage(array('alert' => "Some error has occured. Please fill details again."));
                }
            } else {
                $form->populate($_POST);
            }
        }

        $this->view->form = $form;
        $this->view->transaction = $transaction;
    }

}

