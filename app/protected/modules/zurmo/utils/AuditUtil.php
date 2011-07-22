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
     * Provides functionality needed by Item, OwnedAuditableModel,
     * and OwnedAuditableCustomField, (which do not have a common inheritance
     * hierarchy), for saving old values on __set and writing audit entries.
     */
    class AuditUtil
    {
        // Give the class this...
        //public $originalAttributeValues = array();

        public static function throwNotSupportedExceptionIfNotCalledFromAnItem() // Pass __CLASS__
        {
            $backTrace = debug_backtrace();
            for ($i = 0; $i < count($backTrace); $i++)
            {
                if (isset($backTrace[$i]['class']) &&
                          $backTrace[$i]['class'] == 'Item')
                {
                    return;
                }
            }
            throw new NotSupportedException();
        }

        public static function saveOriginalAttributeValue($auditableModel, $attributeName, $value)
        {
            assert('$auditableModel instanceof Item          ||
                    $auditableModel instanceof OwnedModel    ||
                    $auditableModel instanceof OwnedCustomField');
            assert('property_exists($auditableModel, "originalAttributeValues")');
            if (!array_key_exists($attributeName, $auditableModel->originalAttributeValues))
            {
                if (!$auditableModel->isRelation($attributeName))
                {
                    if ($auditableModel->$attributeName != $value)
                    {
                        $auditableModel->originalAttributeValues[$attributeName] = $auditableModel->$attributeName;
                    }
                }
                elseif (!$auditableModel->isOwnedRelation($attributeName) &&
                        !$auditableModel->$attributeName instanceof CustomFieldData)
                {
                    assert('$auditableModel->$attributeName instanceof RedBeanModel');
                    $relatedModel = $auditableModel->$attributeName;
                    if ($value === null || !$relatedModel->isSame($value))
                    {
                        $auditableModel->originalAttributeValues[$attributeName] = array(get_class($relatedModel),
                                                                                         $relatedModel->id,
                                                                                         strval($relatedModel));
                    }
                }
            }
        }

        public static function logAuditEventsListForChangedAttributeValues(Item $item, array $attributeNames = array(), RedBeanModel $ownedModel = null)
        {
            assert('$item->id > 0');
            $attributeModel = $ownedModel === null ? $item : $ownedModel;
            $noAuditAttributeNames = self::getNoAuditAttributeNames($attributeModel);
            foreach ($attributeModel->originalAttributeValues as $attributeName => $oldValue)
            {
                if (!in_array($attributeName, $noAuditAttributeNames))
                {
                    if (!$attributeModel->isRelation($attributeName))
                    {
                        $newValue = $attributeModel->$attributeName;
                    }
                    else
                    {
                        assert('$attributeModel->$attributeName instanceof RedBeanModel');
                        $relatedModel = $attributeModel->$attributeName;
                        $newValue = array(get_class($relatedModel),
                                          $relatedModel->id,
                                          strval($relatedModel));
                        assert('$oldValue != $newValue');
                    }
                    $tempAttributeNames = $attributeNames;
                    $tempAttributeNames[] = $attributeName;
                    $data = array(strval($item), $tempAttributeNames, $oldValue, $newValue);
                    AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_MODIFIED,
                                              $data, $item);
                }
            }
            foreach ($attributeModel->attributeNames() as $attributeName)
            {
                if (!in_array($attributeName, $noAuditAttributeNames) &&
                    $attributeModel->isOwnedRelation($attributeName))
                {
                    try
                    {
                        $ownedModel = $attributeModel->$attributeName;
                    }
                    catch (AccessDeniedSecurityException $e)
                    {
                        continue; // If someone doesn't have access they
                                  // they can't have modified the attributes.
                    }
                    catch (NotSupportedException $e)
                    {
                        continue; // Certain attributes can't be modified, like
                                  // rights on the super administrators group
                                  // so we can safely ignore them.
                    }
                    if ($ownedModel instanceof OwnedModel ||
                        $ownedModel instanceof OwnedCustomField)
                    {
                        $ownedModels = array($ownedModel);
                    }
                    else
                    {
                        assert('$ownedModel instanceof RedBeanModels');
                        $ownedModels = $ownedModel;
                    }
                    foreach ($ownedModels as $ownedModel)
                    {
                        $tempAttributeNames = $attributeNames;
                        $tempAttributeNames[] = $attributeName;
                        self::logAuditEventsListForChangedAttributeValues($item, $tempAttributeNames, $ownedModel);
                    }
                }
            }
        }
                                                // TODO - collections
        public static function stringifyValue(/*RedBeanModel*/ $attributeModel, $attributeName, $value)
        {
            if ($attributeModel instanceof RedBeanModels)
            {
                return 'Collection';
            }
            assert('is_string($attributeName) && $attributeName != ""');
            if (!$attributeModel->isRelation($attributeName))
            {
                if ($value === null || $value == '')
                {
                    $value = yii::t('Default', '(None)');
                }
                $s = $value;
            }
            else
            {
                assert('is_array($value)');
                $modelClassName = $value[0];
                $s = $modelClassName::getModelLabelByTypeAndLanguage('Singular') .
                     '(' . $value[1] . ') ';
                if ($value[2] === null || $value == '')
                {
                    $s .= yii::t('Default', '(None)');
                }
                else
                {
                    $s .= $value[2];
                }
            }
            return $s;
        }

        public static function clearRelatedModelsOriginalAttributeValues(Item $item)
        {
            assert('$item->id > 0');
            $noAuditAttributeNames = self::getNoAuditAttributeNames($item);
            foreach ($item->attributeNames() as $attributeName)
            {
                if (!in_array($attributeName, $noAuditAttributeNames) &&
                    $item->isOwnedRelation($attributeName))
                {
                    try
                    {
                        $ownedModel = $item->$attributeName;
                    }
                    catch (NotSupportedException $e)
                    {
                        continue;
                    }
                    if ($ownedModel instanceof OwnedModel ||
                        $ownedModel instanceof OwnedCustomField)
                    {
                        $ownedModels = array($ownedModel);
                    }
                    else
                    {
                        assert('$ownedModel instanceof RedBeanModels');
                        $ownedModels = $ownedModel;
                    }
                    for ($i = 0; $i < count($ownedModels); $i++)
                    {
                        $ownedModel = $ownedModels[$i];
                        $ownedModel->forgetOriginalAttributeValues();
                    }
                }
            }
        }

        protected static function getNoAuditAttributeNames($auditableModel)
        {
            assert('$auditableModel instanceof Item       ||
                    $auditableModel instanceof OwnedModel ||
                    $auditableModel instanceof OwnedCustomField');
            $noAuditAttributes = array();
            $metadata = $auditableModel->getMetadata();
            foreach ($metadata as $notUsed => $classMetadata)
            {
                if (isset($classMetadata['noAudit']))
                {
                    assert('is_array($classMetadata["noAudit"])');
                    $noAuditAttributes = array_merge($noAuditAttributes, $classMetadata['noAudit']);
                }
            }
            return $noAuditAttributes;
        }
    }
?>
