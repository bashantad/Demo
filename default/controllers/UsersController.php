<?php

class Default_UsersController extends Zend_Controller_Action
{

    protected $_keys;
    protected $_salt;
    protected $_usertypeId;
	
    public function init()
    {
        $applicationIni = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" .DIRECTORY_SEPARATOR . "application.ini", 'production');
        $this->_usertypeId = $applicationIni->users->general->id;
        $this->_salt = "nepal-advisor";
        $this->_keys = Zend_Registry::get("keys");
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("check-email", "json")
                    ->addActionContext("authenticate","json")
                ->initContext();
        $this->_helper->block->add('floatingnav');
        $this->_helper->block->add('loggedin');
        $this->view->placeholder('title')->set('General User');
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
    }

    public function loginAction()
    {
        $user = new StdClass();
        // get an instace of Zend_Auth
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            //$this->_helper->FlashMessenger('You are logged into the system ');
            return $this->_helper->redirector('index', 'index');
        }
        $form = new Default_Form_LoginForm(true);
        if (isset(Zend_Registry::get('request_uri')->request_uri)) {
            $value = Zend_Registry::get('request_uri')->request_uri;
            $form->request_uri->setValue($value);
            unset(Zend_Registry::get('request_uri')->request_uri);
        }
        $this->view->form = $form;
        // if the user is not logged, the do the logging
        // $openid_identifier will be set when users 'clicks' on the account provider
        $openid_identifier = $this->getRequest()->getParam('openid_identifier', null);

        // $openid_mode will be set after first query to the openid provider
        $openid_mode = $this->getRequest()->getParam('openid_mode', null);

        // this one will be set by facebook connect
        $code = $this->getRequest()->getParam('code', null);

        // while this one will be set by twitter
        $oauth_token = $this->getRequest()->getParam('oauth_token', null);

