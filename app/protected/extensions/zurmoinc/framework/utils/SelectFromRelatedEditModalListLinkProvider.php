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
     * Class utilized by 'select' modal popup in the edit views. If you are selecting a owner in an account edit view
     * as an example.  Provides link information in the modal popup for when you click on a name.
     */
    class SelectFromRelatedEditModalListLinkProvider extends ModalListLinkProvider
    {
        /**
         * Id of input field in display for saving back a selected
         * record from the modal list view.
         * @see $sourceIdFieldId
         */
        protected $sourceIdFieldId;

        /**
         * Name of input field in display for saving back a selected
         * record from the modal list view.
         * @see $sourceNameFieldId
         */
        protected $sourceNameFieldId;

        /**
         * sourceIdFieldName and sourceNameFieldId are needed to know
         * which fields in the parent form to populate data with
         * upon selecting a row in the listview
         *
         */
        public function __construct($sourceIdFieldId, $sourceNameFieldId)
        {
            assert('is_string($sourceIdFieldId)');
            assert('is_string($sourceNameFieldId)');
            $this->sourceIdFieldId   = $sourceIdFieldId;
            $this->sourceNameFieldId = $sourceNameFieldId;
        }

        public function getLinkString($attributeString)
        {
            $string  = 'CHtml::link(';
            $string .= $attributeString . ', ';
            $string .= '"javascript:transferModalValues(\"#modalContainer\", " . CJavaScript::encode(array(\'' . $this->sourceIdFieldId . '\' => $data->id, \'' . $this->sourceNameFieldId . '\' => strval(' . $attributeString . '))) . ");"';
            $string .= ')';
            return $string;
        }
    }
?>