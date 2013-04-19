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
     * View for managing report details
      */
    class ReportDetailsView extends DetailsView
    {
        protected $savedReport;

        /**
         * Override to support security checks on user rights/permissions.  The savedReport is needed for this
         */
        public function __construct($controllerId, $moduleId, Report $model, $title = null, SavedReport$savedReport)
        {
            parent::__construct($controllerId, $moduleId, $model);
            $this->savedReport = $savedReport;
        }

        /**
         * @param $model
         */
        public static function assertModelIsValid($model)
        {
            assert('$model instanceof Report');
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                           // array('type'  => 'ReportDetailsLink',
                           //     'htmlOptions' => array('class' => 'icon-details')),
                            array('type'  => 'ReportOptionsLink',
                                  'htmlOptions' => array('class' => 'icon-edit')),
                            array('type'  => 'ReportExportLink',
                                  'htmlOptions' => array('class' => 'icon-export')),
                            array('type'  => 'ReportTogglePortletsLink',
                                  'htmlOptions' => array('class' => 'hasCheckboxes'),
                                  'hasRuntimeFilters' => 'eval:$this->model->hasRuntimeFilters()',
                                  'hasChart'          => 'eval:$this->model->hasChart()'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * @return string
         * @throws NotSupportedException if the Report is new
         */
        public function getTitle()
        {
            if ($this->model->id > 0)
            {
                $moduleClassName = $this->model->moduleClassName;
                $typesAndLabels  = Report::getTypeDropDownArray();
                return strval($this->model) . ' - ' .
                       Zurmo::t('ReportsModule', '{moduleLabel} {typeLabel} Report',
                              array('{moduleLabel}' => $moduleClassName::getModuleLabelByTypeAndLanguage('Singular'),
                                    '{typeLabel}'   => $typesAndLabels[$this->model->type]));
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function registerScripts()
        {
            $script = '$(".ReportSQLForPortletView").hide();';
            Yii::app()->getClientScript()->registerScript('ReportPortletsDefaultHideScript', $script);
            Yii::app()->getClientScript()->registerCoreScript('bbq');
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $content = $this->renderTitleContent();
            $content .= '<div class="view-toolbar-container clearfix"><div class="view-toolbar">';
            $content .= $this->renderActionElementBar(false);
            $content .= '</div></div>';
            $this->registerScripts();
            return $content;
        }

        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            if (get_class($element) == 'ReportExportLinkActionElement' &&
               ($this->model->getType() == Report::TYPE_MATRIX || !$this->userCanExportReport()))
            {
                return false;
            }
            if (get_class($element) == 'ReportOptionsLinkActionElement')
            {
                $userCanEditReport   = $this->userCanEditReport();
                $userCanDeleteReport = $this->userCanDeleteReport();
                if (!$userCanEditReport && !$userCanDeleteReport)
                {
                    return false;
                }
                if (!$userCanEditReport)
                {
                    $element->setHideEdit();
                }
                if (!$userCanDeleteReport)
                {
                    $element->setHideDelete();
                }
            }
            return true;
        }

        protected function userCanEditReport()
        {
            return ActionSecurityUtil::canCurrentUserPerformAction('Edit', $this->savedReport);
        }

        protected function userCanDeleteReport()
        {
            return ActionSecurityUtil::canCurrentUserPerformAction('Delete', $this->savedReport);
        }

        protected function userCanExportReport()
        {
            return ActionSecurityUtil::canCurrentUserPerformAction('Export', $this->savedReport);
        }
    }
?>