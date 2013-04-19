<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
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
                    'endDateTime',
                    'isProcessed',
                    'message',
                    'startDateTime',
                    'status',
                    'type'
                ),
                'rules' => array(
                    array('endDateTime',    'required'),
                    array('endDateTime',    'type', 'type' => 'datetime'),
                    array('isProcessed',    'boolean'),
                    array('isProcessed',    'validateIsProcessedIsSet'),
                    array('message',        'type',   'type' => 'string'),
                    array('startDateTime',  'required'),
                    array('status',         'required'),
                    array('status',         'type',   'type' => 'integer'),
                    array('startDateTime',  'type', 'type' => 'datetime'),
                    array('type',           'required'),
                    array('type',           'type',   'type' => 'string'),
                    array('type',           'length', 'min'  => 3, 'max' => 64),
                ),
                'defaultSortAttribute' => 'type',
                'noAudit' => array(
                    'endDateTime',
                    'message',
                    'startDateTime',
                    'status',
                    'type'
                ),
                'elements' => array(
                    'description'     => 'TextArea',
                    'endDateTimex'    => 'DateTime',
                    'startDateTime'   => 'DateTime',
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
                $this->addError('isProcessed', Zurmo::t('JobsManagerModule', 'Is Processed must be set as true or false, not null.'));
            }
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('JobsManagerModule', 'Job Log', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('JobsManagerModule', 'Job Logs', array(), null, $language);
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'endDateTime'   => Zurmo::t('ZurmoModule',       'End Date Time',  array(), null, $language),
                    'isProcessed'   => Zurmo::t('JobsManagerModule', 'Is Processed',  array(), null, $language),
                    'message'       => Zurmo::t('JobsManagerModule', 'Message',  array(), null, $language),
                    'startDateTIme' => Zurmo::t('ZurmoModule',       'Start Date Time',  array(), null, $language),
                    'status'        => Zurmo::t('JobsManagerModule', 'Status',  array(), null, $language),
                    'type'          => Zurmo::t('Core',              'Type',  array(), null, $language),
                )
            );
        }
    }
?>