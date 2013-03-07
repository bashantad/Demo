<?php

/**
 * Application Model Mappers
 *
 * @package Default_Model
 * @subpackage Mapper
 * @author Himal Rai
 * @copyright ZF model generator
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Data Mapper implementation for Default_Model_NadBookingUser
 *
 * @package Default_Model
 * @subpackage Mapper
 * @author Himal Rai
 */
class Default_Model_Mapper_NadBookingUser extends NepalAdvisor_Zfdmg_Model_Mapper_MapperAbstract
{
    /**
     * Returns an array, keys are the field names.
     *
     * @param Default_Model_NadBookingUser $model
     * @return array
     */
    public function toArray($model)
    {
        if (! $model instanceof Default_Model_NadBookingUser) {
            throw new Exception('Unable to create array: invalid model passed to mapper');
        }

        $result = array(
            'booking_user_id' => $model->getBookingUserId(),
            'booking_id' => $model->getBookingId(),
            'language_id' => $model->getLanguageId(),
            'full_name' => $model->getFullName(),
            'email_address' => $model->getEmailAddress(),
            'age' => $model->getAge(),
            'country_id' => $model->getCountryId(),
            'gender' => $model->getGender(),
            'diet_restriction' => $model->getDietRestriction(),
            'medical_restriction' => $model->getMedicalRestriction(),
            'travel_insurance' => $model->getTravelInsurance(),
            'medical_insurance' => $model->getMedicalInsurance(),
            'status' => $model->getStatus(),
            'entered_by' => $model->getEnteredBy(),
            'entered_dt' => $model->getEnteredDt(),
            'checked' => $model->getChecked(),
            'checked_by' => $model->getCheckedBy(),
            'checked_dt' => $model->getCheckedDt(),
            'approved' => $model->getApproved(),
            'approved_by' => $model->getApprovedBy(),
            'approved_dt' => $model->getApprovedDt(),
        );

        return $result;
    }

    /**
     * Returns the DbTable class associated with this mapper
     *
     * @return Default_Model_DbTable_NadBookingUser
     */
    public function getDbTable()
    {
        if ($this->_dbTable === null) {
            $this->setDbTable('Default_Model_DbTable_NadBookingUser');
        }

        return $this->_dbTable;
    }

    /**
     * Deletes the current model
     *
     * @param Default_Model_NadBookingUser $model The model to delete
     * @see Default_Model_DbTable_TableAbstract::delete()
     * @return int
     */
    public function delete($model)
    {
        if (! $model instanceof Default_Model_NadBookingUser) {
            throw new Exception('Unable to delete: invalid model passed to mapper');
        }

        $this->getDbTable()->getAdapter()->beginTransaction();
        try {
            $where = $this->getDbTable()->getAdapter()->quoteInto('booking_user_id = ?', $model->getBookingUserId());
            $result = $this->getDbTable()->delete($where);

            $this->getDbTable()->getAdapter()->commit();
        } catch (Exception $e) {
            $this->getDbTable()->getAdapter()->rollback();
            $result = false;
        }

        return $result;
    }

    /**
     * Saves current row, and optionally dependent rows
     *
     * @param Default_Model_NadBookingUser $model
     * @param boolean $ignoreEmptyValues Should empty values saved
     * @param boolean $recursive Should the object graph be walked for all related elements
     * @param boolean $useTransaction Flag to indicate if save should be done inside a database transaction
     * @return boolean If the save action was successful
     */
    public function save(Default_Model_NadBookingUser $model,
        $ignoreEmptyValues = true, $recursive = false, $useTransaction = true
    ) {
        $data = $model->toArray();
        if ($ignoreEmptyValues) {
            foreach ($data as $key => $value) {
                if ($value === null or $value === '') {
                    unset($data[$key]);
                }
            }
        }

        $primary_key = $model->getBookingUserId();
        $success = true;

        if ($useTransaction) {
            $this->getDbTable()->getAdapter()->beginTransaction();
        }

        unset($data['booking_user_id']);

        try {
            if ($primary_key === null) {
                $primary_key = $this->getDbTable()->insert($data);
                if ($primary_key) {
                    $model->setBookingUserId($primary_key);
                } else {
                    $success = false;
                }
            } else {
                $this->getDbTable()
                     ->update($data,
                              array(
                                 'booking_user_id = ?' => $primary_key
                              )
                );
            }

            if ($useTransaction && $success) {
                $this->getDbTable()->getAdapter()->commit();
            } elseif ($useTransaction) {
                $this->getDbTable()->getAdapter()->rollback();
            }

        } catch (Exception $e) {
            if ($useTransaction) {
                $this->getDbTable()->getAdapter()->rollback();
            }

            $success = false;
        }

        return $success;
    }

