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
				->setAttribs(array("class"=>"form-text","size"=>"22"))
				->setBelongsTo("bookings[{$index}]");
        $email = new Zend_Form_Element_Text("email_address");
        $email->setRequired(true)
				->addValidator("EmailAddress")
				->setLabel("Email")
				->setAttribs(array("class"=>"form-text","size"=>"22"))
				->setBelongsTo("bookings[{$index}]");
        $age = new Zend_Form_Element_Text("age");
        $age->setLabel("Age")
			->setAttribs(array("class"=>"form-text","size"=>"22"))
			->setBelongsTo("bookings[{$index}]");
        	
        $travelIns = new Zend_Form_Element_Radio("travel_insurance");
        $travelIns->setLabel("Travel Insurance?")
				->setRequired(true)
                ->addMultiOptions(array("Y"=>"Yes","N"=>"No"))
				->setAttribs(array("class"=>"form-radio"))
				->setBelongsTo("bookings[{$index}]");
				
		$medicalIns = new Zend_Form_Element_Radio("medical_insurance");
        $medicalIns->setLabel("Medical Insurance?")
				->setRequired(true)
				->addMultiOptions(array("Y"=>"Yes","N"=>"No"))
                ->setAttribs(array("class"=>"form-radio"))
				->setBelongsTo("bookings[{$index}]");
        $dietRestriction = new Zend_Form_Element_Text("diet_restriction");
        $dietRestriction->setLabel("Diet Restriction")
				->setAttribs(array("class"=>"form-text","size"=>"22","placeholder"=>"vegetarian/non-vegetarian"))
				->setBelongsTo("bookings[{$index}]");
        
        $medicalRestriction = new Zend_Form_Element_Text("medical_restriction");
        $medicalRestriction->setLabel("Medical Restriction")
				->setAttribs(array("class"=>"form-text","size"=>"22","placeholder"=>"any medical problems?"))
				->setBelongsTo("bookings[{$index}]");
				
        
        $formElements = array(
		  $bookingUserId,
		  $fullName,
          $email,
          $age,
          $travelIns,
          $medicalIns,
          $dietRestriction,
          $medicalRestriction,
        );
		$val = $index+1;
		$subForm->addElements($formElements)
						->setLegend("Person {$val} Information ");
		$subForm->setElementDecorators(array(
            'viewHelper',
			'Description',
            'Errors',
			array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clear input-wrapper')),
            array('Label', array()),
			array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clear booking-detail-item'))
         ));
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
		$submit->setLabel("Save")
			   ->setAttribs(array("id"=>"signin_submit"));
		$bookingId->setDecorators(array("ViewHelper"));
		$this->addElements(array($bookingId, $submit));
		$this->setElementDecorators(array(
            'viewHelper',
			'Description',
            'Errors',
			array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clear input-wrapper')),
            array('Label', array()),
			array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'clear booking-detail-item'))
         ));
		$submit->setDecorators(array(
            'viewHelper',
            'Errors',
            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'submit-booking-wrapper'))
        ));
		$submit->removeDecorator("label");
    }
}