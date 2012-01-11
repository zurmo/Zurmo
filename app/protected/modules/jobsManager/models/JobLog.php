<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * A class to store historical information on past jobs that have run.
     */
    class JobLog extends Item
    {
        /**
         * Utilized by the status attribute to define the status as complete without an error.
         * @var integer
         */
        const STATUS_COMPLETE_WITHOUT_ERROR = 1;

        /**
         * Utilized by the status attribute to define the status as complet with an error.
         * @var integer
         */
        const STATUS_COMPLETE_WITH_ERROR    = 2;

        public function __toString()
        {
            if ($this->type == null)
            {
                return null;
            }
            return JobsUtil::resolveStringContentByType($this->type);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'type',
                    'startDateTime',
                    'endDateTime',
                    'status',
                    'message',
                    'isProcessed'
                ),
                'rules' => array(

                    array('type',           'required'),
                    array('startDateTime',  'required'),
                    array('endDateTime',    'required'),
                    array('status',         'required'),
                    array('type',           'type',   'type' => 'string'),
                    array('type',           'length', 'min'  => 3, 'max' => 64),
                    array('status',         'type',   'type' => 'integer'),
                    array('message',        'type',   'type' => 'string'),
                    array('startDateTime',  'type', 'type' => 'datetime'),
                    array('endDateTime',    'type', 'type' => 'datetime'),
                    array('isProcessed',    'boolean'),
                    array('isProcessed',    'validateIsProcessedIsSet'),
                ),
                'defaultSortAttribute' => 'type',
                'noAudit' => array(
                    'type',
                    'startDateTime',
                    'endDateTime',
                    'status',
                    'message'
                ),
                'elements' => array(
                    'startDateTime'   => 'DateTime',
                    'endDateTimex'    => 'DateTime',
                    'description'     => 'TextArea',
                ),
            );
            return $metadata;
        }

        /**
         * Because isProcessed is a boolean attribute, disallow if the value is not specified. We do
         * not want NULL values in the database for this attribute.
         */
        public function validateIsProcessedIsSet()
        {
            if ($this->isProcessed == null)
            {
                $this->addError('isProcessed', Yii::t('Default', 'Is Processed must be set as true or false, not null.'));
            }
        }

        public static function isTypeDeletable()
        {
            return true;
        }
    }
?>