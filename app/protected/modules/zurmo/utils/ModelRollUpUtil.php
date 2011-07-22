<?php
    /**
     * Helper class to interface with models that have rollup rules.
     */
    class ModelRollUpUtil
    {
        public static function getItemIdsByModelAndUser(Item $model, User $user)
        {
            $relatedItemIds = array($model->getClassId('Item'));
            $metadata = $model::getMetadata();
            if(isset($metadata[get_class($model)]['rollupRelations']))
            {
                foreach($metadata[get_class($model)]['rollupRelations'] as $relationOrKey => $relationOrRelations)
                {
                    if(is_string($relationOrRelations))
                    {
                        foreach($model->{$relationOrRelations} as $relationModel)
                        {
                            self::resolveRelatedItemIdsByModelAndUser($relationModel, $relatedItemIds, $user);
                        }
                    }
                    elseif(is_array($relationOrRelations) && is_string($relationOrKey))
                    {
                        //Only supports single nesting level.
                        foreach($model->{$relationOrKey} as $relationModel)
                        {
                            foreach($relationOrRelations as $notUsed => $relationsSubRelation)
                            {
                                foreach($relationModel->$relationsSubRelation as $subRelationModel)
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
                if(!in_array($itemId, $relatedItemIds))
                {
                    $relatedItemIds[] = $itemId;
                }
            }
        }
    }
?>