<?php

/**
 * Application Models
 *
 * @package Default_Model
 * @subpackage Model
 * @author Himal Rai
 * @copyright ZF model generator
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * 
 *
 * @package Default_Model
 * @subpackage Model
 * @author Himal Rai
 */
class Default_Model_NadBookingUser extends NepalAdvisor_Zfdmg_Model_DbTable_TableAbstract
{

    /**
     * Database var type int(20)
     *
     * @var int
     */
    protected $_BookingUserId;

    /**
     * Database var type int(20)
     *
     * @var int
     */
    protected $_BookingId;

    /**
     * Database var type int(20)
     *
     * @var int
     */
    protected $_LanguageId;

    /**
     * Database var type varchar(200)
     *
     * @var string
     */
    protected $_FullName;

    /**
     * Database var type varchar(100)
     *
     * @var string
     */
    protected $_EmailAddress;

    /**
     * Database var type int(2)
     *
     * @var int
     */
    protected $_Age;

    /**
     * Database var type int(20)
     *
     * @var int
     */
    protected $_CountryId;

    /**
     * Database var type enum('MALE','FEMALE','OTHER')
     *
     * @var string
     */
    protected $_Gender;

    /**
     * Database var type varchar(50)
     *
     * @var string
     */
    protected $_DietRestriction;

    /**
     * Database var type varchar(50)
     *
     * @var string
     */
    protected $_MedicalRestriction;

    /**
     * Database var type enum('Y','N')
     *
     * @var string
     */
    protected $_TravelInsurance;

    /**
     * Database var type enum('Y','N')
     *
     * @var string
     */
    protected $_MedicalInsurance;

    /**
     * Database var type enum('D','E')
     *
     * @var string
     */
    protected $_Status;

    /**
     * Database var type varchar(30)
     *
     * @var string
     */
    protected $_EnteredBy;

    /**
     * Database var type date
     *
     * @var string
     */
    protected $_EnteredDt;

    /**
     * Database var type enum('Y','N')
     *
     * @var string
     */
    protected $_Checked;

    /**
     * Database var type varchar(30)
     *
     * @var string
     */
    protected $_CheckedBy;

    /**
     * Database var type date
     *
     * @var string
     */
    protected $_CheckedDt;

    /**
     * Database var type enum('Y','N')
     *
     * @var string
     */
    protected $_Approved;

    /**
     * Database var type varchar(30)
     *
     * @var string
     */
    protected $_ApprovedBy;

    /**
     * Database var type date
     *
     * @var string
     */
    protected $_ApprovedDt;


    /**
     * Parent relation fk_nad_booking_user1
     *
     * @var Default_Model_NadBooking
     */
    protected $_Booking;

    /**
     * Parent relation fk_nad_booking_user2
     *
     * @var Default_Model_NadLanguageMst
     */
    protected $_Language;

    /**
     * Parent relation fk_nad_booking_user3
     *
     * @var Default_Model_NadCountryMst
     */
    protected $_Country;


    /**
     * Sets up column and relationship lists
     */
    public function __construct()
    {
        parent::init();
        $this->setColumnsList(array(
            'booking_user_id'=>'BookingUserId',
            'booking_id'=>'BookingId',
            'language_id'=>'LanguageId',
            'full_name'=>'FullName',
            'email_address'=>'EmailAddress',
            'age'=>'Age',
            'country_id'=>'CountryId',
            'gender'=>'Gender',
            'diet_restriction'=>'DietRestriction',
            'medical_restriction'=>'MedicalRestriction',
            'travel_insurance'=>'TravelInsurance',
            'medical_insurance'=>'MedicalInsurance',
            'status'=>'Status',
            'entered_by'=>'EnteredBy',
            'entered_dt'=>'EnteredDt',
            'checked'=>'Checked',
            'checked_by'=>'CheckedBy',
            'checked_dt'=>'CheckedDt',
            'approved'=>'Approved',
            'approved_by'=>'ApprovedBy',
            'approved_dt'=>'ApprovedDt',
        ));

        $this->setParentList(array(
            'FkNadBookingUser1'=> array(
                    'property' => 'Booking',
                    'table_name' => 'NadBooking',
                ),
            'FkNadBookingUser2'=> array(
                    'property' => 'Language',
                    'table_name' => 'NadLanguageMst',
                ),
            'FkNadBookingUser3'=> array(
                    'property' => 'Country',
                    'table_name' => 'NadCountryMst',
                ),
        ));

        $this->setDependentList(array(
        ));
    }

    /**
     * Sets column booking_user_id
     *
     * @param int $data
     * @return Default_Model_NadBookingUser
     */
    public function setBookingUserId($data)
    {
        $this->_BookingUserId = $data;
        return $this;
    }

