<?php
class Default_Form_BookingForm extends Zend_Form
{
    public function init()
    {
		$bookingId = new Zend_Form_Element_Hidden("booking_id");
        $adultOptions = array(""=>"No of adults");
        $childrenOptions = array(""=>"No of childrens");
        $noRooms = array(""=>"Quantity");
        for($i=1;$i<=10;$i++){
            $adultOptions[$i] = $i;
			$noRooms[$i] = $i;
			$childrenOptions[$i] = $i;
        }
		$bookingDate = new Zend_Form_Element_Text("booking_dt");
		$bookingDate->setAttribs(array("placeholder"=>"exact start date","id"=>"fromdate","readonly"=>"readonly"))
					->addValidator('NotEmpty', true, array("messages"=>"Please enter booking date"))
					->setRequired(true);
        $noOfAdults = new Zend_Form_Element_Select("no_of_adult");
        $noOfAdults->setRequired(true)
				->addValidator('NotEmpty', true, array("messages"=>"Please choose atleast one adult"))
			    ->addMultiOptions($adultOptions)
				->setAttribs(array("class"=>"form-select"));
        $noOfChildrens = new Zend_Form_Element_Select("no_of_children");
        $noOfChildrens->addMultiOptions($childrenOptions)
                ->setAttribs(array("class"=>"form-select"));
        $submit = new Zend_Form_Element_Submit("submit");
		
        $submit->setAttribs(array("id"=>"signin_submit","class"=>"book-submit"))
                ->setLabel("Next");
        
        $formElements = array(
		  $bookingId,
		  $bookingDate,
		  $noOfAdults,
		  $noOfChildrens,
		  $submit
        );
        $this->addElements($formElements);
        $this->setElementDecorators(array("ViewHelper","Errors"));
        $submit->removeDecorator("label");
    }
}