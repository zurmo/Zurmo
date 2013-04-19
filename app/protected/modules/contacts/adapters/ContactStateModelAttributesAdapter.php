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
     * Adapter to set attributes from a contact state attribute form.
     */
    class ContactStateModelAttributesAdapter extends ModelAttributesAdapter
    {
        public function setAttributeMetadataFromForm(AttributeForm $attributeForm)
        {
            $modelClassName                  = get_class($this->model);
            $attributeName                   = $attributeForm->attributeName;
            $attributeLabels                 = $attributeForm->attributeLabels;
            $elementType                     = $attributeForm->getAttributeTypeName();
            $isRequired                      = (boolean)$attributeForm->isRequired;
            $isAudited                       = (boolean)$attributeForm->isAudited;
            $contactStatesData               = $attributeForm->contactStatesData;
            $contactStatesLabels             = $attributeForm->contactStatesLabels;
            $startingStateOrder              = (int)$attributeForm->startingStateOrder;
            $contactStatesDataExistingValues = $attributeForm->contactStatesDataExistingValues;
            if ($contactStatesDataExistingValues == null)
            {
                $contactStatesDataExistingValues = array();
            }

            if ($attributeForm instanceof ContactStateAttributeForm)
            {
                //update order on existing states.
                //delete removed states
                $states = ContactState::getAll('order');
                $stateNames = array();
                foreach ($states as $state)
                {
                    $stateNames[] = $state->name;
                    if (in_array($state->name, $contactStatesData))
                    {
                        $state->order = array_search($state->name, $contactStatesData);
                        $state->serializedLabels = $this->makeSerializedLabelsByLabelsAndOrder($contactStatesLabels,
                                                                                               (int)$state->order);
                        $saved        = $state->save();
                        assert('$saved');
                    }
                    elseif (in_array($state->name, $contactStatesDataExistingValues))
                    {
                        $order                   = array_search($state->name, $contactStatesDataExistingValues);
                        $state->name             = $contactStatesData[$order];
                        $state->order            = $order;
                        $state->serializedLabels = $this->makeSerializedLabelsByLabelsAndOrder($contactStatesLabels,
                                                                                               (int)$state->order);
                        $saved                   = $state->save();
                        assert('$saved');
                    }
                    else
                    {
                        $state->delete();
                    }
                }
                //add new states with correct order.
                foreach ($contactStatesData as $order => $name)
                {
                    if (!in_array($name, $stateNames))
                    {
                        $state                   = new ContactState();
                        $state->name             = $name;
                        $state->order            = $order;
                        $state->serializedLabels = $this->makeSerializedLabelsByLabelsAndOrder($contactStatesLabels,
                                                                                               (int)$order);
                        $saved                   = $state->save();
                        assert('$saved');
                    }
                }
                //Set starting state by order.
                ContactsUtil::setStartingStateByOrder($startingStateOrder);
                ModelMetadataUtil::addOrUpdateRelation($modelClassName,
                                                       $attributeName,
                                                       $attributeLabels,
                                                       $elementType,
                                                       $isRequired,
                                                       $isAudited,
                                                       'ContactState');
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function makeSerializedLabelsByLabelsAndOrder($contactStatesLabels, $order)
        {
            assert('is_array($contactStatesLabels) || $contactStatesLabels == null');
            assert('is_int($order)');
            if ($contactStatesLabels == null)
            {
                return null;
            }
            $unserializedLabels = array();
            foreach ($contactStatesLabels as $language => $languageLabelsByOrder)
            {
                if (isset($languageLabelsByOrder[$order]))
                {
                    $unserializedLabels[$language] = $languageLabelsByOrder[$order];
                }
            }
            if (count($unserializedLabels) == 0)
            {
                return null;
            }
            return serialize($unserializedLabels);
        }
    }
?>
