<?php

/**
 * Application Model DbTables
 *
 * @package Default_Model
 * @subpackage DbTable
 * @author Himal Rai
 * @copyright ZF model generator
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Table definition for nad_booking_user
 *
 * @package Default_Model
 * @subpackage DbTable
 * @author Himal Rai
 */
class Default_Model_DbTable_NadBookingUser extends NepalAdvisor_Zfdmg_Model_DbTable_TableAbstract
{
    /**
     * $_name - name of database table
     *
     * @var string
     */
    protected $_name = 'nad_booking_user';

    /**
     * $_id - this is the primary key name
     *
     * @var int
     */
    protected $_id = 'booking_user_id';

    protected $_sequence = true;

    protected $_referenceMap = array(
        'FkNadBookingUser1' => array(
          	'columns' => 'booking_id',
            'refTableClass' => 'Default_Model_DbTable_NadBooking',
            'refColumns' => 'booking_id'
        ),
        'FkNadBookingUser2' => array(
          	'columns' => 'language_id',
            'refTableClass' => 'Default_Model_DbTable_NadLanguageMst',
            'refColumns' => 'language_id'
        ),
        'FkNadBookingUser3' => array(
          	'columns' => 'country_id',
            'refTableClass' => 'Default_Model_DbTable_NadCountryMst',
            'refColumns' => 'country_id'
        )
    );
    



}
