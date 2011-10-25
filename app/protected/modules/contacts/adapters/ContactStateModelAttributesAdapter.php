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
     * Adapter to set attributes from a contact state attribute form.
     */
    class ContactStateModelAttributesAdapter extends ModelAttributesAdapter
    {
        public function setAttributeMetadataFromForm(AttributeForm $attributeForm)
        {
            $modelClassName      = get_class($this->model);
            $attributeName       = $attributeForm->attributeName;
            $attributeLabels     = $attributeForm->attributeLabels;
            $elementType         = $attributeForm->getAttributeTypeName();
            $isRequired          = (boolean)$attributeForm->isRequired;
            $isAudited           = (boolean)$attributeForm->isAudited;
            $contactStatesData   = $attributeForm->contactStatesData;
            $contactStatesLabels = $attributeForm->contactStatesLabels;
            $startingStateOrder  = (int)$attributeForm->startingStateOrder;

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
            if($contactStatesLabels == null)
            {
                return null;
            }
            $unserializedLabels = array();
            foreach($contactStatesLabels as $language => $languageLabelsByOrder)
            {
                if(isset($languageLabelsByOrder[$order]))
                {
                    $unserializedLabels[$language] = $languageLabelsByOrder[$order];
                }
            }
            if(count($unserializedLabels) == 0)
            {
                return null;
            }
            return serialize($unserializedLabels);
        }
    }
?>
