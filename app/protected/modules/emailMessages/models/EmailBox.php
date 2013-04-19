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
     * Model for storing email boxes.
     */
    class EmailBox extends Item
    {
        const NOTIFICATIONS_NAME   = 'System Notifications';

        const USER_DEFAULT_NAME    = 'Default';

        protected $isNotifications = false;

        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            $bean = R::findOne(EmailBox::getTableName('EmailBox'), "name = :name ", array(':name' => $name));
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            else
            {
                $box = self::makeModel($bean);
            }
            $box->setSpecialBox();
            return $box;
        }

        public static function resolveAndGetByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            try
            {
                $box = static::getByName($name);
            }
            catch (NotFoundException $e)
            {
                if ($name == self::NOTIFICATIONS_NAME)
                {
                    $box = new EmailBox();
                    $box->name        = self::NOTIFICATIONS_NAME;
                    $folder           = new EmailFolder();
                    $folder->name     = EmailFolder::getDefaultDraftName();
                    $folder->type     = EmailFolder::TYPE_DRAFT;
                    $folder->emailBox = $box;
                    $box->folders->add($folder);
                    $folder           = new EmailFolder();
                    $folder->name     = EmailFolder::getDefaultSentName();
                    $folder->type     = EmailFolder::TYPE_SENT;
                    $folder->emailBox = $box;
                    $box->folders->add($folder);
                    $folder           = new EmailFolder();
                    $folder->name     = EmailFolder::getDefaultOutboxName();
                    $folder->type     = EmailFolder::TYPE_OUTBOX;
                    $folder->emailBox = $box;
                    $box->folders->add($folder);
                    $folder           = new EmailFolder();
                    $folder->name     = EmailFolder::getDefaultOutboxErrorName();
                    $folder->type     = EmailFolder::TYPE_OUTBOX_ERROR;
                    $folder->emailBox = $box;
                    $box->folders->add($folder);
                    $folder           = new EmailFolder();
                    $folder->name     = EmailFolder::getDefaultInboxName();
                    $folder->type     = EmailFolder::TYPE_INBOX;
                    $folder->emailBox = $box;
                    $box->folders->add($folder);
                    $folder           = new EmailFolder();
                    $folder->name     = EmailFolder::getDefaultArchivedName();
                    $folder->type     = EmailFolder::TYPE_ARCHIVED;
                    $folder->emailBox = $box;
                    $box->folders->add($folder);
                    $folder           = new EmailFolder();
                    $folder->name     = EmailFolder::getDefaultArchivedUnmatchedName();
                    $folder->type     = EmailFolder::TYPE_ARCHIVED_UNMATCHED;
                    $folder->emailBox = $box;
                    $box->folders->add($folder);
                    $saved            = $box->save();
                    assert('$saved');
                }
                else
                {
                    throw new NotFoundException();
                }
            }
            $box->setSpecialBox();
            return $box;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'folders' => Zurmo::t('EmailMessagesModule', 'Folders', array(), null, $language),
                    'name'    => Zurmo::t('ZurmoModule',         'Name',    array(), null, $language),
                    'users'   => Zurmo::t('UsersModule',         'Users',   array(), null, $language),
                )
            );
        }

        protected function setSpecialBox()
        {
            $this->isNotifications = $this->name == self::NOTIFICATIONS_NAME;
        }

        public function isSpecialBox()
        {
            return $this->isNotifications;
        }

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Zurmo::t('EmailMessagesModule', '(Unnamed)');
            }
            return $this->name;
        }

        public static function getModuleClassName()
        {
            return 'EmailMessagesModule';
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                ),
                'relations' => array(
                    'folders' => array(RedBeanModel::HAS_MANY, 'EmailFolder'),
                    'user'    => array(RedBeanModel::HAS_MANY_BELONGS_TO, 'User'),
                ),
                'rules' => array(
                    array('name',          'required'),
                    array('name',          'type',    'type' => 'string'),
                    array('name',          'length',  'min'  => 3, 'max' => 64),
                )
            );
            return $metadata;
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
            return Zurmo::t('EmailMessagesModule', 'Email Box', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Boxes', array(), null, $language);
        }

        public function isDeletable()
        {
            return !$this->isNotifications;
        }
    }
?>
