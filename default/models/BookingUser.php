<?php

class Default_Model_BookingUser
{

    protected $_name = 'nad_booking_user';
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
            $this->setDbTable('Default_Model_DbTable_BookingUser');
        }
        return $this->_dbTable;
    }

    public function add($formData)
    {
        $db = $this->getDbTable()->getDefaultAdapter();
        $db->beginTransaction();
        $bookingId = $formData['booking_id'];
        $bookingUserIds = array();
        unset($formData['booking_id']);
        $i = 0;
        foreach ($formData['bookings'] as $data) {
            if ($i == 0) {
                $userData = array(
                    "full_name" => $data["full_name"]
                );
                $usersModel = new Default_Model_Users();
                $usersModel->updateUserByEmail($userData, $data["email_address"]);
            }
            $i++;
            $data['booking_id'] = $bookingId;
            $bookingUserId = $data['booking_user_id'];
            unset($data['booking_user_id']);
            if ($bookingUserId != '') {
                $data['checked_dt'] = date("Y-m-d");
                array_push($bookingUserIds, $bookingUserId);
                $this->getDbTable()->update($data, "booking_user_id='$bookingUserId'");
                $lastId = true;
            } else {
                $data['status'] = 'E';
                $data['entered_dt'] = date("Y-m-d");
                $data['language_id'] = 1;
                $lastId = $this->getDbTable()->insert($data);
                array_push($bookingUserIds, $lastId);
            }
        }
        $bookingUserIds = implode(",", $bookingUserIds);
        $dsql = "DELETE FROM nad_booking_user WHERE (booking_id=$bookingId) AND (booking_user_id NOT IN ($bookingUserIds))";
        $delete = $db->query($dsql);
        if ($lastId) {
            $db->commit();
        } else {
            $db->rollback();
            throw new Zend_Db_Exception('Cannot insert data.');
        }
        return $lastId;
    }

    public function addDefault($bookingId)
    {
        $user = Zend_Auth::getInstance()->getIdentity();
        $data = array(
            'booking_id' => $bookingId,
            'email_address' => $user->email,
            'full_name' => ($user->full_name) ? $user->full_name : '',
            'status' => 'E',
            'entered_dt' => date("Y-m-d"),
            'language_id' => 1,
        );
        $lastId = $this->getDbTable()->insert($data);
        if (!$lastId) {
            throw new Exception("Couldn't insert data");
        }
        return $lastId;
    }

    public function addBookedUsers($data)
    {
        $optdata = array(
            'status' => 'E',
            'entered_dt' => date("Y-m-d"),
            'language_id' => 1,
        );
        $data +=$optdata;
        $lastId = $this->getDbTable()->insert($data);
        if (!$lastId) {
            throw new Exception("Couldn't insert data");
        }
        return $lastId;
    }

    public function update($data, $id = null)
    {

        if (!empty($data['full_name'])) {
            if ($id) {
                $this->getDbTable()->update($data, "booking_user_id= $id");
            } else {
                $optdata = array(
                    'status' => 'E',
                    'entered_dt' => date("Y-m-d"),
                    'language_id' => 1,
                );
                $data +=$optdata;
                $this->getDbTable()->insert($data);
            }
        }
    }

    public function getDetailById($booking_id)
    {
        $row = $this->getDbTable()->fetchRow("booking_id='$booking_id'");
        return $row->toArray();
    }

    public function deleteBookingUser($userIds, $bookingId)
    {
        if (is_array($userIds)) {
            $Ids = implode(',', array_filter($userIds));
          //  $profiler = Zend_Db_Table_Abstract::getDefaultAdapter()->getProfiler()->setEnabled(true);
            $this->getDbTable()->delete("booking_user_id not in ($Ids) AND booking_id = '$bookingId'");
            //var_dump($profiler->getQueryProfiles());
                    //    exit;
        }
    }

    public function isDetailAdded($bookingId)
    {
        $where = "booking_id='$bookingId'";
        $result = $this->getDbTable()->fetchRow($where);
        return $result;
    }

    public function getBookingUsersByBookingId($bookingId)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $results = $db->fetchAll("SELECT * from nad_booking_user where booking_id='$bookingId'");
        return $results;
    }

}

?>
