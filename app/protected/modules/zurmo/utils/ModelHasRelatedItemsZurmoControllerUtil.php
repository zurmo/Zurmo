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
     * Extended class to support models that have related items such as activities or conversations.
     */
    class ModelHasRelatedItemsZurmoControllerUtil extends ZurmoControllerUtil
    {
        protected $relatedItemsRelationName;

        protected $relatedItemsFormName;

        public function __construct($relatedItemsRelationName, $relatedItemsFormName)
        {
            assert('is_string($relatedItemsRelationName)');
            assert('is_string($relatedItemsFormName)');
            $this->relatedItemsRelationName = $relatedItemsRelationName;
            $this->relatedItemsFormName     = $relatedItemsFormName;
        }

        protected function afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions)
        {
            assert('$model instanceof Item');
            $this->resolveModelsRelatedItemsFromPost($model);
        }

        /**
         * Passing in a $model, process any relatedItems that have to be removed, added, or changed.
         */
        protected function resolveModelsRelatedItemsFromPost(& $model)
        {
            assert('$model instanceof Item');
            $relationName = $this->relatedItemsRelationName;
            if (isset($_POST[$this->relatedItemsFormName]))
            {
                $newRelationModelsIndexedByItemId = array();
                foreach ($_POST[$this->relatedItemsFormName] as $modelClassName => $relationData)
                {
                    if (!empty($relationData['id']))
                    {
                        $aModel = $modelClassName::getById((int)$relationData['id']);
                        $newRelationModelsIndexedByItemId[$aModel->getClassId('Item')] = $aModel;
                    }
                    elseif (!empty($relationData['ids']))
                    {
                        $relationIds = explode(",", $relationData['ids']);  // Not Coding Standard
                        foreach ($relationIds as $relationIdFromElementStoringMultiples)
                        {
                            $aModel = $modelClassName::getById((int)$relationIdFromElementStoringMultiples);
                            $newRelationModelsIndexedByItemId[$aModel->getClassId('Item')] = $aModel;
                        }
                    }
                }
                if ($model->{$relationName}->count() > 0)
                {
                    $itemsToRemove = array();
                    foreach ($model->{$relationName} as $index => $existingItem)
                    {
                        if (!isset($newRelationModelsIndexedByItemId[$existingItem->getClassId('Item')]))
                        {
                            $itemsToRemove[] = $existingItem;
                        }
                        else
                        {
                            unset($newRelationModelsIndexedByItemId[$existingItem->getClassId('Item')]);
                        }
                    }
                    foreach ($itemsToRemove as $itemToRemove)
                    {
                        $model->{$relationName}->remove($itemToRemove);
                    }
                }
                //Now add missing related Items.
                foreach ($newRelationModelsIndexedByItemId as $modelToAdd)
                {
                    $model->{$relationName}->add($modelToAdd);
                }
            }
        }
    }
?>