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
     * A chart view for displaying a chart showing all opportunities by sales stage.
     *
     */
    class OpportunitiesByStageChartView extends ChartView implements PortletViewInterface
    {
        public function renderContent()
        {
            $accessContent = $this->resolveContentIfCurrentUserCanAccessChartByModule(
                                        'OpportunitiesModule', 'OpportunitiesModulePluralLabel');
            if ($accessContent != null)
            {
                return $accessContent;
            }
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("Chart");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.FusionChart', array(
                    'id'      => $this->uniqueLayoutId,
                    'dataUrl' => Yii::app()->createUrl('/home/defaultPortlet/makeChartXML',
                                    array('portletId' => $this->params['portletId'], 'chartLibraryName' => 'Fusion')),
                    'type'    => $this->resolveViewAndMetadataValueByName('type'),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Chart'];
        }

        public static function getDefaultMetadata()
        {
            return array(
                'perUser' => array(
                    'title' => Yii::t('Default', 'Opportunities By Sales Stage'),
                    'type'  => 'Column2D',
                ),
                'global' => array(
                ),
            );
        }

        /**
         * What kind of PortletRules this view follows
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'Chart';
        }

        /**
         * The view's module class name.
         */
        public static function getModuleClassName()
        {
            return 'OpportunitiesModule';
        }

        public function getChartDataProviderType()
        {
            return 'OpportunitiesByStage';
        }
    }
?>
