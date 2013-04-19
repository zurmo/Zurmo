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
     * Search form for filtering a list of WorkflowMessageInQueue models in a list
     */
    class WorkflowMessageInQueuesSearchForm extends SearchForm
    {
        /**
         * @var string
         */
        public $workflowName;

        /**
         * @return string|void
         */
        protected static function getRedBeanModelClassName()
        {
            return 'WorkflowMessageInQueue';
        }

        /**
         * @return array
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('workflowName', 'safe'),
            ));
        }

        /**
         * @return array
         */
        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                'workflowName' => Zurmo::t('WorkflowsModule', 'Workflow Name'),
            ));
        }

        /**
         * @return array
         */
        public function getAttributesMappedToRealAttributesMetadata()
        {
            return array_merge(parent::getAttributesMappedToRealAttributesMetadata(), array(
                'workflowName' => array(
                    array('savedWorkflow',  'name'),
                ),
            ));
        }

        /**
         * Override since the module globalSearchAttributeNames are for SavedWorkflow not the WorkflowMessageInQueue
         * models.
         * @param unknown_type $realAttributesMetadata
         */
        public function resolveMixedSearchAttributeMappedToRealAttributesMetadata(& $realAttributesMetadata)
        {
            assert('is_array($realAttributesMetadata)');
            $data = array();
            $data['anyMixedAttributes'][] = array('savedWorkflow', 'name');
            $realAttributesMetadata = array_merge($realAttributesMetadata, $data);
        }

        /**
         * @return array of attributeName and label pairings.  Based on what attributes are used
         * in a mixed attribute search.
         */
        public function getGlobalSearchAttributeNamesAndLabelsAndAll()
        {
            $namesAndLabels = array();
            $namesAndLabels['workflowName'] = $this->getAttributeLabel('workflowName');
            return array_merge(array('All' => Zurmo::t('Core', 'All')), $namesAndLabels);
        }
    }
?>