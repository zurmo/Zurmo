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
     * View for displaying a row of recipient information for an email message
     */
    class EmailMessageRecipientRowForWorkflowComponentView extends View
    {
        const REMOVE_LINK_CLASS_NAME = 'remove-dynamic-email-message-recipient-row-link';

        /**
         * @var bool
         */
        public    $addWrapper = true;

        /**
         * @var WorkflowEmailMessageRecipientToElementAdapter
         */
        protected $elementAdapter;

        /**
         * @var int
         */
        protected $rowNumber;

        /**
         * @var array
         */
        protected $inputPrefixData;

        /**
         * @return string
         */
        public static function getFormId()
        {
            return WizardView::getFormId();
        }

        /**
         * @param WorkflowEmailMessageRecipientToElementAdapter $elementAdapter
         * @param integer $rowNumber
         * @param array $inputPrefixData
         */
        public function __construct($elementAdapter, $rowNumber, $inputPrefixData)
        {
            assert('$elementAdapter instanceof WorkflowEmailMessageRecipientToElementAdapter');
            assert('is_int($rowNumber)');
            assert('is_array($inputPrefixData)');
            $this->elementAdapter                     = $elementAdapter;
            $this->rowNumber                          = $rowNumber;
            $this->inputPrefixData                    = $inputPrefixData;
        }

        /**
         * @return string
         */
        public function render()
        {
            return $this->renderContent();
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $content  = '<div>';
            $content .= $this->renderRecipientRowNumberLabel();
            $content .= $this->renderRecipientContent();
            $content .= '</div>';
            $content .= ZurmoHtml::link('—', '#', array('class' => self::REMOVE_LINK_CLASS_NAME));
            $content  = ZurmoHtml::tag('div', array('class' => "dynamic-sub-row dynamic-email-message-recipient-row"), $content);
            if ($this->addWrapper)
            {
                return ZurmoHtml::tag('li', array('class' => 'dynamic-sub-row'), $content);
            }
            return $content;
        }

        /**
         * @return string
         */
        protected function renderRecipientRowNumberLabel()
        {
            return ZurmoHtml::tag('span', array('class' => 'dynamic-row-number-label dynamic-email-message-recipient-row-number-label'),
                   ($this->rowNumber + 1) . '.');
        }

        /**
         * @return string
         */
        protected function renderRecipientContent()
        {
            $content = $this->elementAdapter->getContent();
            return $content;
        }
    }
?>