<?php

class Default_Model_Booking
{

    protected $_name = 'nad_booking';
    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_Booking');
        }
        return $this->_dbTable;
    }

    public function add($formData)
    {
        $data = array(
            'status' => 'E',
            'entered_dt' => date('Y-m-d'),
        );
        if ($formData['booking_dt'] == '') {
            $formData['booking_dt'] = date('Y-m-d');
        }
        $data +=$formData;
        $last_id = $this->getDbTable()->insert($data);
        if (!$last_id) {
            throw new Zend_Db_Exception('Cannot insert data.');
        }
        return $last_id;
    }

    public function addBookedPackage($formData)
    {
        $data = array(
            'status' => 'E',
            'entered_dt' => date('Y-m-d'),
            'bought' => 'N',
            'approved' => 'N'
            );
            if ($formData['booking_dt'] == '') {
                $formData['booking_dt'] = date('Y-m-d');
            }
            $data +=$formData;
            $last_id = $this->getDbTable()->insert($data);
            if (!$last_id) {
                throw new Zend_Db_Exception('Cannot insert data.');
            }
            return $last_id;
    }

    public function getBookedDetailById($booking_id)
    {
        $sql = "SELECT A.booking_id,A.email_address as email,A.booking_type,A.package_id,A.event_id,A.element_dtl_id,
        A.booking_dt,A.user_id,A.no_of_adult,A.no_of_children,B.is_child,B.booking_user_id,
        C.title as package,D.name as element,F.title as event,
            B.full_name,B.dob,B.restriction,B.is_child,B.email_address
            FROM `nad_booking` As A 
           LEFT JOIN nad_booking_user As B ON A.booking_id = B.booking_id
           LEFT JOIN nad_package_mst As C ON A.package_id = C.package_id
        LEFT JOIN nad_element_dtl As D ON A.element_dtl_id = D.element_dtl_id
         LEFT JOIN nad_event_mst As E ON A.event_id = E.event_id
        LEFT JOIN nad_package_mst As F ON E.package_id = F.package_id
            where A.booking_id  = $booking_id
            ";

        $results = $this->getDbTable()->getAdapter()->fetchAll($sql);
        $output = array();
        foreach ($results as $res) {
            $output[] = (array) $res;
        }
        return $output;
    }

    public function isBooked($formData)
    {
        $where = '';
        foreach ($formData as $key => $val) {
            $where .= $key . " = '" . $val . "' AND ";
        }
        $where .=" bought='N'";
        $result = $this->getDbTable()->fetchRow($where);
        if ($result) {
            $transactionModel = new Payment_Model_Transaction();
            $isInTransaction = $transactionModel->getTransactionByBookingId($result['booking_id']);
            if ($isInTransaction) {
                if ('pending' == $isInTransaction['payment_status'] && 'completed' == $isInTransaction['payement_status']) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    public function update($data, $id)
    {
        $this->getDbTable()->update($data, "booking_id='$id'");
    }

    public function getBookingDetail($userId, $bookingId)
    {
        if (!$userId || !$bookingId) {
            return false;
        }
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
        ->from(array("b" => "nad_booking"), array("b.booking_id", "b.booking_type", "b.package_id", "b.event_id", "b.element_dtl_id", "b.booking_dt", "b.no_of_adult", "b.no_of_children"))
        ->joinLeft(array("bu" => "nad_booking_user"), "b.booking_id=bu.booking_id", array("bu.booking_user_id", "bu.full_name", "bu.email_address", "bu.dob", "bu.is_child", "bu.gender", "bu.restriction", "bu.travel_insurance", "bu.medical_insurance", "bu.status AS bu_status"))
        ->joinLeft(array("t" => "nad_transaction"), "b.booking_id=t.booking_id AND b.user_id=t.user_id", array("t.invoice", "t.payment_status", "t.pending_reason"))
        ->where("b.user_id={$userId} AND b.booking_id={$bookingId} AND b.status='E'")
        ->order("b.booking_type");
        //        print $select->assemble();exit;
        return $db->fetchAll($select);
    }

    public function getDetailById($booking_id)
    {
        $userId = Zend_Auth::getInstance()->getIdentity()->user_id;
        $row = $this->getDbTable()->fetchRow("booking_id='$booking_id' AND user_id='$userId' AND status='E'");
        if ($row) {
            $row = $row->toArray();
        } else {
            $row = array();
        }
        return $row;
    }

    public function deleteBooking($id)
    {
        $this->getDbTable()->delete('booking_id = ' . $id);
    }

    public function getAllByUserId($userId)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
        ->from(array("b" => "nad_booking"), array("b.booking_id", "b.booking_type", "b.package_id", "b.event_id", "b.element_dtl_id", "b.booking_dt"))
        ->joinLeft(array("t" => "nad_transaction"), "b.booking_id=t.booking_id AND b.user_id=t.user_id", array("t.invoice", "t.payment_status", "t.pending_reason"))
        ->joinLeft(array("p" => "nad_package_mst"), "b.booking_type='PACKAGE' AND b.package_id=p.package_id", array("p.title as package_title", "p.total_cost as package_cost", "p.description as package_description", "p.file_path as package_image"))
        ->joinLeft(array("e" => "nad_event_mst"), "b.booking_type='EVENT' AND b.event_id=e.event_id", array())
        ->joinLeft(array("ep" => "nad_package_mst"), "b.booking_type='EVENT' AND ep.package_id=e.package_id", array("ep.title as event_title", "ep.total_cost as event_cost", "ep.description as event_description", "ep.file_path as event_image"))
        ->joinLeft(array("ed" => "nad_element_dtl"), "b.booking_type='ELEMENT' AND ed.element_dtl_id=b.element_dtl_id", array("ed.name as element_title", "ed.price as element_cost", "ed.description as element_description", "ed.company_id", "ed.image_url as element_image"))
        ->where("b.user_id='$userId' AND b.status='E' AND b.bought='N'")
        ->order(array("b.booking_id DESC", "b.booking_type"));

        $results = $db->fetchAll($select);
        return $results;
    }

    public function getAllDetailById($booking_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
        ->from(array("b" => "nad_booking"), array("b.booking_id", "b.defined_id", "b.booking_type", "b.package_id", "b.event_id", "b.element_dtl_id", "b.booking_dt", "b.no_of_adult", "b.no_of_children", "b.quantity", "b.user_id"))
        ->joinLeft(array("p" => "nad_package_mst"), "b.booking_type='PACKAGE' AND b.package_id=p.package_id", array("p.title as package_title", "p.total_cost as package_cost", "p.description as package_description", "p.file_path as package_image", 'p.no_of_days as duration'))
        ->joinLeft(array("e" => "nad_event_mst"), "b.booking_type='EVENT' AND b.event_id=e.event_id", array())
        ->joinLeft(array("ep" => "nad_package_mst"), "b.booking_type='EVENT' AND ep.package_id=e.package_id", array("ep.title as event_title", "ep.total_cost as event_cost", "ep.description as event_description", "ep.file_path as event_image"))
        ->joinLeft(array("ed" => "nad_element_dtl"), "b.booking_type='ELEMENT' AND ed.element_dtl_id=b.element_dtl_id", array("ed.name as element_title", "ed.price as element_cost", "ed.description as element_description", "ed.company_id", "ed.image_url as element_image"))
        ->where("b.booking_id='$booking_id' AND b.status='E'");
        $results = $db->fetchAll($select);
        return $results;
    }

    public function getCompleteDetailById($booking_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
        ->from(array("b" => "nad_booking"), array("b.booking_id", "b.defined_id", "b.booking_type", "b.package_id", "b.event_id", "b.element_dtl_id", "b.booking_dt", "b.no_of_adult", "b.no_of_children", "b.quantity", "b.user_id"))
        ->joinLeft(array("p" => "nad_package_mst"), "b.package_id=p.package_id", array("p.title as package_title", "p.total_cost as package_cost", "p.description as package_description", "p.file_path as package_image", 'p.no_of_days as duration'))
        ->joinLeft(array("e" => "nad_event_mst"), "b.event_id=e.event_id", array())
        ->joinLeft(array("ed" => "nad_element_dtl"), "ed.element_dtl_id=b.element_dtl_id", array("ed.name as element_title", "ed.price as element_cost", "ed.description as element_description", "ed.company_id", "ed.image_url as element_image"))
        ->where("b.booking_id='$booking_id'");
        $results = $db->fetchAll($select);
        return $results;
    }

    public function cancelBook($bookingId)
    {
        $data = array(
            'status' => 'D'
            );
            $this->getDbTable()->update($data, "booking_id='$bookingId'");
    }

    public function fetchByPackageId($packageId)
    {
        $db = $this->getDbTable()->getDefaultAdapter();
        $select = $this->getDbTable()->select()->where('package_id=' . $packageId);
        $results = $db->fetchAll($select);
        return $results;
    }

    public function getAllBookingUsersByBookingId($bookingId)
    {
        if (!$bookingId) {
            return false;
        }
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
        ->from(array("b" => "nad_booking"), array(""))
        ->joinLeft(array("bu" => "nad_booking_user"), "b.booking_id=bu.booking_id", array("bu.full_name", "bu.email_address", "bu.dob"))
        ->where("b.booking_id={$bookingId} AND b.status='E'");
        //        print $select->assemble();exit;
        return $db->fetchAll($select);
    }

    public function getBookingList($bookingType)
    {
        if ($bookingType == "ELEMENT") {
            $sql = "SELECT  C.name,B.full_name as user,A.booking_id,A.no_of_adult,A.no_of_children,A.booking_dt
            FROM `nad_booking` As A 
            JOIN nad_user As B ON A.user_id = B.user_id
            JOIN nad_element_dtl As C ON A.element_dtl_id = C.element_dtl_id
            where booking_type = '$bookingType' AND A.status = 'E'";
        }
        if ($bookingType == "PACKAGE") {
            $sql = "SELECT  C.title,B.full_name as user,A.booking_id,A.no_of_adult,A.no_of_children,A.booking_dt
            FROM `nad_booking` As A 
            JOIN nad_user As B ON A.user_id = B.user_id
            JOIN nad_package_mst As C ON A.package_id = C.package_id
            where booking_type = '$bookingType' AND A.status = 'E'";
        }
        if ($bookingType == "EVENT") {
            $sql = "SELECT  D.title,B.full_name as user,A.booking_id,A.no_of_adult,A.no_of_children,A.booking_dt
            FROM `nad_booking` As A 
            JOIN nad_user As B ON A.user_id = B.user_id
            JOIN nad_event_mst As C ON A.event_id = C.event_id
            JOIN nad_package_mst As D ON D.package_id = C.package_id
            where booking_type = '$bookingType' AND A.status = 'E'";
        }
        $results = $this->getDbTable()->getAdapter()->fetchAll($sql);
        return $results;
    }

    public function getTransactionDetailByBookingId($bookingId)
    {
        if (!$bookingId) {
            return false;
        }
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
        ->from(array("b" => "nad_booking"), array("b.booking_id", "b.booking_type", "b.package_id", "b.event_id", "b.element_dtl_id"))
        ->joinLeft(array("t" => "nad_transaction"), "b.booking_id=t.booking_id AND b.user_id=t.user_id", array("t.invoice", "t.payment_status", "t.gross_amount"))
        ->where("b.booking_id={$bookingId}");
        //        print $select->assemble();exit;
        return $db->fetchAll($select);
    }

    public function getbookingdataforattachment($bookingId)
    {
        $data = array();

        $userModel = new User_Model_User();
        $bookingData = $this->getCompleteDetailById($bookingId);
        $bookingData = (array) $bookingData[0];

        $bookingTransactionData = $this->getTransactionDetailByBookingId($bookingId);
        $bookingUserData = $this->getAllBookingUsersByBookingId($bookingId);
        
        $packageModel = new Package_Model_Mapper_NadPackageMst();

        $data['itinerary'] = '';
        $data['overview']['duration'] = '';
        if ($bookingData['booking_type'] == 'PACKAGE') {
            $data['title'] = $bookingData['package_title'];
            $data['overview']['description'] = $bookingData['package_description'];
            $data['overview']['image'] = $bookingData['package_image'];
            $data['overview']['cost'] = $bookingTransactionData[0]->gross_amount;
            $itinerary = $packageModel->getItineraryDetail($bookingData['package_id']);
            $packages = array();
            $data['overview']['duration'] = $bookingData['duration'];
            foreach ($itinerary as $package) {
                $packages[$package->day][] = $package->name;
            };
            $data['itinerary'] = $packages;
        } else if ($bookingData['booking_type'] == 'EVENT') {
            $data['overview']['title'] = $bookingData['event_title'];
            $data['overview']['description'] = $bookingData['event_description'];
            $data['overview']['image'] = $bookingData['event_image'];
            $data['overview']['cost'] = $bookingTransactionData[0]->gross_amount;
        } else if ($bookingData['booking_type'] == 'ELEMENT') {
            $data['overview']['title'] = $bookingData['element_title'];
            $data['overview']['description'] = $bookingData['element_description'];
            $data['overview']['image'] = $bookingData['element_image'];
            $data['overview']['cost'] = $bookingTransactionData[0]->gross_amount;
        } else {
            $data['overview']['title'] = '';
            $data['overview']['description'] = '';
            $data['overview']['image'] = '';
            $data['overview']['cost'] = '';
        }
        $userdata = $userModel->getUserDtlById($bookingData['user_id']);
        $data['booking_details'] = array(
            'booking_code' => $bookingData['defined_id'],
            'booking_status' => ucfirst(strtolower(($bookingTransactionData[0]->payment_status) ? $bookingTransactionData[0]->payment_status : " ")),
            'user_detail' => (array) $userdata[0]
        );

        $data['personal_details'] = $bookingUserData;

        //        var_dump($packages);
        //        var_dump($userdata);
        //        var_dump($bookingData);
        //        var_dump($bookingUserData);
        //        var_dump($bookingTransactionData);
        //        echo "<pre>";
        //        print_r($data);
        //        exit;
        return $data;
    }

    public function createPdfAttachment($bookingId)
    {
        try {
            $data = $this->getbookingdataforattachment($bookingId);

            $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
            $html = $view->partial('pdf/attachment.phtml', array('data' => $data));

            $path = "bookingpdf/NepalAdvisor-" . $data['booking_details']['booking_code'] . ".pdf";

            $pdf = new Pdf_html2pdf();
            $pdf->convert($html, $path, 'F');
            return TRUE;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}

?>