        $adapter = null;
        // do the first query to an authentication provider
        if ($openid_identifier) {
            if ('https://www.twitter.com' == $openid_identifier) {
                $adapter = $this->_getTwitterAdapter();
            } else if ('https://www.facebook.com' == $openid_identifier) {
                $adapter = $this->_getFacebookAdapter();
            } else {
                // for openid
                $adapter = $this->_getOpenIdAdapter($openid_identifier);

                // specify what to grab from the provider and what extension to use
                // for this purpose
                $toFetch = $this->_keys->openid->tofetch->toArray();

                // for google and yahoo use AtributeExchange Extension
                if ('https://www.google.com/accounts/o8/id' == $openid_identifier || 'http://me.yahoo.com/' == $openid_identifier) {
                    $ext = $this->_getOpenIdExt('ax', $toFetch);
                } else {
                    $ext = $this->_getOpenIdExt('sreg', $toFetch);
                }
                $adapter->setExtensions($ext);
            }

            //here a user is redirect to the provider for loging
            $result = $auth->authenticate($adapter);

            // the following two lines should never be executed unless the redirection faild.
            $this->_helper->FlashMessenger('Redirection faild');
            return $this->_helper->redirector('index', 'index');
        } else if ($openid_mode || $code || $oauth_token) {
            // this will be exectued after provider redirected the user back to us
            if ($code) {
                // for facebook
                $adapter = $this->_getFacebookAdapter();
            } else if ($oauth_token) {
                // for twitter
                $adapter = $this->_getTwitterAdapter()->setQueryData($_GET);
            } else {
                // for openid                
                $adapter = $this->_getOpenIdAdapter(null);

                // specify what to grab from the provider and what extension to use
                // for this purpose
                $ext = null;

                $toFetch = $this->_keys->openid->tofetch->toArray();

                // for google and yahoo use AtributeExchange Extension
                if (isset($_GET['openid_ns_ext1']) || isset($_GET['openid_ns_ax'])) {
                    $ext = $this->_getOpenIdExt('ax', $toFetch);
                } else if (isset($_GET['openid_ns_sreg'])) {
                    $ext = $this->_getOpenIdExt('sreg', $toFetch);
                }

                if ($ext) {
                    $ext->parseResponse($_GET);
                    $adapter->setExtensions($ext);
                }
            }
            $result = $auth->authenticate($adapter);
            if ($result->isValid()) {
                //$toStore = array('identity' => $auth->getIdentity());
                if ($ext) {
                    // for openId
                    $toStore->properties = (object) $ext->getProperties();
                } else if ($code) {
                    // for facebook
                    $msgs = $result->getMessages();
                    $toStore->properties = $msgs['user'];
                } else if ($oauth_token) {
                    // for twitter
                    $identity = $result->getIdentity();
                    // get user info
                    $twitterUserData = (array) $adapter->verifyCredentials();
                    $toStore->identity = $identity['user_id'];
                    if (isset($twitterUserData['status'])) {
                        $twitterUserData['status'] = (array) $twitterUserData['status'];
                    }
                    $toStore->properties = $twitterUserData;
                }
                $userModel = new Default_Model_Users();
                if (!$userModel->checkEmail($toStore->properties->email)) {
                    $newUser = array(
                        'email' => $toStore->properties->email,
                        'password' => md5($this->_salt . md5(123456)),
                        'usertype_id'=>$this->_usertypeId
                    );
                    if (isset($toStore->properties->name)) {
                        $newUser['full_name'] = $toStore->properties->name;
                    }
                    $userId = $userModel->add($newUser, true);
                    $user = $userModel->findUser($userId);
                    $this->_helper->FlashMessenger("Your Nepaladvisor password is 12345678. Please change it at account settings.");
                } else {
                    $user = $userModel->getUserByEmail($toStore->properties->email);
                }
                $user->role = "general";
                $auth->getStorage()->write($user);

               // $this->_helper->FlashMessenger('Successful authentication.');
                //return $this->_redirect('/index/index');
                return $this->_helper->redirector('index', 'index', 'default');
            } else {
                $this->_helper->FlashMessenger($result->getMessages());
                //return $this->_redirect('/index/index');
                return $this->_helper->redirector('index', 'index', 'default');
            }
        }
    }

    public function indexAction()
    {
        if ($this->isGeneral()) {
            $this->_helper->redirector('index', 'index');
        }
        $form = new Default_Form_LoginForm(true);
        $this->view->form = $form;
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $requestUri = $form->getValue('request_uri');
                $loginModel = new Default_Model_Login();
                $formData = $request->getPost();
                $result = $loginModel->authenticate($this->_salt,$formData);
                if (!$result->isValid()) {
                    $this->_helper->FlashMessenger->addMessage(array('alert'=>'Your login credential is invalid.'));
                    $this->view->form = $form;
                    $this->_helper->redirector('login'); // re-render the login form
                } else {
                    if (!Zend_Auth::getInstance()->getIdentity()) {
                        $loginModel->registerSession();
                    }
                    //check if the liked contents is saved in database
                    $this->checksessionlike();
                  //  $this->_helper->FlashMessenger('Successful Authentication.');
                    $this->_redirect($requestUri);
                }
            }else {
                $this->_helper->FlashMessenger->addMessage(array('alert'=>'Your login credential is invalid.'));
                $this->_helper->redirector("login");
            }
        }else{
            $this->_helper->redirector("login");
        }
    }

    public function registerAction()
    {
        $form = new Default_Form_RegisterForm();
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            $formData['usertype_id'] = $this->_usertypeId;
            if ($form->isValid($formData)) {
                try{
                    $userModel = new Default_Model_Users();
                    if ($userModel->checkEmail($formData['email'])) {
                        throw new Exception("Email already exists");
                    }
                    $loginModel = new Default_Model_Login();
                    $siteUrl = $this->view->siteUrl();
                    $result = $loginModel->registerUser($this->_salt, $formData, $siteUrl);
                    if (!$result->isValid()) {
                        // Invalid credentials
                        $form->setDescription('Invalid credentials provided');
                        $this->view->form = $form;
                        return $this->_helper->redirector('index'); // re-render the login form
                    } else {
                        $loginModel->registerSession();
                        $this->_helper->redirector('home');
                    }    
                }catch(Exception $e){
                    $this->_helper->FlashMessenger->addMessage(array("error"=>$e->getMessage()));
                }
                
            }
        }
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy(true);
        $this->_helper->redirector('index', 'index');
    }

    public function homeAction()
    {
        $rowStatus = '';
        $code = $this->_getParam('code');
        $id = $this->_getParam('id');
        $user = null;
        if ('' != $code && '' != $id) {
            $userModel = new Default_Model_Users();
            $user = $userModel->getDetailById($id);
            if ($user['activation_code'] == $code) {
                $rowStatus = $userModel->approve($id);
            }
        }
        $user = Zend_Auth::getInstance()->getIdentity();
        if ($user->approved == 'Y') {
            $rowStatus = $user->approved;
        }
        $this->view->user = $user;
        $this->view->approved = $rowStatus;
    }

    public function checkEmailAction()
    {
        $email = $this->_getParam('email');
        $userModel = new Default_Model_Users();
        if ($userModel->checkEmail($email)) {
            $this->view->check = true;
            $this->view->msg = "Email $email already exists";
            $this->view->bookMsg = "You already have an account.Please enter password";
        } else {
            $this->view->check = false;
        }
    }

    public function isGeneral()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $userType = Zend_Auth::getInstance()->getIdentity()->usertype_id;
            if (2 == $userType) {
                return true;
            }
        }
    }

    public function settingsAction()
    {
        try {
            $userId = Zend_Auth::getInstance()->getIdentity()->user_id;
            $userModel = new Default_Model_Users();
            $user = $userModel->getDetailById($userId);
            $form = new Default_Form_SettingsForm();
            $form->populate($user);
            $this->view->form = $form;
            if ($this->getRequest()->isPost()) {
                $formData = $this->getRequest()->getPost();
                if ($form->isValid($formData)) {
                    if(''==$formData['password']){
                        unset($formData['password']);
                    }else{
                        $formData["password"] = md5($this->_salt . md5($formData["password"]));    
                    }
                    unset($formData['save']);
                    $userModel = new Default_Model_Users();
                    $userModel->update($formData, $userId);
                    $user = $userModel->getDetailById($userId);
                    $loginModel = new Default_Model_Login();
                    $loginModel->registerSession(null, (object) $user);
                    $this->_helper->FlashMessenger->addMessage(array('message'=>'Successfully saved your data'));
                }
            }
        }
        catch(Exception $e) {
            print $e->getMessage();
        }
    }

    protected function _getFacebookAdapter()
    {
        extract($this->_keys->facebook->toArray());
        return new My_Auth_Adapter_Facebook($appid, $secret, $redirecturi, $scope);
    }

    /**
     * Get My_Auth_Adapter_Oauth_Twitter adapter
     *
     * @return My_Auth_Adapter_Oauth_Twitter
     */
    protected function _getTwitterAdapter()
    {
        extract($this->_keys->twitter->toArray());
        return new My_Auth_Adapter_Oauth_Twitter(array(), $appid, $secret, $redirecturi);
    }

    /**
     * Get Zend_Auth_Adapter_OpenId adapter
     *
     * @param string $openid_identifier
     * @return Zend_Auth_Adapter_OpenId
     */
    protected function _getOpenIdAdapter($openid_identifier = null)
    {
        $adapter = new Zend_Auth_Adapter_OpenId($openid_identifier);
        $dir = APPLICATION_PATH . '/../tmp';
        if (!file_exists($dir)) {
            if (!mkdir($dir)) {
                throw new Zend_Exception("Cannot create $dir to store tmp auth data.");
            }
        }
        $adapter->setStorage(new Zend_OpenId_Consumer_Storage_File($dir));

        return $adapter;
    }

    /**
     * Get Zend_OpenId_Extension. Sreg or Ax. 
     * 
     * @param string $extType Possible values: 'sreg' or 'ax'
     * @param array $propertiesToRequest
     * @return Zend_OpenId_Extension|null
     */
    protected function _getOpenIdExt($extType, array $propertiesToRequest)
    {

        $ext = null;

        if ('ax' == $extType) {
            $ext = new My_OpenId_Extension_AttributeExchange($propertiesToRequest);
        } elseif ('sreg' == $extType) {
            $ext = new Zend_OpenId_Extension_Sreg($propertiesToRequest);
        }

        return $ext;
    }

    public function forgotPasswordAction()
    {
        $form = new Default_Form_PasswordRecoveryForm();
        $showForm = true;
        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                $userModel = new Default_Model_Users();
                $email = $formData["email"];
                if ($userModel->checkEmail($email)) {
                    $showForm = false;
                    $user = $userModel->getUserByEmail($email);
                    /*
                     * Code to send email regarding password change
                     */
                    $activationCode = md5(rand());
                    $data = array("activation_code" => $activationCode);
                    $userModel->updateFields($data, $user->user_id);
                    $activationLink = $this->view->siteUrl() . '/users/password-recovery/code/' . $activationCode . '/id/' . $user->user_id;
                    try {
                        $emailParams = array(
                            'url' => $activationLink,
                            'username' => $user->username
                        );
                        $modelNotification = new Notification_Model_EmailSettings();
                        $modelNotification->sendEmail($emailParams, 'forgot_password', $email);
                        $this->view->message = "You have successfully submitted your request.Your password recovery information has been sent to <span style=\"color:#00983D\">$email</span>. Please check your email. Check your spam folder too.";
                    } catch (Exception $e) {
                        $this->view->message = $e->getMessage();
                    }
                    /*
                     * End of code to send email
                     */
                } else {
                    $frontController = Zend_Controller_Front::getInstance();
                    $baseUrl = $frontController->getBaseUrl();
                    $registerUrl = "<a href=\"$baseUrl/users/register\">here</a>";
                    $this->view->error = "<span style=\"color:#ff0000;\">$email</span> doesn't exist in our record. You can create your account $registerUrl";
                }
            }
        }
        $this->view->form = $form;
        $this->view->show = $showForm;
    }

    public function passwordRecoveryAction()
    {
        $showForm = false;
        $code = $this->_getParam("code");
        $userId = $this->_getParam("id");
        $userModel = new Default_Model_Users();
        $user = $userModel->getDetailById($userId);
        if ($code == $user['activation_code']) {
            $showForm = true;
            $this->view->id = $userId;
            $this->view->activationCode = $code;
            if ($this->getRequest()->isPost()) {
                $data = array("password" => md5($this->_salt . md5($this->getRequest()->getPost("password"))));
                $userModel->updateFields($data, $userId);
                $this->_helper->redirector("login");
            }
        } else {
            $this->_helper->redirector("forget");
        }
    }

    public function checksessionlike()
    {
        $userId = Zend_Auth::getInstance()->getIdentity()->user_id;
        $session = Zend_Registry::get('defaultsession');
        $contentLikeModel = new Content_Model_NadUserContentLikes;
        $packageLikeModel = new Package_Model_NadUserPackageLikes;
        foreach ($session->userlikecontent as $element_id => $values) {
            if (is_array($values)) {
                foreach ($values as $content_id => $val) {
                    $data['user_id'] = $userId;
                    $data['content_id'] = $content_id;
                    $data['element_id'] = $element_id;
                    $result = $contentLikeModel->add($data);
                }
            }
        }

        foreach ($session->userlikedpackage as $package_id => $val) {
            $data['user_id'] = $userId;
            $data['package_id'] = $package_id;
            $result = $packageLikeModel->add($data);
        }
    }
    
    public function authenticateAction()
    {
        $email = $this->_getParam("email");
        $password = md5($this->_salt . md5($this->_getParam("password")));
        $loginModel = new Default_Model_Login();
        $doesExists = false;
        if($loginModel->checkUser($email, $password)){
            $doesExists = true;
        }
        $this->view->doesExists = $doesExists;
    }
}
