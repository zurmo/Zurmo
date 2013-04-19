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
     * Adapter class to handle special metadata needs of conversation listviews.  This is because there is a
     * relation where clause with ConversationParticipants person, which is difficult to do via the searchAttributes array
     */
    class ConversationsSearchDataProviderMetadataAdapter extends SearchDataProviderMetadataAdapter
    {
        /**
         * Filter by conversations the current user created.
         * @var integer
         */
        const LIST_TYPE_CREATED = 1;

        /**
         * Filter by conversations the current user is participating in
         * @var integer
         */
        const LIST_TYPE_PARTICIPANT = 2;

        /**
         * Filter by conversations that are marked as closed
         * @var integer
         */
        const LIST_TYPE_CLOSED = 3;

        protected $type;

        /**
         * Override to add passing in type
         */
        public function __construct($model, $userId, $metadata, $type)
        {
            assert('$type == self::LIST_TYPE_CREATED || $type == self::LIST_TYPE_PARTICIPANT || $type == self::LIST_TYPE_CLOSED');
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

            if ($this->type == self::LIST_TYPE_CREATED)
            {
                $adaptedMetadata['clauses'][$startingCount] = array(
                    'attributeName' => 'isClosed',
                    'operatorType'  => 'isNull',
                    'value'         => null
                );
                $adaptedMetadata['clauses'][$startingCount + 1] = array(
                    'attributeName' => 'isClosed',
                    'operatorType'  => 'equals',
                    'value'         => 0
                );
                $adaptedMetadata['clauses'][$startingCount + 2] = array(
                    'attributeName' => 'owner',
                    'operatorType'  => 'equals',
                    'value'         => Yii::app()->user->userModel->id
                );
                $structure .= '( ' . $startingCount . ' or ' . ($startingCount + 1) . ' ) and ' . ($startingCount + 2);
            }
            elseif ($this->type == self::LIST_TYPE_PARTICIPANT)
            {
                $adaptedMetadata['clauses'][$startingCount] = array(
                    'attributeName' => 'isClosed',
                    'operatorType'  => 'isNull',
                    'value'         => null
                );
                $adaptedMetadata['clauses'][$startingCount + 1] = array(
                    'attributeName' => 'isClosed',
                    'operatorType'  => 'equals',
                    'value'         => 0
                );
                $adaptedMetadata['clauses'][$startingCount + 2] = array(
                    'attributeName'        => 'conversationParticipants',
                    'relatedAttributeName' => 'person',
                    'operatorType'  => 'equals',
                    'value'         => Yii::app()->user->userModel->getClassId('Item')
                );
                $adaptedMetadata['clauses'][$startingCount + 3] = array(
                    'attributeName' => 'owner',
                    'operatorType'  => 'equals',
                    'value'         => Yii::app()->user->userModel->id
                );
                $structure .= '( ' . $startingCount . ' or ' . ($startingCount + 1) . ' ) and (' . ($startingCount + 2) . ' or ' . ($startingCount + 3) . ')';
            }
            elseif ($this->type == self::LIST_TYPE_CLOSED)
            {
                $adaptedMetadata['clauses'][$startingCount] = array(
                    'attributeName' => 'isClosed',
                    'operatorType'  => 'equals',
                    'value'         => true
                );
                $adaptedMetadata['clauses'][$startingCount + 1] = array(
                    'attributeName'        => 'conversationParticipants',
                    'relatedAttributeName' => 'person',
                    'operatorType'  => 'equals',
                    'value'         => Yii::app()->user->userModel->getClassId('Item')
                );
                $adaptedMetadata['clauses'][$startingCount + 2] = array(
                    'attributeName' => 'owner',
                    'operatorType'  => 'equals',
                    'value'         => Yii::app()->user->userModel->id
                );
                $structure .= $startingCount . ' and (' . ($startingCount + 1) . ' or ' . ($startingCount + 2) . ')';
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