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
     * User interface element for managing related model relations for conversation participants. This class supports a HAS_MANY
     * specifically for the 'user' or 'contact' relation. This is utilized by the conversation model.
     *
     */
    class MultiplePeopleForConversationElement extends Element implements DerivedElementInterface
    {
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof Conversation');
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModelElement");
            $cClipWidget->widget('application.core.widgets.MultiSelectAutoComplete', array(
                'name'        => $this->getNameForIdField(),
                'id'          => $this->getIdForIdField(),
                'jsonEncodedIdsAndLabels'   => CJSON::encode($this->getExistingPeopleRelationsIdsAndLabels()),
            //change source url to something else.
                'sourceUrl'   => Yii::app()->createUrl('users/default/autoCompleteForMultiSelectAutoComplete'),
                'htmlOptions' => array(
                    'disabled' => $this->getDisabledValue(),
                    ),
                'hintText' => Zurmo::t('ConversationsModule', 'Type a User\'s name'),
                'onAdd'    => $this->getOnAddContent(),
                'onDelete' => $this->getOnDeleteContent(),
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['ModelElement'];
            return $content;
        }

        protected function getOnAddContent()
        {
        }

        protected function getOnDeleteContent()
        {
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
            return Yii::app()->format->text(Zurmo::t('ConversationsModule', 'Participants'));
        }

         public static function getDisplayName()
        {
            return Zurmo::t('ConversationsModule', 'Participants');
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
                return 'ConversationParticipantsForm[itemIds]';
        }

        protected function getIdForIdField()
        {
            return 'ConversationParticipantsForm_item_ids';
        }

        protected function getExistingPeopleRelationsIdsAndLabels()
        {
            $existingPeople = array(
                                array(  'id'       => $this->model->owner->getClassId('Item'),
                                        'name'     => strval($this->model->owner),
                                        'readonly' => true));
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Contact');
            foreach ($this->model->conversationParticipants as $participant)
            {
                try
                {
                    $contact = $participant->person->castDown(array($modelDerivationPathToItem));
                    if (get_class($contact) == 'Contact')
                    {
                        $existingPeople[] = array('id' => $contact->getClassId('Item'),
                                                    'name' => strval($contact));
                    }
                    else
                    {
                        throw new NotFoundException();
                    }
                }
                catch (NotFoundException $e)
                {
                    $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
                    try
                    {
                        $user = $participant->person->castDown(array($modelDerivationPathToItem));
                        //Owner is always added first.
                        if (get_class($user) == 'User' && $user->id != $this->model->owner->id)
                        {
                            $readOnly = false;
                            $existingPeople[] = array('id'       => $user->getClassId('Item'),
                                                      'name'     => strval($user),
                                                      'readonly' => $readOnly);
                        }
                    }
                    catch (NotFoundException $e)
                    {
                        //This means the item is not a recognized or expected supported model.
                        throw new NotSupportedException();
                    }
                }
            }
            return $existingPeople;
        }
    }
?>