    /**
     * Gets column booking_user_id
     *
     * @return int
     */
    public function getBookingUserId()
    {
        return $this->_BookingUserId;
    }

    /**
     * Sets column booking_id
     *
     * @param int $data
     * @return Default_Model_NadBookingUser
     */
    public function setBookingId($data)
    {
        $this->_BookingId = $data;
        return $this;
    }

    /**
     * Gets column booking_id
     *
     * @return int
     */
    public function getBookingId()
    {
        return $this->_BookingId;
    }

    /**
     * Sets column language_id
     *
     * @param int $data
     * @return Default_Model_NadBookingUser
     */
    public function setLanguageId($data)
    {
        $this->_LanguageId = $data;
        return $this;
    }

    /**
     * Gets column language_id
     *
     * @return int
     */
    public function getLanguageId()
    {
        return $this->_LanguageId;
    }

    /**
     * Sets column full_name
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setFullName($data)
    {
        $this->_FullName = $data;
        return $this;
    }

    /**
     * Gets column full_name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->_FullName;
    }

    /**
     * Sets column email_address
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setEmailAddress($data)
    {
        $this->_EmailAddress = $data;
        return $this;
    }

    /**
     * Gets column email_address
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->_EmailAddress;
    }

    /**
     * Sets column age
     *
     * @param int $data
     * @return Default_Model_NadBookingUser
     */
    public function setAge($data)
    {
        $this->_Age = $data;
        return $this;
    }

    /**
     * Gets column age
     *
     * @return int
     */
    public function getAge()
    {
        return $this->_Age;
    }

    /**
     * Sets column country_id
     *
     * @param int $data
     * @return Default_Model_NadBookingUser
     */
    public function setCountryId($data)
    {
        $this->_CountryId = $data;
        return $this;
    }

    /**
     * Gets column country_id
     *
     * @return int
     */
    public function getCountryId()
    {
        return $this->_CountryId;
    }

    /**
     * Sets column gender
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setGender($data)
    {
        $this->_Gender = $data;
        return $this;
    }

    /**
     * Gets column gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->_Gender;
    }

    /**
     * Sets column diet_restriction
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setDietRestriction($data)
    {
        $this->_DietRestriction = $data;
        return $this;
    }

    /**
     * Gets column diet_restriction
     *
     * @return string
     */
    public function getDietRestriction()
    {
        return $this->_DietRestriction;
    }

    /**
     * Sets column medical_restriction
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setMedicalRestriction($data)
    {
        $this->_MedicalRestriction = $data;
        return $this;
    }

    /**
     * Gets column medical_restriction
     *
     * @return string
     */
    public function getMedicalRestriction()
    {
        return $this->_MedicalRestriction;
    }

    /**
     * Sets column travel_insurance
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setTravelInsurance($data)
    {
        $this->_TravelInsurance = $data;
        return $this;
    }

    /**
     * Gets column travel_insurance
     *
     * @return string
     */
    public function getTravelInsurance()
    {
        return $this->_TravelInsurance;
    }

    /**
     * Sets column medical_insurance
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setMedicalInsurance($data)
    {
        $this->_MedicalInsurance = $data;
        return $this;
    }

    /**
     * Gets column medical_insurance
     *
     * @return string
     */
    public function getMedicalInsurance()
    {
        return $this->_MedicalInsurance;
    }

    /**
     * Sets column status
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setStatus($data)
    {
        $this->_Status = $data;
        return $this;
    }

    /**
     * Gets column status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_Status;
    }

    /**
     * Sets column entered_by
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setEnteredBy($data)
    {
        $this->_EnteredBy = $data;
        return $this;
    }

    /**
     * Gets column entered_by
     *
     * @return string
     */
    public function getEnteredBy()
    {
        return $this->_EnteredBy;
    }

    /**
     * Sets column entered_dt
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setEnteredDt($data)
    {
        $this->_EnteredDt = $data;
        return $this;
    }

    /**
     * Gets column entered_dt
     *
     * @return string
     */
    public function getEnteredDt()
    {
        return $this->_EnteredDt;
    }

    /**
     * Sets column checked
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setChecked($data)
    {
        $this->_Checked = $data;
        return $this;
    }

    /**
     * Gets column checked
     *
     * @return string
     */
    public function getChecked()
    {
        return $this->_Checked;
    }

    /**
     * Sets column checked_by
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setCheckedBy($data)
    {
        $this->_CheckedBy = $data;
        return $this;
    }

    /**
     * Gets column checked_by
     *
     * @return string
     */
    public function getCheckedBy()
    {
        return $this->_CheckedBy;
    }

