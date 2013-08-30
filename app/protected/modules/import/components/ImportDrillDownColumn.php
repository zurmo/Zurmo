<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/
    Yii::import('zii.widgets.grid.CGridColumn');

    /**
     * Column class for managing the drill down link column when viewing analysis or imported record results.
     * The expanded drill down can show information about columns with problems or information about why a row will be
     * or was skipped.
     */
    class ImportDrillDownColumn extends CGridColumn
    {
        public $expandableContentType;

        public function init()
        {
            // Begin Not Coding Standard
            $script = <<<END
jQuery('.drillDownExpandAndLoadLink').unbind('click'); jQuery('.drillDownExpandAndLoadLink').live('click', function(){
    $(this).hide();
    $(this).parent().find('.drillDownCollapseLink').first().show();
    $(this).parentsUntil('tr').parent().next().show();
    var loadDivId = $(this).parentsUntil('tr').parent().addClass('expanded-row').next().find('.drillDownContent').attr('id');
});
jQuery('.drillDownExpandLink').unbind('click'); jQuery('.drillDownExpandLink').live('click', function(){
    $(this).hide();
    $(this).parent().find('.drillDownCollapseLink').first().show();
    $(this).parentsUntil('tr').parent().addClass('expanded-row').next().show();
});
jQuery('.drillDownCollapseLink').unbind('click');jQuery('.drillDownCollapseLink').live('click', function(){
    $(this).hide();
    $(this).parent().find('.drillDownExpandLink').first().show();
    $(this).parentsUntil('tr').parent().removeClass('expanded-row').next().hide();
});
END;
            // End Not Coding Standard
            Yii::app()->getClientScript()->registerScript(__CLASS__ . 'SummationDrillDownToggleScript', $script);
        }

        /**
         * (non-PHPdoc)
         * @see CCheckBoxColumn::renderDataCellContent()
         */
        protected function renderDataCellContent($row, $data)
        {
            $dataParams               = array('rowId' => $data->getId());
            $expandAndLoadLinkContent = ZurmoHtml::tag('span', array('class' => 'drillDownExpandAndLoadLink drilldown-link'),
                                                                     'G');
            $expandLinkContent        = ZurmoHtml::tag('span', array('class' => 'drillDownExpandLink drilldown-link',
                                                                     'style' => "display:none;"), 'G');
            $collapseLinkContent      = ZurmoHtml::tag('span', array('class' => 'drillDownCollapseLink drilldown-link',
                                                                     'style' => "display:none;"), '&divide;');
            if ($this->hasExpandableContent($data))
            {
                echo $expandAndLoadLinkContent . $expandLinkContent . $collapseLinkContent;
            }
        }

        protected function hasExpandableContent($data)
        {
            $content = null;
            if ($this->expandableContentType == ImportTempTableListView::EXPANDABLE_ANALYSIS_CONTENT_TYPE &&
                $data->serializedAnalysisMessages != null)
            {
                $analysisMessages = unserialize($data->serializedAnalysisMessages);
                if (count($analysisMessages) > 0)
                {
                    return true;
                }
            }
            if ($this->expandableContentType == ImportTempTableListView::EXPANDABLE_IMPORT_RESULTS_CONTENT_TYPE &&
                $data->serializedMessages != null)
            {
                $resultMessages = unserialize($data->serializedMessages);
                if (count($resultMessages) > 0)
                {
                    return true;
                }
            }
            return false;
        }
    }
?>
