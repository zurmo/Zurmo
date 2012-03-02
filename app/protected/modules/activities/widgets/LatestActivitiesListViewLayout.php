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
     * Latest activity list view layout used for displaying rows of data in a list format.
     */
    class LatestActivitiesListViewLayout extends LatestActivitiesBaseViewLayout
    {
        protected function renderListViewLayout()
        {
            $content  = '<div class ="latest-activity-feed">';
            $content .= '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:50px" /><col style="width:100%" /><col/>';
            $content .= '</colgroup>';
            $content .= '<tbody id="' . $this->getViewContainerId() . '" >';
            $data = $this->dataProvider->getData();
            if (count($data) == 0)
            {
                $content .= '<tr>';
                $content .= '<td colspan="3"><span class="empty">';
                $content .= Yii::t('Default', 'No Activities found.');
                $content .= '</span></td>';
                $content .= '</tr>';
            }
            else
            {
                foreach ($data as $model)
                {
                    $mashableActivityRules = MashableActivityRulesFactory::createMashableActivityRulesByModel(
                                                 get_class($model));
                    $orderByAttributeName = $mashableActivityRules->getLatestActivitiesOrderByAttributeName();
                    $content .= '<tr>';
                    $content .= '<td>';
                    $content .= '<span class="'.get_class($model).'"></span>';
                    $content .= '</td>';
                    $content .= '<td>';
                    $content .= '<strong>'.DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                    $model->{$orderByAttributeName}, 'long', null) . '</strong><br/>';
                    $modelDisplayString = strval($model);
                    if (strlen($modelDisplayString) > 200)
                    {
                        $modelDisplayString = substr($modelDisplayString, 0, 200) . '...';
                    }
                    $params = array('label' => $modelDisplayString, 'redirectUrl' => $this->redirectUrl);
                    $moduleClassName = $model->getModuleClassName();
                    $moduleId        = $moduleClassName::getDirectoryName();
                    $element  = new DetailsLinkActionElement('default', $moduleId, $model->id, $params);
                    $content .= $element->render() . '<br/>';
                    //$content .= Yii::t('Default', 'by') . '&#160;' . Yii::app()->format->text($model->createdByUser);
                    $extraContent = $mashableActivityRules->getLatestActivityExtraDisplayStringByModel($model);
                    if ($extraContent)
                    {
                        $content .= '<br/>' . $extraContent;
                    }
                    $content .= '</td>';
                    $content .= '</tr>';
                }
                $content .= '<tr>';
                $content .= '<td colspan="2">';
                $content .= $this->renderPaginationContent();
                $content .= '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            //$content .= $this->renderPaginationContent();
            $content .= '</div>';
            return $content;
        }
    }
?>
