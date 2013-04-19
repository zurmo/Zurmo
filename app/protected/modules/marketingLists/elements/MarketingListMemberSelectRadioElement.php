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

    class MarketingListMemberSelectRadioElement extends Element
    {
        public function getEditableHtmlOptions()
        {
            $htmlOptions = array(
                'name'      => $this->getEditableInputName(),
                'id'        => $this->getEditableInputId(),
                'class'     => $this->attribute,
                'separator' => ' ',
                'template'  => '{input}{label}',
            );
            return $htmlOptions;
        }

        /**
         * Renders the setting as a radio list.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $this->registerScripts();
            assert('$this->model instanceof MarketingListMemberSelectForm');
            $content = $this->form->radioButtonList(
                                            $this->model,
                                            $this->attribute,
                                            $this->getArray(),
                                            $this->getEditableHtmlOptions()
                                        );
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function registerScripts()
        {
            $selectContactBoxId    = $this->getSelectContactOrLeadSearchBoxId();
            $selectReportBoxId     = $this->getSelectReportSearchBoxId();
            Yii::app()->clientScript->registerScript($this->getListViewGridId() . '-toggleSelectContactOrReportSearchBoxVisibility', '
                // TODO: @Shoaibi/@Jason: Medium: Get rid of global js variable from here
                function toggleSelectContactOrReportByRadioButtonId()
                {
                    var selectContactBoxId  = "#' . $selectContactBoxId . '";
                    var selectReportBoxId   = "#' . $selectReportBoxId . '";
                    var hideBoxId           = selectReportBoxId;
                    var showBoxId           = selectContactBoxId;
                    var radioButtonIdSuffix = window.selectContactOrReportRadioButtonSuffix;
                    var radioButtonIdSuffix = (radioButtonIdSuffix === undefined) ? 0 : radioButtonIdSuffix;
                    var radioButtonId       = "#' . $this->getEditableInputId() . '_" + radioButtonIdSuffix;
                    if ($(radioButtonId).attr("checked") !== "checked")
                    {
                        $(radioButtonId).attr("checked", "checked");
                    }
                    if (radioButtonIdSuffix == 1)
                    {
                        showBoxId = selectReportBoxId;
                        hideBoxId = selectContactBoxId;
                    }
                    $(hideBoxId).hide();
                    $(showBoxId).show();
                }
                toggleSelectContactOrReportByRadioButtonId(); // call it on page load to ensure proper radio buttons checked and divs shown
                $(".' . $this->attribute . '").unbind("change.action").bind("change.action", function(event)
                    {
                        radioButtonId       = ($(this)).attr("id");
                        window.selectContactOrReportRadioButtonSuffix = radioButtonId.charAt(radioButtonId.length - 1);
                        toggleSelectContactOrReportByRadioButtonId();
                    }
                );
            ');
        }

        protected function renderLabel()
        {
            return null;
        }

        protected function getArray()
        {
            $data = array(
                        Zurmo::t('MarketingListsModule', 'From Contacts/Leads'),
                        Zurmo::t('MarketingListsModule', 'From a Report'),
                    );
            return $data;
        }

        protected function getSelectContactOrLeadSearchBoxId()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'selectContactSearchBoxId');
        }

        protected function getSelectReportSearchBoxId()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'selectReportSearchBoxId');
        }
    }
?>