    /**
     * Finds row by primary key
     *
     * @param int $primary_key
     * @param Default_Model_NadBookingUser|null $model
     * @return Default_Model_NadBookingUser|null The object provided or null if not found
     */
    public function find($primary_key, $model)
    {
        $result = $this->getRowset($primary_key);

        if (is_null($result)) {
            return null;
        }

        $row = $result->current();

        $model = $this->loadModel($row, $model);

        return $model;
    }

    /**
     * Loads the model specific data into the model object
     *
     * @param Zend_Db_Table_Row_Abstract|array $data The data as returned from a Zend_Db query
     * @param Default_Model_NadBookingUser|null $entry The object to load the data into, or null to have one created
     * @return Default_Model_NadBookingUser The model with the data provided
     */
    public function loadModel($data, $entry)
    {
        if ($entry === null) {
            $entry = new Default_Model_NadBookingUser();
        }

        if (is_array($data)) {
            $entry->setBookingUserId($data['booking_user_id'])
                  ->setBookingId($data['booking_id'])
                  ->setLanguageId($data['language_id'])
                  ->setFullName($data['full_name'])
                  ->setEmailAddress($data['email_address'])
                  ->setAge($data['age'])
                  ->setCountryId($data['country_id'])
                  ->setGender($data['gender'])
                  ->setDietRestriction($data['diet_restriction'])
                  ->setMedicalRestriction($data['medical_restriction'])
                  ->setTravelInsurance($data['travel_insurance'])
                  ->setMedicalInsurance($data['medical_insurance'])
                  ->setStatus($data['status'])
                  ->setEnteredBy($data['entered_by'])
                  ->setEnteredDt($data['entered_dt'])
                  ->setChecked($data['checked'])
                  ->setCheckedBy($data['checked_by'])
                  ->setCheckedDt($data['checked_dt'])
                  ->setApproved($data['approved'])
                  ->setApprovedBy($data['approved_by'])
                  ->setApprovedDt($data['approved_dt']);
        } elseif ($data instanceof Zend_Db_Table_Row_Abstract || $data instanceof stdClass) {
            $entry->setBookingUserId($data->booking_user_id)
                  ->setBookingId($data->booking_id)
                  ->setLanguageId($data->language_id)
                  ->setFullName($data->full_name)
                  ->setEmailAddress($data->email_address)
                  ->setAge($data->age)
                  ->setCountryId($data->country_id)
                  ->setGender($data->gender)
                  ->setDietRestriction($data->diet_restriction)
                  ->setMedicalRestriction($data->medical_restriction)
                  ->setTravelInsurance($data->travel_insurance)
                  ->setMedicalInsurance($data->medical_insurance)
                  ->setStatus($data->status)
                  ->setEnteredBy($data->entered_by)
                  ->setEnteredDt($data->entered_dt)
                  ->setChecked($data->checked)
                  ->setCheckedBy($data->checked_by)
                  ->setCheckedDt($data->checked_dt)
                  ->setApproved($data->approved)
                  ->setApprovedBy($data->approved_by)
                  ->setApprovedDt($data->approved_dt);
        }

        $entry->setMapper($this);

        return $entry;
    }
}
