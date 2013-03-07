<?php
class Default_Model_Login
{
    protected $_authAdapter;
    public function registerSession($authAdapter = null, $user = null)
    {
        $auth = Zend_Auth::getInstance();
        if (!$user && !$authAdapter) {
            $authAdapter = $this->getAuthAdapter();
            $user = $authAdapter->getResultRowObject(null, 'password');
        }
        $usertypemodel = new Admin_Model_DbTable_Usertypes();
        $usertype = $usertypemodel->getDetailById($user->usertype_id);
        $obj = new stdClass();
        $obj->role = $usertype->short_name;
        $user = (object) array_merge((array) $user, (array) $obj);
        $auth->getStorage()->write($user);
    }
    public function authenticate($salt,$formData)
    {
        $applicationIni = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" .DIRECTORY_SEPARATOR . "application.ini", 'production');
        $usertypeId = $applicationIni->users->general->id;
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $email = $formData['email'];
        $password = md5($salt . md5($formData['password']));
        // and here's the auth adapter that uses the db as backend
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter, 'nad_user', 'email', 'password');
        $authAdapter->setIdentity($email)
                ->setCredential($password)
                ->setCredentialTreatment("(?)AND usertype_id=".$usertypeId." AND status='E'");
        $result = $authAdapter->authenticate();
        $this->_authAdapter = $authAdapter;
        return $result;
    }
    public function getAuthAdapter(){
        return $this->_authAdapter;
    }
    public function registerUser($salt,$formData,$siteUrl)
    {
        
        $formData['activation_code'] = md5(rand());
        $password = $formData['password'];
        $formData['password'] = md5($salt . md5($formData['password']));
        $userModel = new Default_Model_Users();
        $user_id = $userModel->add($formData);
        if (!isset($formData['defined_id'])) {
            $trigger = null;
            $userModel->update($trigger, $user_id);
        }
        $activationLink = $siteUrl . '/users/home/code/' . $formData['activation_code'] . '/id/' . $user_id;
        $emailParams = array(
            'url' => $activationLink,
            'login-url' => $siteUrl . '/users/login',
            'username' => $formData['email']
        );
        try{
            $modelNotification = new Notification_Model_EmailSettings();
            $sent = $modelNotification->sendEmail($emailParams, 'register_user', $formData['email']);
            $formData['password'] = $password;
            $result = $this->authenticate($salt,$formData);    
        }catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
        
        return $result;
    }
    public function checkUser($email, $password)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select();
        $select->from("nad_user")->where("email='$email' AND password='$password'");
        $result = $db->fetchAll($select);
        return $result;
    }
    
}