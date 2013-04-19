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
     * Class to help interacting with classes that implement the MashableInboxInterface.
     */
    abstract class MashableInboxRules
    {
        /**
         * This sets if action button to create a new model should be rendered in the mashableInboxView
         * @var boolean
         */
        public $shouldRenderCreateAction = false;

        /**
         * This method return the count of the models that current user
         * has not read latest changes
         * @return integer
         */
        abstract public function getUnreadCountForCurrentUser();

        /**
         * Returns true if current user had read latest changes of model
         * else it should return false
         * @param integer $modelId
         * @return boolean
         */
        abstract public function hasCurrentUserReadLatest($modelId);

        /**
         * Returns the metada for the FilteredBy
         * @param string $filteredBy
         */
        abstract public function getMetadataFilteredByFilteredBy($filteredBy);

        /**
         * Returns the metada for the filter by model options
         * @param integer $option
         */
        abstract public function getMetadataFilteredByOption($option);

        /**
         * Returns the option to populate the MashableInboxOptionsByModelRadioElement
         * that will be used to filter list view by model options
         */
        abstract public function getActionViewOptions();

        abstract public function getModelClassName();

        /**
         * The attribute to be used for the ordering of the list view
         */
        abstract public function getMachableInboxOrderByAttributeName();

        /**
         * Marks the model as read latest changes by current user by modelId
         * @param integer $modelId
         */
        abstract public function resolveMarkRead($modelId);

        /**
         * Marks the model as read latest changes by current user by modelId
         * @param integer $modelId
         */
        abstract public function resolveMarkUnread($modelId);

        /**
         * Makes the metadata to filter models by the searchTerm
         * @param string $searchTerm
         */
        public function getSearchAttributeData($searchTerm)
        {
            return null;
        }

        /**
         * Makes the metadata to be used when searching models that
         * will be displayed in the MashableInboxListView
         */
        public function getMetadataForMashableInbox()
        {
            return null;
        }

        /**
         * The list view class name that will be displayed for the current model
         * @return string
         */
        public function getListViewClassName()
        {
            $modelClassName  = $this->getModelClassName();
            $moduleClassName = $modelClassName::getModuleClassName();
            return $moduleClassName::getPluralCamelCasedName() . 'ListView';
        }

        /**
         * The list view class name that will be used to display a ZeroModelView
         * @return string
         */
        public function getZeroModelViewClassName()
        {
            $modelClassName  = $this->getModelClassName();
            $moduleClassName = $modelClassName::getModuleClassName();
            return $moduleClassName::getPluralCamelCasedName() . 'ZeroModelsYetView';
        }

        public function getListView($option, $filteredBy = MashableInboxForm::FILTERED_BY_ALL, $searchTerm = null)
        {
            $modelClassName             = $this->getModelClassName();
            $orderBy                    = $this->getMachableInboxOrderByAttributeName();
            $pageSize                   = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                'listPageSize', get_class(Yii::app()->controller->module));
            $metadataByOptionAndFilter  = MashableUtil::mergeMetada(
                                                    $this->getMetadataFilteredByOption($option),
                                                    $this->getMetadataFilteredByFilteredBy($filteredBy));
            $metadata                   = MashableUtil::mergeMetada(
                                                    $metadataByOptionAndFilter,
                                                    $this->getSearchAttributeData($searchTerm));
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadata,
                $modelClassName,
                'RedBeanModelDataProvider',
                $orderBy,
                true,
                $pageSize
            );
            $listViewClassName = $this->getListViewClassName();
            $listView = new $listViewClassName(
                    Yii::app()->controller->id,
                    Yii::app()->controller->module->id,
                    $modelClassName,
                    $dataProvider,
                    array());
            return $listView;
        }

        /**
         * The content to be displayed in the MashableInboxListView row
         * @param RedBeanModel $model
         * @return string
         */
        public function getModelStringContent(RedBeanModel $model)
        {
            $modelDisplayString = strval($model);
            $params             = array('label' => $modelDisplayString, 'wrapLabel' => false);
            $moduleClassName    = $model->getModuleClassName();
            $moduleId           = $moduleClassName::getDirectoryName();
            $element            = new DetailsLinkActionElement('default', $moduleId, $model->id, $params);
            $content            = $element->render();
            $lastCommentNumber  = count($model->comments) - 1;
            if ($lastCommentNumber > 0)
            {
                $content .= ZurmoHtml::tag(
                                    'span',
                                    array("class" => "last-comment"),
                                    $model->comments[$lastCommentNumber]->description
                                );
            }
            return $content;
        }

        /**
         * A string containing the time passed from latest changes on the model
         * to be used in the MashableInboxListView row
         * @param RedBeanModel $model
         * @return string
         */
        public function getModelCreationTimeContent(RedBeanModel $model)
        {
            return MashableUtil::getTimeSinceLatestUpdate($model->latestDateTime);
        }

        /**
         * Template to display the models rows content
         * @return string
         */
        public function getSummaryContentTemplate()
        {
            return "<span>{modelStringContent}</span><span  class=\"list-item-details\">{modelCreationTimeContent}</span>";
        }

        /**
         * Mass options to be rendered in the MashableInboxMassActionElement
         * The array retunr should be like this
         * array('stringForTheActionName'  => array('label' => $label,
                                                    'isActionForAll' => $boolean),
             );
         * @return array
         */
        public function getMassOptions()
        {
            return array();
        }

        /**
         * Given the model, check if the a user has read latest changes
         * @param $model
         * @param User $user
         */
        public function hasUserReadLatest($model, User $user)
        {
            $haveNotReadRelationName = $this->getHaveNotReadRelationName();
            if ($model->$haveNotReadRelationName->count() > 0)
            {
                foreach ($model->$haveNotReadRelationName as $personWhoHaveNotReadLatest)
                {
                    if ($personWhoHaveNotReadLatest->person->getClassId('Item') == $user->getClassId('Item'))
                    {
                        return false;
                    }
                }
            }
            return true;
        }

        public function markUserAsHavingUnreadLatestModel($model, User $user)
        {
            if ($this->hasUserReadLatest($model, $user))
            {
                $this->addPersonWhoHasNotReadLatestToModel(
                                                    $model,
                                                    $this->makePersonWhoHasNotReadLatest($user));
            }
            $model->save();
        }

        public function markUserAsHavingReadLatestModel($model, User $user)
        {
            $haveNotReadRelationName = $this->getHaveNotReadRelationName();
            foreach ($model->$haveNotReadRelationName as $existingPersonWhoHaveNotReadLatest)
            {
                if ($existingPersonWhoHaveNotReadLatest->person->getClassId('Item') == $user->getClassId('Item'))
                {
                    $this->removePersonWhoHasNotReadLatestToModel(
                                                    $model,
                                                    $existingPersonWhoHaveNotReadLatest);
                }
            }
            $model->save();
        }

        /**
         * Makes an return a PersonWhoHasNotReadLatest based on the person or user
         * @param $personOrUserModel
         */
        public function makePersonWhoHasNotReadLatest($personOrUserModel)
        {
            assert('$personOrUserModel instanceof User || $personOrUserModel instanceof Person');
            $personWhoHaveNotReadLatestModelName = $this->getPersonWhoHasNotReadLatestModelName();
            $personWhoHaveNotReadLatest          = new $personWhoHaveNotReadLatestModelName();
            $personWhoHaveNotReadLatest->person  = $personOrUserModel;
            return $personWhoHaveNotReadLatest;
        }

        /**
         * This method should return the name of the model relation that contains
         * the persons who have not read the latest changes
         * @return string
         */
        protected function getHaveNotReadRelationName()
        {
            return 'personsWhoHaveNotReadLatest';
        }

        /**
         * Retunr the model name where is stored the users or persons who have not
         * read the latest changes of the model
         * @return string
         */
        protected function getPersonWhoHasNotReadLatestModelName()
        {
            return 'PersonWhoHaveNotReadLatest';
        }

        /**
         * Add a person who has not read the model latest changes
         * @param $model
         * @param $personWhoHasNotReadLatest
         */
        protected function addPersonWhoHasNotReadLatestToModel($model, $personWhoHasNotReadLatest)
        {
            $relationName = $this->getHaveNotReadRelationName();
            $model->$relationName->add($personWhoHasNotReadLatest);
        }

        /**
         * Removes a person from the one that has not read the model latest changes
         * @param $model
         * @param $personWhoHasNotReadLatest
         */
        protected function removePersonWhoHasNotReadLatestToModel($model, $personWhoHasNotReadLatest)
        {
            $relationName = $this->getHaveNotReadRelationName();
            $model->$relationName->remove($personWhoHasNotReadLatest);
        }
    }
?>