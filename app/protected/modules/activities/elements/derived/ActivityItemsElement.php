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
     * User interface element for managing related model relations for activities. This class supports a HAS_ONE
     * only.  If you need to support HAS_MANY models you will need to extend this class.
     *
     */
    class ActivityItemsElement extends ModelsElement implements DerivedElementInterface, ElementActionTypeInterface
    {
        /**
         * The action type of the related model
         * for which the autocomplete/select popup are calling.
         */
        protected static $editableActionType = 'ActivityItemsModalList';

        protected function renderControlNonEditable()
        {
            assert('$this->model instanceof Activity');
            $metadata     = Activity::getMetadata();
            return $this->renderNonEditableElementsForRelationsByRelationsData($metadata['Activity']['activityItemsModelClassNames']);
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof Activity');
            assert('!isset($this->params["inputPrefix"])'); //Not supported at this time.
            $metadata     = Activity::getMetadata();
            return $this->renderElementsForRelationsByRelationsData($metadata['Activity']['activityItemsModelClassNames']);
        }

        protected function renderElementsForRelationsByRelationsData($relationModelClassNames)
        {
            $content = "<table> \n";
            foreach ($relationModelClassNames as $relationModelClassName)
            {
                $activityItemForm = null;
                //ASSUMES ONLY A SINGLE ATTACHED ACTIVITYITEM PER RELATION TYPE.
                foreach ($this->model->activityItems as $item)
                {
                    try
                    {
                        $modelDerivationPathToItem = ActivitiesUtil::getModelDerivationPathToItem($relationModelClassName);
                        $castedDownModel           = $item->castDown(array($modelDerivationPathToItem));
                        $activityItemForm         = new ActivityItemForm($castedDownModel);
                        break;
                    }
                    catch (NotFoundException $e)
                    {
                    }
                }
                if ($activityItemForm == null)
                {
                    $relationModel     = new $relationModelClassName();
                    $activityItemForm  = new ActivityItemForm($relationModel);
                }
                $modelElementClassName = ActivityItemRelationToModelElementUtil::resolveModelElementClassNameByActionSecurity(
                                              $relationModelClassName, Yii::app()->user->userModel);
                if ($modelElementClassName != null)
                {
                    $element  = new $modelElementClassName($activityItemForm,
                                                           $relationModelClassName,
                                                           $this->form);
                    assert('$element instanceof ModelElement');
                    $element->editableTemplate = $this->getActivityItemEditableTemplate();
                    $content .= $element->render();
                }
            }
            $content     .= "</table> \n";
            return $content;
        }

        protected function renderNonEditableElementsForRelationsByRelationsData($relationModelClassNames)
        {
            $content = "<table> \n";
            foreach ($relationModelClassNames as $relationModelClassName)
            {
                $activityItemForm = null;
                //ASSUMES ONLY A SINGLE ATTACHED ACTIVITYITEM PER RELATION TYPE.
                foreach ($this->model->activityItems as $item)
                {
                    try
                    {
                        $modelDerivationPathToItem = ActivitiesUtil::getModelDerivationPathToItem($relationModelClassName);
                        $castedDownModel = $item->castDown(array($modelDerivationPathToItem));
                        $activityItemForm = new ActivityItemForm($castedDownModel);
                        break;
                    }
                    catch (NotFoundException $e)
                    {
                        //do nothing
                    }
                }
                if ($activityItemForm != null)
                {
                    $modelElementClassName = ActivityItemRelationToModelElementUtil::resolveModelElementClassNameByActionSecurity(
                                          $relationModelClassName, Yii::app()->user->userModel);
                    if ($modelElementClassName != null)
                    {
                        $element  = new $modelElementClassName($activityItemForm, $relationModelClassName, $this->form);
                        assert('$element instanceof ModelElement');
                        $element->nonEditableTemplate = $this->getActivityItemNonEditableTemplate();
                        $content .= $element->render();
                    }
                }
            }
            $content     .= "</table> \n";
            return $content;
        }

        protected function getActivityItemEditableTemplate()
        {
            $template  = "<tr><td style='border:0px;' nowrap='nowrap'>\n";
            $template .= "{label}";
            $template .= "</td><td width='100%' style='border:0px;'>\n";
            $template .= '&#160;{content}{error}';
            $template .= "</td></tr>\n";
            return $template;
        }

        protected function getActivityItemNonEditableTemplate()
        {
            $template  = "<tr><td width='100%' style='border:0px;'>\n";
            $template .= '{content}';
            $template .= "</td></tr>\n";
            return $template;
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return CHtml::label(Yii::t('Default', 'Related to'), false);
        }

        public static function getDisplayName()
        {
            $metadata        = Activity::getMetadata();
            $content         =  Yii::t('Default', 'Related') . '&#160;';
            $relationContent = null;
            foreach ($metadata['Activity']['activityItemsModelClassNames'] as $relationModelClassName)
            {
                if ($relationContent != null)
                {
                    $relationContent .= ',&#160;';
                }
                $relationContent .= $relationModelClassName::getModelLabelByTypeAndLanguage('Plural');
            }
            return $content . $relationContent;
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        /**
         * Gets the action type for the related model's action
         * that is called by the select button or the autocomplete
         * feature in the Editable render.
         */
        public static function getEditableActionType()
        {
            return static::$editableActionType;
        }

        /**
         * Currently ActivityItems is not supported in non editable views.
         */
        public static function getNonEditableActionType()
        {
            throw new NotImplementedException();
        }
    }
?>