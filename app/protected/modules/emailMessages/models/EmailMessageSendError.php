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
     * A model for storing error information about a problem with sending an email message.
     */
    class EmailMessageSendError extends OwnedModel
    {
        public function __toString()
        {
            if ($this->serializedData != null)
            {
                $data   = unserialize($this->serializedData);
                $string = '';
                if (isset($data['code']))
                {
                    $string .= Zurmo::t('EmailMessagesModule', 'Error Code:') . ' ' . $data['code'];
                }
                if (isset($data['message']))
                {
                    if ($string != null)
                    {
                        $string .= "\n";
                    }
                    $string .= Zurmo::t('EmailMessagesModule', 'Error Message:') . ' ' . $data['message'];
                }
                return $string;
            }
            else
            {
                return Zurmo::t('EmailMessagesModule', '(Unnamed)');
            }
        }

        public function onCreated()
        {
            $this->unrestrictedSet('createdDateTime',  DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'createdDateTime',
                    'serializedData',
                ),
                'rules' => array(
                    array('createdDateTime', 'required'),
                    array('createdDateTime', 'type', 'type' => 'datetime'),
                    array('serializedData',  'required'),
                    array('serializedData',  'type', 'type' => 'string'),
                ),
                'noAudit' => array(
                    'createdDateTime',
                    'serializedData',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Send Error', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Send Errors', array(), null, $language);
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'createdDateTime'   => Zurmo::t('ZurmoModule', 'Created Date Time',  array(), null, $language),
                )
            );
        }
    }
?>