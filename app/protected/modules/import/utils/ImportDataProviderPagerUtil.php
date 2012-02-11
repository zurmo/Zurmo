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
     * Helper class for rendering the pager content for the sample column data.  This is used by the import mapping
     * step to allow a user to toggle between different sample rows while deciding on the mappings to do.
     */
    class ImportDataProviderPagerUtil
    {
        public static function renderPagerAndHeaderTextContent(ImportDataProvider $dataProvider, $url)
        {
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('bbq');
            $currentPage = $dataProvider->getPagination()->getCurrentPage();
            $pageCount   = $dataProvider->getPagination()->getPageCount();
            $content = null;
            $content .= Yii::t('Default', 'Sample Row');
            $previousStyle = null;
            if (!($currentPage > 0))
            {
                $previousStyle = 'display:none;';
            }
            $nextStyle     = null;
            if (!(($currentPage + 1) < $pageCount))
            {
                $nextStyle = 'display:none;';
            }
            $content .= '&#160;';
            $content .= self::renderAjaxLink('sample-column-header-previous-page-link', Yii::t('Default', 'Previous'),
                                             $url, $dataProvider->getPagination()->pageVar, $currentPage, $previousStyle);
            $content .= '&#160;';
            $content .= self::renderAjaxLink('sample-column-header-next-page-link', Yii::t('Default', 'Next'),
                                             $url, $dataProvider->getPagination()->pageVar, $currentPage + 2, $nextStyle);
            return $content;
        }

        protected static function renderAjaxLink($id, $label, $url, $pageVar, $page, $style)
        {
            assert('is_string($id)');
            assert('is_string($label)');
            assert('is_string($url)');
            assert('is_string($pageVar)');
            assert('is_int($page)');
            assert('is_string($style) || $style == null');
            $urlScript = 'js:$.param.querystring("' . $url . '", "' .
                         $pageVar . '=" + $(this).attr("href"))';
            // Begin Not Coding Standard
            return       CHtml::ajaxLink($label, $urlScript,
                         array('type' => 'GET',
                               'dataType' => 'json',
                               'success' => 'js:function(data){
                                $.each(data, function(key, value){
                                    $("#" + key).html(value);
                                });
                              }'),
                         array('id' => $id, 'href' => $page, 'style' => $style));
            // End Not Coding Standard
        }
    }
?>