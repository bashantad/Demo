<?php

class Default_ServiceProviderController extends Zend_Controller_Action
{

    protected $_salt;
    protected $_usertypeId;

    public function init()
    {
        $this->_salt = "nepal-advisor";
        $applicationIni = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR . "application.ini", 'production');
        $this->limit = $applicationIni->pagination->limit;
        $this->_usertypeId = $applicationIni->users->general->id;
    }

    public function bookAction()
    {
        $auth = Zend_Auth::getInstance();
        /*cipher parameter contains either package, element or event in encrypted form
         *this parameter is in the format "package-package_id"
         *so first parameter after explode will determine whether it's package element or event
         */
        $params = explode('-', $this->view->rijndael->decrypt($this->_getParam('cipher')));
        $element_dtl_id = $this->_getParam('element_dtl_id', null);
        $package_id = $this->_getParam('package_id', null);
        $event_id = $this->_getParam('event_id', null);
        if ($params[0]) {
            switch ($params[0]) {
                case 'package':
                    $package_id = $params[1];
                    break;
                case 'element':
                    $element_dtl_id = $params[1];
                    break;
                case 'event':
                    $event_id = $params[1];
                    break;
                default:
                    $this->_helper->FlashMessenger->addMessage(array('error' => "You haven't choosen any packages to book."));
                    $this->_helper->redirector("booked-trip");
            }
        }
        $bookingModel = new Default_Model_Booking();
        $bookingForm = new Default_Form_BookingForm();
        $this->view->bookingForm = $bookingForm;
        if (!$auth->hasIdentity()) {
            $bookingUserForm = new Default_Form_BookingRegistrationForm();
            $this->view->registerForm = $bookingUserForm;
            /*this is the form containing email, password and captcha,
             *which is used for login/register credential for not logged in users
             */
        }
        $this->view->currentDate = date('Y-m-d');
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost("submit") == "Next") {
            $formData = $this->getRequest()->getPost();
            if (!$auth->hasIdentity()) {
                $a = $bookingForm->isValid($formData);//validates booking details like no_of_adults and start_date
                $b = $bookingUserForm->isValid($formData);//validates user details like email,password and captcha
                if ($a AND $b) {
                    $loginModel = new Default_Model_Login();
                    $usersModel = new Default_Model_Users();
                    $doesExists = $usersModel->checkEmail($formData['email']);//check if that user with provided email and password exists or not
                    try {
                        if ($doesExists) {
                            //if the user already exists then it checks if login credential is valid or not
                            $result = $loginModel->authenticate($this->_salt, $formData);
                            if ($result->isValid()) {
                                $loginModel->registerSession();//register user session
                            } else {
                                $href = Zend_Controller_Front::getInstance()->getBaseUrl() . "/users/forgot-password";
                                $url = "<a href=\"$href\">click here</a>";
                                $this->_helper->FlashMessenger->addMessage(array("error" => "You already have an account. But your password is wrong. Forgot password? " . $url));
                                //if the provide email already exists but password doesn't match then this will give an error message showing forgot password link
                            }
                        } else {
                            $formData['usertype_id'] = $this->_usertypeId;//set usertype_id = 2
                            //if given email doesn't exists then system will add new user with given email and password
                            $result = $loginModel->registerUser($this->_salt, $formData, $this->view->siteUrl());
                            unset($formData['usertype_id']);
                            if ($result->isValid()) {
                                $loginModel->registerSession();
                                $this->_helper->FlashMessenger->addMessage(array("Your account for nepaladvisor has been created. Activation link is sent in your email. Please activate your account"));
                            }
                        }
                    } catch (Exception $e) {
                        $this->_helper->FlashMessenger->addMessage(array($e->getMessage()));
                    }
                }
            }
        }

        if ($auth->hasIdentity()) {
            $checkbook = array("user_id" => $auth->getIdentity()->user_id);
            if ($package_id) {
                $checkbook['package_id'] = $package_id;
            } elseif ($element_dtl_id) {
                $checkbook['element_dtl_id'] = $element_dtl_id;
            } elseif ($event_id) {
                $checkbook['event_id'] = $event_id;
            }
            $isBooked = $bookingModel->isBooked($checkbook);
            //checks if the combination of the user and given package,event or element exists in our database or not.s
            if ($isBooked) {
                //if this combination exists and it's booing status is E and bought flag is N then it will redirect to it's edit page
                if ($isBooked['status'] == 'E' AND $isBooked['bought'] == 'N') {
                    $this->_helper->FlashMessenger->addMessage(array('message' => "This package is already booked. However you can edit this booking"));
                    $params = sprintf("booking_id=%s&encryption=%s", $isBooked['booking_id'], rand());
                    $cipherQuery = $this->view->rijndael->encrypt($params);
                    $parameter = array('q' => $cipherQuery);
                    $this->_helper->redirector("edit-book", "serviceprovider", "default", $parameter);
                }
            }
        }
        /*when user arrives at the booking page it will fetch corresponding package,event or element detail 
         *If element_dtl_id is set then it will fetch element detail from nad_element_dtl,
         *if package_id is set then it will fetch package from nad_package_mst
         *and if event_id is set then it will fetch custom package(event) from nad_event_mst 
         */
        if ($element_dtl_id) {
            $elementDtlModel = new Package_Model_ElementDetail();
            $elementDetail = $elementDtlModel->getElementDetailById($element_dtl_id);
            $this->view->elementDetail = $elementDetail;
        } elseif ($package_id) {
            $packageMstModel = new Package_Model_NadPackageMst();
            $package = (object) $packageMstModel->find($package_id)->toArray();
            $fixDepartureModel = new Package_Model_NadPackageFixed();
            $this->view->fixDates = $fixDepartureModel->getDetailById($package_id);
            $this->view->package = $package;
        } elseif ($event_id) {
            $userId = $auth->getIdentity()->user_id;
            $customPackage = '';
            $eventModel = new Package_Model_Event();
            $customizedPackage = $eventModel->fetchPackagesByUserId($userId, $event_id);
            if ($customizedPackage) {
                $customPackage = $customizedPackage[0];
            }
            $customData = array(
                "booking_dt" => $this->_helper->dateFromMysql($customPackage->start_dt),
                "no_of_child" => $customPackage->child,
                "no_of_adult" => $customPackage->adult,
            );
            //if customdata parameters are already set when customizing package then these data will be populated in booking form
            $bookingForm->populate($customData);
            $this->view->package = $customPackage;
        }
        /*Enters into the process of booking, if the form data are valid and user is logged in then these data will be inserted into
         *nad_booking table,
         */
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost("submit") == "Next") {
            if ($auth->hasIdentity()) {
                if ($bookingForm->isValid($this->getRequest()->getPost())) {
                    unset($formData['request_uri']);
                    try {
                        $data = array();
                        unset($formData['submit']);
                        unset($formData['password']);
                        unset($formData['email']);
                        unset($formData['recaptcha_challenge_field']);
                        unset($formData['recaptcha_response_field']);
                        unset($formData['captcha']);
                        if ($element_dtl_id) {
                            $data['booking_type'] = 'ELEMENT';
                            $data['element_dtl_id'] = $element_dtl_id;
                        } elseif ($package_id) {
                            $data['booking_type'] = 'PACKAGE';
                            $data['package_id'] = $package_id;
                        } elseif ($event_id) {
                            $data['booking_type'] = 'EVENT';
                            $data['event_id'] = $event_id;
                        }
                        if (isset($data['booking_type'])) {
                            $user = $auth->getIdentity();
                            $data['user_id'] = $user->user_id;
                            $data['entered_by'] = $user->user_id;
                            //checks if that combination of user_id and package/event/element_detail is in database
                            $result = $bookingModel->isBooked($data);
                            //booking date is converted into mysql format with the help of action helper.
                            $formData['booking_dt'] = $this->_helper->dateToMysql($formData['booking_dt']);
                            $data += $formData;
                            if (!$result) {
                                $data['email_address'] = $user->email;
                                $lastId = $bookingModel->add($data);
                                if ($lastId) {
                                    $update = array('defined_id' => $this->_helper->bookingNumber($lastId));
                                    $bookingModel->update($update, $lastId);
                                    $bookingUserModel = new Default_Model_BookingUser();
                                    /*After successful insertion of these data into nad_booking, the logged in user is also
                                     *inserted into nad_booking_user
                                     */
                                    $firstBookingUserId = $bookingUserModel->addDefault($lastId);
                                }
                            } elseif ($result['status'] == 'D') {
                                //if it's this combination is in database however it's status is D then it will change it's status to E
                                $data += array(
                                    'status' => 'E',
                                    'entered_dt' => date('Y-m-d')
                                );
                                $bookingModel->update($data, $result['booking_id']);
                                $lastId = $result['booking_id'];
                            } else {
                                $msg = "Package already booked";
                                $this->_helper->FlashMessenger->addMessage(array('error' => $msg));
                            }
                        }
                        if ($lastId) {
                            $params = sprintf("booking_id=%s&encryption=%s", $lastId, rand());
                            $cipherQuery = $this->view->rijndael->encrypt($params);
                            $params = array('q' => $cipherQuery);
                            /*after successful completion of first stage system will redirect it to detail page
                             *in order to add detail information of other people who are coming with that person.
                             */
                            $this->_helper->redirector("book-detail", "serviceprovider", "default", $params);
                        }
                    } catch (Exception $e) {
                        $msg = $e->getMessage();
                        print 'An error occured while processing. Please contact the site adminstrator and try again.';
                        exit;
                    }
                }
            }
        }
    }

    public function editBookAction()
    {
        if ($this->_getParam("q")) {
            $params = $this->view->rijndael->decrypt($this->_getParam('q'));
            $arr = array();
            $str = parse_str($params, $arr);
            $bookingId = $arr['booking_id'];
        } else {
            $bookingId = $this->_getParam("booking_id");
        }
        if ($bookingId) {
            $bookingModel = new Default_Model_Booking();
            $booking = $bookingModel->getDetailById($bookingId);
            $booking['booking_dt'] = $this->_helper->dateFromMysql($booking['booking_dt']);
            if ($booking['element_dtl_id']) {
                $elementDtlModel = new Package_Model_ElementDetail();
                $elementDetail = $elementDtlModel->getElementDetailById($booking['element_dtl_id']);
                $this->view->elementDetail = $elementDetail;
            } elseif ($booking['package_id']) {
                $packageMstModel = new Package_Model_NadPackageMst();
                $package = (object) $packageMstModel->find($booking['package_id'])->toArray();
                $fixDepartureModel = new Package_Model_NadPackageFixed();
                $this->view->fixDates = $fixDepartureModel->getDetailById($booking['package_id']);
                $this->view->package = $package;
            } elseif ($booking['event_id']) {
                $userId = Zend_Auth::getInstance()->getIdentity()->user_id;
                $customPackage = '';
                $eventModel = new Package_Model_Event();
                $customizedPackage = $eventModel->fetchPackagesByUserId($userId, $booking['event_id']);
                if ($customizedPackage) {
                    $customPackage = $customizedPackage[0];
                }
                $this->view->package = $customPackage;
            }
            $form = new Default_Form_BookingForm();
            $form->populate($booking);
            $this->view->booking = $booking;
            $this->view->form = $form;
            if ($this->getRequest()->isPost() && $this->getRequest()->getPost("submit") == "Next") {
                $formData = $this->getRequest()->getPost();
                if ($form->isValid($formData)) {
                    unset($formData['booking_id']);
                    unset($formData['submit']);
                    unset($formData['request_uri']);
                    $formData['booking_dt'] = $this->_helper->dateToMysql($formData['booking_dt']);
                    $bookingModel->update($formData, $bookingId);
                    $params = sprintf("booking_id=%s&encryption=%s", $bookingId, rand());
                    $cipherQuery = $this->view->rijndael->encrypt($params);
                    $params = array('q' => $cipherQuery);
                    $this->_helper->redirector("book-detail", "serviceprovider", "default", $params);
                }
            }
        } else {
            $this->_helper->FlashMessenger->addMessage(array('error' => "You haven't choosen any packages to book."));
            $this->_helper->redirector("booked-trip");
        }
    }

    public function elementDetailAction()
    {
        $elementId = $this;
        $params = $this->view->rijndael->decrypt($this->getRequest()->getParam('q'));
        var_dump($params);
        exit;
    }

    public function detailAction()
    {
        $rijndael = new NepalAdvisor_Rijndael_Decrypt();
        $params = $rijndael->decrypt($this->getRequest()->getParam('q'));
        $arr = array();
        $str = parse_str($params, $arr);
        $elementDtlModel = new Package_Model_ElementDetail();
        $elementDetail = $elementDtlModel->getElementDetailById($arr['element_dtl_id']);
        $this->view->elementDetail = $elementDetail;
        /* to find out the related service near by that area */
        $related = array(
            'location_id' => $elementDetail->location_id,
            'element_id' => $elementDetail->element_id
        );
        if ($elementDetail->element_id == '6') {
            $related['to_location_id'] = $elementDetail->to_location_id;
        }

        $this->view->relatedServices = $rls = $elementDtlModel->getElementDetailById(null, $related, $this->limit);
        /* end to find out the related service nearby that area */
        $company_id = $arr['company_id'];
        if ($company_id) {
            $companyModel = new Company_Model_Company();
            $company = $companyModel->getDetailById($company_id);
            $this->view->company_name = $company['name'];
            $featureModel = new Company_Model_Feature();
            $features = $featureModel->fetchHierarchy($company['element_id']);
            $this->view->features = $features;
            $companyDetailModel = new Company_Model_Detail();
            $companyDetails = $companyDetailModel->fetchAll($company_id);
            $this->view->companyDetails = $companyDetails->toArray();
            $this->view->id = $company_id;
        }
    }

    public function bookedTripAction()
    {
        $this->view->contentClass = 'main-right-separator';

        $userId = Zend_Auth::getInstance()->getIdentity()->user_id;
        $bookingModel = new Default_Model_Booking();
        $results = $bookingModel->getAllByUserId($userId);
        $this->view->results = $results;
        //top selling holidays
        $packageMstModel = new Package_Model_NadPackageMst();
        $datas = $packageMstModel->getMapper()->getTopPackageDetails(8);
        $this->view->relatedServices = $datas;
    }

    public function cancelAction()
    {
        $params = $this->view->rijndael->decrypt($this->_getParam('q'));
        $arr = array();
        $str = parse_str($params, $arr);
        $bookingId = $arr['booking_id'];
        $bookingModel = new Default_Model_Booking();
        $bookingModel->cancelBook($bookingId);
        $this->_helper->redirector("booked-trip");
    }

    public function bookDetailAction()
    {
        $params = $this->view->rijndael->decrypt($this->_getParam('q'));
        $arr = array();
        $str = parse_str($params, $arr);
        if (empty($arr)) {
            $this->_helper->redirector("index", "index", "default");
        }
        $bookingId = $arr['booking_id'];

        $bookingModel = new Default_Model_Booking();
        $booking = $bookingModel->getDetailById($bookingId);
        if ($booking['element_dtl_id']) {
            $elementDtlModel = new Package_Model_ElementDetail();
            $elementDetail = $elementDtlModel->getElementDetailById($booking['element_dtl_id']);
            $this->view->elementDetail = $elementDetail;
        } elseif ($booking['package_id']) {
            $packageMstModel = new Package_Model_NadPackageMst();
            $package = (object) $packageMstModel->find($booking['package_id'])->toArray();
            $this->view->package = $package;
        } elseif ($booking['event_id']) {
            $userId = Zend_Auth::getInstance()->getIdentity()->user_id;
            $customPackage = '';
            $eventModel = new Package_Model_Event();
            $customizedPackage = $eventModel->fetchPackagesByUserId($userId, $booking['event_id']);
            if ($customizedPackage) {
                $customPackage = $customizedPackage[0];
            }
            $this->view->package = $customPackage;
        } else {
            throw new Exception("Invalid index in book detail.");
        }
        $number = $booking['no_of_children'] + $booking['no_of_adult'];
        $this->view->children = $booking['no_of_children'];
        $this->view->adult = $booking['no_of_adult'];
        $bookingUserModel = new Default_Model_BookingUser();
        $bookingUsers = $bookingUserModel->getBookingUsersByBookingId($bookingId);
        $booking_user = array();
        $i = 0;
        foreach ($bookingUsers as $members) {
            $members->dob = $this->_helper->dateFromMysql($members->dob);
            if ('Y' == $members->is_child) {
                continue;
            }
            foreach ($members as $key => $val) {
                $booking_user["bookings"][$i][$key] = $val;
            }
            $i++;
        }
        $i = $booking['no_of_adult'];
        foreach ($bookingUsers as $members) {
            if ('N' == $members->is_child) {
                continue;
            }
            foreach ($members as $key => $val) {
                $booking_user["bookings"][$i][$key] = $val;
            }
            $i++;
        }
        $form = new Default_Form_BookingDetailForm($number);
        $form->booking_id->setValue($booking['booking_id']);
        $form->populate($booking_user);
        $this->view->form = $form;
        $this->view->bookingId = $bookingId;
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost("submit") == "Next") {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $this->getRequest()->getPost();
                unset($formData['terms_and_conditions']);
                unset($formData["submit"]);
                foreach ($formData['bookings'] as $key => $data) {
                    $formData['bookings'][$key]['dob'] = $this->_helper->dateToMysql($formData['bookings'][$key]['dob']);
                }
                try {
                    $lastId = $bookingUserModel->add($formData);
                } catch (Exception $e) {
                    $this->_helper->FlashMessenger->addMessage(array('error' => $e->getMessage()));
                }
                $params = sprintf("booking_id=%s", $booking['booking_id']);
                $cipherQuery = $this->view->rijndael->encrypt($params);
                $params = array('q' => $cipherQuery);

                $this->_helper->redirector("checkout", "index", "payment", $params);
            }
        }
    }

    public function bookListAction()
    {
        $ids = explode('-', $this->view->rijndael->decrypt($this->_getParam('id')));
        $id = $ids[0];
        $cat = (isset($ids[1]) && $ids[1]) ? (int) $ids[1] : array();
        $chid = (isset($ids[2]) && $ids[2]) ? explode(',', $ids[2]) : array();
        
        $objModel = new Content_Model_ContentMapper;
        $companyModel = new Company_Model_Company();
        $eleDtlModel = new Package_Model_ElementDetail();
        
        $this->view->results = $objModel->getContentDetails($id);
        $company = $companyModel->getDetailByContentId($id);
        $this->view->element = $eleDtlModel->getElementDetailByCompany($company['company_id']);
        
        $contenttagModel = new Content_Model_NadContentTagMapMapper();
        $this->view->hotels = $contenttagModel->suggestedHotels($id);
    }

}

?>
