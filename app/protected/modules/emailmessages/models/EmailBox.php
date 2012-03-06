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

    class EmailBox extends Item
    {
        const NOTIFICATIONS_NAME   = 'System Notifications';

        protected $isNotifications = false;

        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            $bean = R::findOne(EmailBox::getTableName('EmailBox'), "name = '$name'");
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
                if($name == self::NOTIFICATIONS_NAME)
                {
                    $box = new EmailBox();
                    $box->name        = self::NOTIFICATIONS_NAME;
                    $folder           = new EmailFolder();
                    $folder->name     = EmailFolder::getDefaultSentName();
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
                    $folder->name     = EmailFolder::getDefaultOutboxName();
                    $folder->type     = EmailFolder::TYPE_OUTBOX_ERROR;
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
                return Yii::t('Default', '(Unnamed)');
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

        public function isDeletable()
        {
            return !$this->isNotifications;
        }
    }
?>