<?php
class Default_Form_BookingDetailForm extends Zend_Form
{
	protected $_param;
	public function __construct($param = 1)
    {
        $this->_param = $param;
        parent::__construct($param);
	}
    public function createElements($index)
    { 
        $subForm = new Zend_Form_SubForm();
		$bookingUserId = new Zend_Form_Element_Hidden("booking_user_id");
		$bookingUserId->setBelongsTo("bookings[{$index}]");
		$fullName = new Zend_Form_Element_Text("full_name");
        $fullName->setRequired(true)
				->setLabel("Full Name")
				->setRequired(true)
				->addValidator('NotEmpty', true, array("messages"=>"Full name can't be empty"))
				->setAttribs(array("class"=>"form-text booking-full-name"))
				->addValidator('regex', false, array('pattern'   => "#^[a-z0-9-.\x20]+$#i", 'messages' => 'Only alphanumeric, -, . and space are allowed'))
				->setBelongsTo("bookings[{$index}]");
        $email = new Zend_Form_Element_Text("email_address");
        $email->addValidator(new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_ALL))
				->setLabel("Email")
				->setAttribs(array("class"=>"form-text booking-email"))
				->setBelongsTo("bookings[{$index}]");
        $dob = new Zend_Form_Element_Text("dob");
        $dob->setLabel("Date of Birth")
			->setAttribs(array("class"=>"form-text booking-age","readonly"=>"readonly"))
            ->setRequired(false)
			//->addValidator('Date', false, array('dd MM, yyyy'))
			->addErrorMessage("Invalid date")
			->setBelongsTo("bookings[{$index}]");
        $isChild = new Zend_Form_Element_Hidden("is_child");
		
        $restriction = new Zend_Form_Element_Text("restriction");
        $restriction->setLabel("Any Restriction")
				->setAttribs(array("class"=>"form-text booking-restriction"))
				->addValidator("Alnum")
				->setBelongsTo("bookings[{$index}]");
        
        $formElements = array(
		  $bookingUserId,
		  $fullName,
          $email,
          $dob,
		  $restriction,
        );
		$val = $index+1;
		$subForm->addElements($formElements)
						->setLegend("Person {$val} Information ");
		$subForm->setElementDecorators(array('viewHelper',"Errors"));
		 
		$isChild->setDecorators(array("ViewHelper"));
		$bookingUserId->setDecorators(array("ViewHelper"));
		return $subForm;
    }    
    public function init()
    {
		for($index=0; $index<$this->_param; $index++){
            $subForm = $this->createElements($index);
			$this->addSubForms(array(
				"booking_user[{$index}]"  => $subForm
			 ));
        }
		$bookingId = new Zend_Form_Element_Hidden("booking_id");
		$submit = new Zend_Form_Element_Submit("submit");
		$submit->setLabel("Next")
			   ->setAttribs(array("id"=>"signin_submit"));
		$siteUrl  = Zend_Controller_Front::getInstance()->getBaseUrl();
		$termsUrl = '<a target="_blank" href="' . $siteUrl . '/termsandconditions#booking-terms-and-conditions">terms and conditions</a>';
        $privacyUrl = '<a target="_blank" href="' . $siteUrl . '/privacy">privacy policy</a>';
		$terms = new Zend_Form_Element_MultiCheckbox('terms_and_conditions');
		$terms->setLabel("I have read and agreed to the $termsUrl and $privacyUrl of nepaladvisor")
				->setRequired(true)
				->addValidator('NotEmpty', true, array("messages"=>"You need to agree terms and conditions by clicking on above checkbox"))
				->addMultiOption("Y","");
		$bookingId->setDecorators(array("ViewHelper"));
		$this->addElements(array($bookingId, $terms, $submit));
		$this->setElementDecorators(array(
            'viewHelper',
			'Description',
            'Errors',
			array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clear input-wrapper')),
            array('Label', array()),
			array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clear booking-detail-item'))
         ));
		$terms->setDecorators(array(
            'viewHelper',
            'Errors',array('Label', array('escape' => false))
        ));
		//$terms->getDecorator('Label')->setOption('escape', false);

		$submit->setDecorators(array(
            'viewHelper',
            'Errors',
        ));
		$submit->removeDecorator("label");
    }
}