    /**
     * Sets column checked_dt
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setCheckedDt($data)
    {
        $this->_CheckedDt = $data;
        return $this;
    }

    /**
     * Gets column checked_dt
     *
     * @return string
     */
    public function getCheckedDt()
    {
        return $this->_CheckedDt;
    }

    /**
     * Sets column approved
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setApproved($data)
    {
        $this->_Approved = $data;
        return $this;
    }

    /**
     * Gets column approved
     *
     * @return string
     */
    public function getApproved()
    {
        return $this->_Approved;
    }

    /**
     * Sets column approved_by
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setApprovedBy($data)
    {
        $this->_ApprovedBy = $data;
        return $this;
    }

    /**
     * Gets column approved_by
     *
     * @return string
     */
    public function getApprovedBy()
    {
        return $this->_ApprovedBy;
    }

    /**
     * Sets column approved_dt
     *
     * @param string $data
     * @return Default_Model_NadBookingUser
     */
    public function setApprovedDt($data)
    {
        $this->_ApprovedDt = $data;
        return $this;
    }

    /**
     * Gets column approved_dt
     *
     * @return string
     */
    public function getApprovedDt()
    {
        return $this->_ApprovedDt;
    }

    /**
     * Sets parent relation Booking
     *
     * @param Default_Model_NadBooking $data
     * @return Default_Model_NadBookingUser
     */
    public function setBooking(Default_Model_NadBooking $data)
    {
        $this->_Booking = $data;

        $primary_key = $data->getPrimaryKey();
        if (is_array($primary_key)) {
            $primary_key = $primary_key['booking_id'];
        }

        $this->setBookingId($primary_key);

        return $this;
    }

    /**
     * Gets parent Booking
     *
     * @param boolean $load Load the object if it is not already
     * @return Default_Model_NadBooking
     */
    public function getBooking($load = true)
    {
        if ($this->_Booking === null && $load) {
            $this->getMapper()->loadRelated('FkNadBookingUser1', $this);
        }

        return $this->_Booking;
    }

    /**
     * Sets parent relation Language
     *
     * @param Default_Model_NadLanguageMst $data
     * @return Default_Model_NadBookingUser
     */
    public function setLanguage(Default_Model_NadLanguageMst $data)
    {
        $this->_Language = $data;

        $primary_key = $data->getPrimaryKey();
        if (is_array($primary_key)) {
            $primary_key = $primary_key['language_id'];
        }

        $this->setLanguageId($primary_key);

        return $this;
    }

    /**
     * Gets parent Language
     *
     * @param boolean $load Load the object if it is not already
     * @return Default_Model_NadLanguageMst
     */
    public function getLanguage($load = true)
    {
        if ($this->_Language === null && $load) {
            $this->getMapper()->loadRelated('FkNadBookingUser2', $this);
        }

        return $this->_Language;
    }

    /**
     * Sets parent relation Country
     *
     * @param Default_Model_NadCountryMst $data
     * @return Default_Model_NadBookingUser
     */
    public function setCountry(Default_Model_NadCountryMst $data)
    {
        $this->_Country = $data;

        $primary_key = $data->getPrimaryKey();
        if (is_array($primary_key)) {
            $primary_key = $primary_key['country_id'];
        }

        $this->setCountryId($primary_key);

        return $this;
    }

    /**
     * Gets parent Country
     *
     * @param boolean $load Load the object if it is not already
     * @return Default_Model_NadCountryMst
     */
    public function getCountry($load = true)
    {
        if ($this->_Country === null && $load) {
            $this->getMapper()->loadRelated('FkNadBookingUser3', $this);
        }

        return $this->_Country;
    }

    /**
     * Returns the mapper class for this model
     *
     * @return Default_Model_Mapper_NadBookingUser
     */
    public function getMapper()
    {
        if ($this->_mapper === null) {
            $this->setMapper(new Default_Model_Mapper_NadBookingUser());
        }

        return $this->_mapper;
    }

    /**
     * Deletes current row by deleting the row that matches the primary key
     *
	 * @see Default_Model_Mapper_NadBookingUser::delete
     * @return int|boolean Number of rows deleted or boolean if doing soft delete
     */
    public function deleteRowByPrimaryKey()
    {
        if ($this->getBookingUserId() === null) {
            throw new Exception('Primary Key does not contain a value');
        }

        return $this->getMapper()
                    ->getDbTable()
                    ->delete('booking_user_id = ' .
                             $this->getMapper()
                                  ->getDbTable()
                                  ->getAdapter()
                                  ->quote($this->getBookingUserId()));
    }
}
