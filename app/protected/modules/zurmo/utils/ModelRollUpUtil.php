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
     * Helper class to interface with models that have rollup rules.
     */
    class ModelRollUpUtil
    {
        public static function getItemIdsByModelAndUser(Item $model, User $user)
        {
            $relatedItemIds = array($model->getClassId('Item'));
            $metadata = $model::getMetadata();
            if (isset($metadata[get_class($model)]['rollupRelations']))
            {
                foreach ($metadata[get_class($model)]['rollupRelations'] as $relationOrKey => $relationOrRelations)
                {
                    if (is_string($relationOrRelations))
                    {
                        foreach ($model->{$relationOrRelations} as $relationModel)
                        {
                            self::resolveRelatedItemIdsByModelAndUser($relationModel, $relatedItemIds, $user);
                        }
                    }
                    elseif (is_array($relationOrRelations) && is_string($relationOrKey))
                    {
                        //Only supports single nesting level.
                        foreach ($model->{$relationOrKey} as $relationModel)
                        {
                            foreach ($relationOrRelations as $notUsed => $relationsSubRelation)
                            {
                                foreach ($relationModel->$relationsSubRelation as $subRelationModel)
                                {
                                    self::resolveRelatedItemIdsByModelAndUser($subRelationModel, $relatedItemIds, $user);
                                }
                            }
                            self::resolveRelatedItemIdsByModelAndUser($relationModel, $relatedItemIds, $user);
                        }
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                }
            }
            return $relatedItemIds;
        }

        protected static function resolveRelatedItemIdsByModelAndUser(Item $model, & $relatedItemIds, User $user)
        {
            assert('is_array($relatedItemIds)');
            if (RightsUtil::canUserAccessModule($model::getModuleClassName(), $user))
            {
                $itemId = $model->getClassId('Item');
                if (!in_array($itemId, $relatedItemIds))
                {
                    $relatedItemIds[] = $itemId;
                }
            }
        }
    }
?>