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
            assert('$auditableModel instanceof Item             ||
                    $auditableModel instanceof OwnedModel       ||
                    $auditableModel instanceof OwnedCustomField ||
                    $auditableModel instanceof OwnedMultipleValuesCustomField');
            assert('property_exists($auditableModel, "originalAttributeValues")');
            if (!array_key_exists($attributeName, $auditableModel->originalAttributeValues))
            {
                if (!$auditableModel::isRelation($attributeName))
                {
                    if ($auditableModel->$attributeName != $value)
                    {
                        $auditableModel->originalAttributeValues[$attributeName] = $auditableModel->$attributeName;
                    }
                }
                elseif (!$auditableModel::isOwnedRelation($attributeName) &&
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
                    $processAuditEvent = true;
                    if (!$attributeModel::isRelation($attributeName))
                    {
                        $newValue = $attributeModel->$attributeName;
                    }
                    elseif ($attributeModel->$attributeName instanceof RedBeanOneToManyRelatedModels)
                    {
                            $newValue = $attributeModel->$attributeName->getStringifiedData();
                            assert('$oldValue != $newValue');
                    }
                    else
                    {
                        assert('$attributeModel->$attributeName instanceof RedBeanModel');
                        $relatedModel = $attributeModel->$attributeName;
                        if ($relatedModel->id < 0 && $oldValue[1] < 0)
                        {
                            $processAuditEvent = false;
                        }
                        else
                        {
                            $newValue = array(get_class($relatedModel),
                                              $relatedModel->id,
                                              strval($relatedModel));
                            assert('$oldValue != $newValue');
                        }
                    }
                    if ($processAuditEvent)
                    {
                        $tempAttributeNames = $attributeNames;
                        $tempAttributeNames[] = $attributeName;
                        $data = array(strval($item), $tempAttributeNames, $oldValue, $newValue);
                        AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_MODIFIED,
                                                  $data, $item);
                    }
                }
            }
            foreach ($attributeModel->attributeNames() as $attributeName)
            {
                if (!in_array($attributeName, $noAuditAttributeNames) &&
                    $attributeModel::isOwnedRelation($attributeName))
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
                        $ownedModel instanceof OwnedCustomField ||
                        $ownedModel instanceof OwnedMultipleValuesCustomField)
                    {
                        $ownedModels = array($ownedModel);
                    }
                    else
                    {
                        assert('$ownedModel instanceof RedBeanModels');
                        $ownedModels = array();
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
        public static function stringifyValue(/*RedBeanModel*/ $attributeModel, $attributeName, $value, $format = 'long')
        {
            assert('$format == "long" || $format == "short"');
            if ($attributeModel instanceof RedBeanModels)
            {
                return 'Collection';
            }
            assert('is_string($attributeName) && $attributeName != ""');
            if (!$attributeModel::isRelation($attributeName))
            {
                if ($value === null || $value == '')
                {
                    $value = Zurmo::t('Core', '(None)');
                }
                $s = $value;
            }
            elseif ($attributeModel->$attributeName instanceof RedBeanOneToManyRelatedModels)
            {
                $s = $attributeModel->stringifyOneToManyRelatedModelsValues($value);
            }
            else
            {
                assert('is_array($value)');
                if ($value[1] < 0)
                {
                    $s = Zurmo::t('Core', '(None)');
                }
                else
                {
                    $modelClassName = $value[0];
                    if ($format == 'long')
                    {
                    $s = $modelClassName::getModelLabelByTypeAndLanguage('Singular') .
                         '(' . $value[1] . ') ';
                    }
                    else
                    {
                        $s = null;
                    }
                    if ($value[2] === null || $value == '')
                    {
                        $s .= Zurmo::t('Core', '(None)');
                    }
                    else
                    {
                        $s .= $value[2];
                    }
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
                    $item::isOwnedRelation($attributeName))
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
                        $ownedModel instanceof OwnedCustomField ||
                        $ownedModel instanceof OwnedMultipleValuesCustomField)
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
                    $auditableModel instanceof OwnedCustomField ||
                    $auditableModel instanceof OwnedMultipleValuesCustomField');
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
