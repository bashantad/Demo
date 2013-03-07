<?php

class Default_Model_Users
{

    protected $_name = 'nad_user';
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
            $this->setDbTable('Default_Model_DbTable_User');
        }
        return $this->_dbTable;
    }

    //public function fetchAll

    public function add($formData, $param = null)
    {
        if ($param) {
            $data = $formData;
        } else {
            $data['email'] = $formData['email'] ;
            $data['password'] = $formData['password'];
            $data['activation_code'] = $formData['activation_code'];
            $data['usertype_id'] = $formData['usertype_id'];
        }
        $data += array(
            'status' => 'E',
            'entered_dt' => date('Y-m-d'),
        );
        $last_id = $this->getDbTable()->insert($data);
        if (!$last_id) {
            throw new Zend_Db_Exception('Cannot insert data.');
        }
        return $last_id;
    }

    public function getDetailById($user_id)
    {
        $row = $this->getDbTable()->fetchRow('user_id=' . $user_id);
        return $row->toArray();
    }

    public function getUser($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select();
        $select->from(array('u' => 'nad_user'), array('*'))
                ->join(array('ut' => 'nad_usertype'), 'u.usertype_id = ut.usertype_id', array('defined_id as usertype_defined_id'))
                ->join(array('c' => 'nad_country_mst'), 'c.country_id = u.country_id', array('defined_id as country_defined_id'))
                ->where('u.user_id=' . $id);
        $row = $db->fetchRow($select);
        return (array) $row;
    }

    public function update($formData, $id)
    {
        if (!is_array($formData)) {
            $data['defined_id'] = $id;
        } else {
            $data = $formData;
            $data['checked_dt'] = date("Y-m-d");
        }
        $update = $this->getDbTable()->update($data, 'user_id = ' . $id);
    }

    public function setValues($formData)
    {
        $data = array(
            'password' => $formData['password'],
            'email' => $formData['email'],
            'country_id' => $formData['country_id'],
            'username' => $formData['username'],
            'full_name' => $formData['full_name'],
            'address' => $formData['address'],
            'last_login_dt' => date('Y-m-d H:i:s'),
        );
        return $data;
    }

    public function deleteUser($id)
    {
        $this->getDbTable()->delete('user_id= ' . $id);
    }

    public function changeStatus($user_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $data = array('status' => 'E');
        $this->getDbTable()->update($data, 'user_id = ' . $user_id);
        return $data['status'];
    }

    public function updateFields($data, $userId)
    {
        $this->getDbTable()->update($data, "user_id='$userId'");
    }

    public function checkEmail($email)
    {
        $where = '';
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $userId = Zend_Auth::getInstance()->getIdentity()->user_id;
            $where = " AND user_id<>'$userId'";
        }
        $doesExists = '';
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT user_id FROM nad_user WHERE email ='$email'$where";
        if ($db->fetchRow($sql)) {
            $doesExists = true;
        } else {
            $doesExists = false;
        }
        return $doesExists;
    }

    public function findUser($userId)
    {
        $result = $this->getDbTable()->fetchRow("user_id='$userId'");
        return (object) $result->toArray();
    }

    public function getUserByEmail($email)
    {
        $select = $this->getDbTable()->select()->where("email='$email'");
        $result = $this->getDbTable()->fetchAll($select)->toArray();
        return (object) $result[0];
    }
    public function updateUserByEmail($data, $email)
    {
        $this->getDbTable()->update($data, "email='$email'");
        $user = $this->getUserByEmail($email);
        $loginModel = new Default_Model_Login();
        $loginModel->registerSession(null,$user);
    }
    public function approve($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $data = array('approved' => 'Y');
        $this->getDbTable()->update($data, 'user_id = ' . $user_id);
        return $data['approved'];
    }
}

?>
