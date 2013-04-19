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

    class Item extends CustomFieldsModel
    {
        private $insideOnModified;

        protected $isSetting = false;

        // On changing a member value the original value
        // is saved (ie: on change it again the original
        // value is not overwritten) so that on save the
        // changes can be written to the audit log.
        public $originalAttributeValues = array();

        private $_workflowsToProcessAfterSave = array();

        private $_processWorkflowOnSave = true;

        public function onCreated()
        {
            $this->unrestrictedSet('createdDateTime',  DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
            $this->unrestrictedSet('modifiedDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        public function onModified()
        {
            if (!$this->insideOnModified)
            {
                $this->insideOnModified = true;
                if (!($this->unrestrictedGet('id') < 0 &&
                     $this->getScenario() == 'importModel' &&
                     array_key_exists('modifiedDateTime', $this->originalAttributeValues)))
                {
                    $this->unrestrictedSet('modifiedDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
                }
                if (Yii::app()->user->userModel != null && Yii::app()->user->userModel->id > 0)
                {
                    if (!($this->unrestrictedGet('id') < 0 &&
                         $this->getScenario() == 'importModel' &&
                         array_key_exists('modifiedByUser', $this->originalAttributeValues)))
                    {
                        $this->unrestrictedSet('modifiedByUser', Yii::app()->user->userModel);
                    }
                }
                $this->insideOnModified = false;
            }
        }

        public function __set($attributeName, $value)
        {
            $this->isSetting = true;
            try
            {
                if (!$this->isSaving)
                {
                    AuditUtil::saveOriginalAttributeValue($this, $attributeName, $value);
                }
                parent::__set($attributeName, $value);
                $this->isSetting = false;
            }
            catch (Exception $e)
            {
                $this->isSetting = false;
                throw $e;
            }
        }

        public function delete()
        {
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_DELETED, strval($this), $this);
            return parent::delete();
        }

        // Makes Item appear on the stack so that auditing can ensure
        // that things owned by Item are only saved by Item and not directly.
        public function save($runValidation = true, array $attributeNames = null)
        {
            return parent::save($runValidation, $attributeNames);
        }

        public function addWorkflowToProcessAfterSave(Workflow $workflow)
        {
            $this->_workflowsToProcessAfterSave[] = $workflow;
        }

        public function getWorkflowsToProcessAfterSave()
        {
            return $this->_workflowsToProcessAfterSave;
        }

        public function setDoNotProcessWorkflowOnSave()
        {
            $this->_processWorkflowOnSave = false;
        }

        public function setProcessWorkflowOnSave()
        {
            $this->_processWorkflowOnSave = true;
        }

        public function shouldProcessWorkflowOnSave()
        {
            return $this->_processWorkflowOnSave;
        }

        protected static function getByNameOrEquivalent($attributeName, $value)
        {
            assert('is_string($attributeName)');
            assert('is_string($value) && $value != ""');
            return static::getSubset(null, null, null, $attributeName . " = '" . DatabaseCompatibilityUtil::escape($value) . "'");
        }

        /**
         * Special handling of the import scenario. When you are importing a model, you can potentially set the
         * created/modified user/datetime which is normally not allowed since they are read-only attributes.  This
         * logic helps to allow for this special use case.
         * @see RedBeanModel::beforeSave()
         */
        protected function beforeSave()
        {
             if (parent::beforeSave())
             {
                if ($this->unrestrictedGet('id') < 0)
                {
                    if ($this->getScenario() != 'importModel' ||
                      ($this->getScenario() == 'importModel' && $this->createdByUser->id  < 0))
                    {
                        if (Yii::app()->user->userModel != null && Yii::app()->user->userModel->id > 0)
                        {
                            $this->unrestrictedSet('createdByUser', Yii::app()->user->userModel);
                        }
                    }
                }
                $this->isNewModel = $this->id < 0;
                return true;
             }
             else
             {
                 return false;
             }
        }

        protected function afterSave()
        {
            parent::afterSave();
            $this->logAuditEventsListForCreatedAndModifed($this->isNewModel);
            AuditUtil::clearRelatedModelsOriginalAttributeValues($this);
            $this->originalAttributeValues      = array();
            $this->_workflowsToProcessAfterSave = array();
            $this->isNewModel = false; //reset.
        }

        protected function logAuditEventsListForCreatedAndModifed($newModel)
        {
            if ($newModel)
            {
                AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_CREATED, strval($this), $this);
            }
            else
            {
                AuditUtil::logAuditEventsListForChangedAttributeValues($this);
            }
        }

        public function forgetOriginalAttributeValues()
        {
            $this->unrestrictedSet('originalAttributeValues', array());
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'createdDateTime',
                    'modifiedDateTime',
                ),
                'relations' => array(
                    'createdByUser'  => array(RedBeanModel::HAS_ONE,  'User', RedBeanModel::NOT_OWNED,
                                     RedBeanModel::LINK_TYPE_SPECIFIC, 'createdByUser'),
                    'modifiedByUser' => array(RedBeanModel::HAS_ONE,  'User', RedBeanModel::NOT_OWNED,
                                     RedBeanModel::LINK_TYPE_SPECIFIC, 'modifiedByUser'),
                ),
                'rules' => array(
                    array('createdDateTime',  'required'),
                    array('createdDateTime',  'readOnly'),
                    array('createdDateTime',  'type', 'type' => 'datetime'),
                    array('createdByUser',    'readOnly'),
                    array('modifiedDateTime', 'required'),
                    array('modifiedDateTime', 'readOnly'),
                    array('modifiedDateTime', 'type', 'type' => 'datetime'),
                    array('modifiedByUser',   'readOnly'),
                ),
                'elements' => array(
                    'createdDateTime'  => 'DateTime',
                    'modifiedDateTime' => 'DateTime',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return false;
        }

        /**
         * Used for testing only. In scenarios where you need to test beforeDelete but can't because beforeDelete is
         * protected
         */
        public function testBeforeDelete()
        {
             $this->beforeDelete();
        }

        /**
         * See the yii documentation.
         */
        public function isAttributeAudited($attributeName)
        {
            assert("\$this->isAttribute('$attributeName')");
            assert('$attributeName != "id"');
            $attributeModelClassName = static::getAttributeModelClassName($attributeName);
            $metadata = static::getMetadata();
            if (isset($metadata[$attributeModelClassName]['noAudit']) &&
                in_array($attributeName, $metadata[$attributeModelClassName]['noAudit']))
            {
                return false;
            }
            return true;
        }

        /**
         * Override to handle the import scenario. During import you are allowed to externally set several read-only
         * attributes.
         * (non-PHPdoc)
         * @see RedBeanModel::isAllowedToSetReadOnlyAttribute()
         */
        public function isAllowedToSetReadOnlyAttribute($attributeName)
        {
            if ($this->getScenario() == 'importModel')
            {
                if ($this->unrestrictedGet('id') > 0)
                {
                    return false;
                }
                if ( in_array($attributeName, array('createdByUser', 'modifiedByUser', 'createdDateTime', 'modifiedDateTime')))
                {
                    return true;
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            return false;
        }

        /**
         * @return string of gamificationRulesType Override for a child class as needed.
         */
        public static function getGamificationRulesType()
        {
            return null;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language), array(
                'createdByUser'      => Zurmo::t('ZurmoModule', 'Created By User', array(), null, $language),
                'createdDateTime'    => Zurmo::t('ZurmoModule', 'Created Date Time', array(), null, $language),
                'modifiedByUser'     => Zurmo::t('ZurmoModule', 'Modified By User', array(), null, $language),
                'modifiedDateTime'   => Zurmo::t('ZurmoModule', 'Modified Date Time', array(), null, $language),
            ));
        }
    }
?>