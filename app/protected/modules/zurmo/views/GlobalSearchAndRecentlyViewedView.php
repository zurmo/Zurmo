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
     * Base view for displaying a global search user interface and a recently viewed user interface.
     */
    class GlobalSearchAndRecentlyViewedView extends View
    {
        protected function renderContent()
        {
            $content  = '<div><div style="float:right;">' . $this->renderRecentlyViewedContent() . '</div>';
            $content .= '<div style="margin: 0 auto;">' . $this->renderGlobalSearchContent() . '</div></div>&#160;';
            return $content;
        }

        protected function renderRecentlyViewedContent()
        {
            $content     = '&#160;&#160;&#160;<span id="recently-viewed" class="tooltip">';
            $content    .= Yii::t('Default', 'Recently Viewed') . '</span>';

            Yii::import('application.extensions.qtip.QTip');
            $imageSourceUrl = Yii::app()->baseUrl . '/themes/default/images/loading.gif';
            $qtip   = new QTip();
            $params = array(
                'content'  => array('text'    => CHtml::image($imageSourceUrl, Yii::t('Default', 'Loading')),
                                    'url'     => Yii::app()->createUrl('zurmo/default/recentlyViewed'),
                                    'title'   => array('text'   => 'Recently Viewed',
                                                       'button' => 'Close')),
                'show'     => array('when'    => 'click'),
                'hide'     => array('when'    => 'click'),
                'position' => array('corner'  => array(
                                'target'      => 'bottomRight',
                                'tooltip'	  => 'topRight')),
                'style'    => array('width'   =>  300));
            $qtip->addQTip("#recently-viewed", $params);
            return $content;
        }

        protected function renderGlobalSearchContent()
        {
            $imagePath = Yii::app()->baseUrl . '/themes/default/images/searchIcon.gif';
            $content                 = CHtml::image($imagePath, 'Search Icon');
            $hintMessage             = Yii::t('Default', 'Search by name, phone, or e-mail');
            $htmlOptions             = array('class'   => 'global-search global-search-hint',
                                             'onFocus' => 'js:$(this).removeClass("global-search-hint"); $(this).val("");',
                                             'onBlur'  => 'js:$(this).val("")');
            $cClipWidget             = new CClipWidget();
            $cClipWidget->beginClip('GlobalSearchElement');
            $cClipWidget->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'name'        => 'globalSearchInput',
                'id'          => 'globalSearchInput',
                'value'       => $hintMessage,
                'source'      => Yii::app()->createUrl('zurmo/default/globalSearchAutoComplete'),
                'htmlOptions' => $htmlOptions,
                'options'	  => array('select' => 'js: function(event, ui) {if(ui.item.href.length > 0)' .
                                                   '{window.location = ui.item.href;} return false;}')
            ));
            $cClipWidget->endClip();
            $content .= '&#160;' . $cClipWidget->getController()->clips['GlobalSearchElement'];
            $script = '$(".ui-autocomplete").position({
                            my: "right top",
                            at: "right bottom",
                            of: $("#globalSearchInput"),
                            collision: "flip flip"});';
            Yii::app()->clientScript->registerScript('GlobalSearchElementPosition', $script);
            return $content;
        }
    }
?>
