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
     * User interface element for managing related model relations for activities. This class supports a HAS_MANY
     * specifically for the 'contact' relation. This is utilized by the meeting model.
     *
     */
    class MultipleContactsForMeetingElement extends Element implements DerivedElementInterface
    {
        protected function renderControlNonEditable()
        {
            $content  = null;
            $contacts = $this->getExistingContactRelationsIdsAndLabels();
            foreach ($contacts as $contactData)
            {
                if ($content != null)
                {
                    $content .= ', ';
                }
                $content .= $contactData['name'];
            }
            return $content;
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof Activity');
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModelElement");
            $cClipWidget->widget('application.core.widgets.MultiSelectAutoComplete', array(
                'name'        => $this->getNameForIdField(),
                'id'          => $this->getIdForIdField(),
                'jsonEncodedIdsAndLabels'   => CJSON::encode($this->getExistingContactRelationsIdsAndLabels()),
                'sourceUrl'   => Yii::app()->createUrl('contacts/variableContactState/autoCompleteAllContactsForMultiSelectAutoComplete'),
                'htmlOptions' => array(
                    'disabled' => $this->getDisabledValue(),
                    ),
                'hintText' => Zurmo::t('MeetingsModule', 'Type a ContactsModuleSingularLowerCaseLabel ' .
                                                'or LeadsModuleSingularLowerCaseLabel: name or email address',
                                LabelUtil::getTranslationParamsForAllModules())
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['ModelElement'];
            return $content;
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Zurmo::t('MeetingsModule', 'Attendees'));
        }

         public static function getDisplayName()
        {
            return Zurmo::t('MeetingsModule', 'Related ContactsModulePluralLabel and LeadsModulePluralLabel',
                       LabelUtil::getTranslationParamsForAllModules());
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        protected function getNameForIdField()
        {
                return 'ActivityItemForm[Contact][ids]';
        }

        protected function getIdForIdField()
        {
            return 'ActivityItemForm_Contact_ids';
        }

        protected function getExistingContactRelationsIdsAndLabels()
        {
            $existingContacts = array();
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Contact');
            foreach ($this->model->activityItems as $item)
            {
                try
                {
                    $contact = $item->castDown(array($modelDerivationPathToItem));
                    if (get_class($contact) == 'Contact')
                    {
                        $existingContacts[] = array('id' => $contact->id,
                                                    'name' => self::renderHtmlContentLabelFromContactAndKeyword($contact, null));
                    }
                }
                catch (NotFoundException $e)
                {
                    //do nothing
                }
            }
            return $existingContacts;
        }

        /**
         * Given a contact model and a keyword, render the strval of the contact and the matched email address
         * that the keyword matches. If the keyword does not match any email addresses on the contact, render the
         * primary email if it exists. Otherwise just render the strval contact.
         * @param object $contact - model
         * @param string $keyword
         */
        public static function renderHtmlContentLabelFromContactAndKeyword($contact, $keyword)
        {
            assert('$contact instanceof Contact && $contact->id > 0');
            assert('$keyword == null || is_string($keyword)');

            if (substr($contact->secondaryEmail->emailAddress, 0, strlen($keyword)) === $keyword)
            {
                $emailAddressToUse = $contact->secondaryEmail->emailAddress;
            }
            else
            {
                $emailAddressToUse = $contact->primaryEmail->emailAddress;
            }
            if ($emailAddressToUse != null)
            {
                return strval($contact) . '&#160&#160<b>' . strval($emailAddressToUse) . '</b>';
            }
            else
            {
                return strval($contact);
            }
        }
    }
?>