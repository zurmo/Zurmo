<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
                    $string .= Yii::t('Default', 'Error Code:') . ' ' . $data['code'];
                }
                if (isset($data['message']))
                {
                    if ($string != null)
                    {
                        $string .= "\n";
                    }
                    $string .= Yii::t('Default', 'Error Message:') . ' ' . $data['message'];
                }
                return $string;
            }
            else
            {
                return Yii::t('Default', '(Unnamed)');
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
                    array('createdDateTime',       'required'),
                    array('createdDateTime',       'type', 'type' => 'datetime'),
                    array('serializedData', 'required'),
                    array('serializedData', 'type', 'type' => 'string'),
                )
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
    }
?>