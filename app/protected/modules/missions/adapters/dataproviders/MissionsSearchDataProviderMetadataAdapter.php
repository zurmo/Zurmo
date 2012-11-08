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
     * Adapter class to handle special metadata needs of mission listviews.  This is because there is a
     * relation where clause with either the owner or takenByUser
     */
    class MissionsSearchDataProviderMetadataAdapter extends SearchDataProviderMetadataAdapter
    {
        protected $type;

        /**
         * Override to add passing in type
         */
        public function __construct($model, $userId, $metadata, $type)
        {
            assert('$type == MissionsListConfigurationForm::LIST_TYPE_CREATED ||
                    $type == MissionsListConfigurationForm::LIST_TYPE_AVAILABLE ||
                    $type == MissionsListConfigurationForm::LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED');
            parent::__construct($model, $userId, $metadata);
            $this->type = $type;
        }

        /**
         * Convert metadata which is just an array
         * of posted searchAttributes into metadata that is
         * readable by the RedBeanModelDataProvider
         */
        public function getAdaptedMetadata($appendStructureAsAnd = true)
        {
            $adaptedMetadata = parent::getAdaptedMetadata($appendStructureAsAnd);
            $clauseCount = count($adaptedMetadata['clauses']);
            $startingCount = $clauseCount + 1;
            $structure = '';

            if ($this->type == MissionsListConfigurationForm::LIST_TYPE_CREATED)
            {
                $adaptedMetadata['clauses'][$startingCount] = array(
                    'attributeName' => 'owner',
                    'operatorType'  => 'equals',
                    'value'         => Yii::app()->user->userModel->id
                );
                $structure .= $startingCount;
            }
            elseif ($this->type == MissionsListConfigurationForm::LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED)
            {
                $adaptedMetadata['clauses'][$startingCount] = array(
                    'attributeName' => 'takenByUser',
                    'operatorType'  => 'equals',
                    'value'         => Yii::app()->user->userModel->id
                );
                $adaptedMetadata['clauses'][$startingCount + 1] = array(
                    'attributeName' => 'status',
                    'operatorType'  => 'oneOf',
                    'value'         => array(Mission::STATUS_TAKEN,
                                             Mission::STATUS_COMPLETED,
                                             Mission::STATUS_REJECTED),
                );
                $structure .= $startingCount . ' and ' . ($startingCount + 1);
            }
            else
            {
                $adaptedMetadata['clauses'][$startingCount] = array(
                    'attributeName' => 'takenByUser',
                    'operatorType'  => 'isNull',
                    'value'         => null
                );
                $adaptedMetadata['clauses'][$startingCount + 1] = array(
                    'attributeName' => 'createdByUser',
                    'operatorType'  => 'doesNotEqual',
                    'value'         => Yii::app()->user->userModel->id,
                );
                $structure .= $startingCount . ' and ' . ($startingCount + 1);
            }
            if (empty($metadata['structure']))
            {
                $adaptedMetadata['structure'] = '(' . $structure . ')';
            }
            else
            {
                $adaptedMetadata['structure'] = '(' . $adaptedMetadata['structure'] . ') and (' . $structure . ')';
            }
            return $adaptedMetadata;
        }
    }
?>