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
     * The base View for a module's mass edit view.
     */
    class MassDeleteView extends EditView
    {
        /**
         * Array of booleans indicating
         * which attributes are currently trying to
         * be mass updated
         */
        protected $activeAttributes;

        protected $alertMessage;

        protected $selectedRecordCount;

        protected $title;

        protected $moduleClassName;

        /**
         * Constructs a detail view specifying the controller as
         * well as the model that will have its mass delete displayed.
         */
        public function __construct($controllerId, $moduleId, RedBeanModel $model, $activeAttributes, $selectedRecordCount, $title, $alertMessage = null, $moduleClassName)
        {
            assert('is_array($activeAttributes)');
            assert('is_string($title)');

            $this->controllerId                       = $controllerId;
            $this->moduleId                           = $moduleId;
            $this->model                              = $model;
            $this->modelClassName                     = get_class($model);
            $this->modelId                            = $model->id;
            $this->activeAttributes                   = $activeAttributes;
            $this->selectedRecordCount                = $selectedRecordCount;
            $this->title                              = $title;
            $this->alertMessage                       = $alertMessage;
            $this->moduleClassName                    = $moduleClassName;
        }

        protected function getSelectedRecordCount()
        {
            return $this->selectedRecordCount;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'CancelLink'),
                            array('type' => 'DeleteButton',
                                  'htmlOptions' => array(
                                                         'params' => array(
                                                            'selectedRecordCount' => 'eval:$this->getselectedRecordCount()'),

                                   ),
                            ),
                        ),
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'name',
                    ),
                ),
            );
            return $metadata;
        }

        protected function renderContent()
        {
            $content  = '<div class="wrapper">';
            $content .= $this->renderTitleContent();
            $content .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'ZurmoActiveForm',
                                                                array('id' => 'delete-form', 'enableAjaxValidation' => false)
                                                            );
            $content .= $formStart;
            if (!empty($this->alertMessage))
            {
                $content .= HtmlNotifyUtil::renderAlertBoxByMessage($this->alertMessage);
            }
            $content .= $this->renderOperationDescriptionContent();
            $actionElementContent = $this->renderActionElementBar(true);
            if ($actionElementContent != null)
            {
                $content .= '<div class="view-toolbar-container clearfix"><div class="form-toolbar">';
                $content .= $actionElementContent;
                $content .= '</div></div>';
            }
            $formEnd = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div></div>';
            return $content;
        }

        protected function renderTitleContent()
        {
            return '<h1>' . $this->title . '</h1>';
        }

        protected function renderOperationDescriptionContent()
        {
            $highlight = ZurmoHtml::tag('em', array(), Zurmo::t('Core', 'Mass Delete is not reversable.'));
            $message  = ZurmoHtml::tag('strong', array(), $highlight) .
                        '<br />' . '<strong>' . $this->selectedRecordCount . '</strong>&#160;' .
                        Zurmo::t('Core', $this->moduleClassName . 'SingularLabel|' . $this->moduleClassName . 'PluralLabel',
                        array_merge(array($this->selectedRecordCount), LabelUtil::getTranslationParamsForAllModules())) .
                        ' ' . Zurmo::t('Core', 'selected for removal.');
            return ZurmoHtml::wrapLabel($message, 'operation-description');
        }

        public static function getDesignerRulesType()
        {
            return 'MassDeleteView';
        }
    }
?>