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

    class MeetingsUtil
    {
        public static function renderDaySumaryContent(Meeting $meeting, $link)
        {
            $content = null;
            $content .= '<h3>' . $meeting->name . '<span>' . $link . '</span></h3>';
            $content .= DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($meeting->startDateTime);
            $localEndDateTime = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($meeting->endDateTime);
            if($localEndDateTime != null)
            {
                $content .= ' - ' . $localEndDateTime;
            }
            $content .= '<br/>';
            $content .= self::renderActivityItemsContentsExcludingContacts($meeting);
            if(count($meeting->activityItems) > 0)
            {
                $contactsContent = null;
                $contactLabels = self::getExistingContactRelationsLabels($meeting->activityItems);
                foreach ($contactLabels as $label)
                {
                    if ($contactsContent != null)
                    {
                        $contactsContent .= ', ';
                    }
                    $contactsContent .= $label;
                }
                $content .= $contactsContent . '<br/>';
            }
            if($meeting->description != null)
            {
                $content .= '<br/>';
                $content .= Yii::t('Default', 'Description') . ':<br/>';
                $content .= $meeting->description;
            }
            return $content;
        }

        protected static function getExistingContactRelationsLabels($activityItems)
        {
            $existingContacts = array();
            $modelDerivationPathToItem = ActivitiesUtil::getModelDerivationPathToItem('Contact');
            foreach ($activityItems as $item)
            {
                try
                {
                    $contact = $item->castDown(array($modelDerivationPathToItem));
                    if (get_class($contact) == 'Contact')
                    {
                        $existingContacts[] = strval($contact);
                    }
                }
                catch (NotFoundException $e)
                {
                    //do nothing
                }
            }
            return $existingContacts;
        }

        protected static function getNonExistingContactRelationsLabels($activityItems)
        {
            $existingContacts = array();
            $modelDerivationPathToItem = ActivitiesUtil::getModelDerivationPathToItem('Contact');
            foreach ($activityItems as $item)
            {
                try
                {
                    $contact = $item->castDown(array($modelDerivationPathToItem));
                    if (get_class($contact) == 'Contact')
                    {
                        $existingContacts[] = strval($contact);
                    }
                }
                catch (NotFoundException $e)
                {
                    //do nothing
                }
            }
            return $existingContacts;
        }

        protected static function renderActivityItemsContentsExcludingContacts(Meeting $meeting)
        {
            $activityItemsModelClassNamesData = ActivitiesUtil::getActivityItemsModelClassNamesDataExcludingContacts();
            $content = null;
            foreach ($activityItemsModelClassNamesData as $relationModelClassName)
            {
                $activityItemForm = null;
                //ASSUMES ONLY A SINGLE ATTACHED ACTIVITYITEM PER RELATION TYPE.
                foreach ($meeting->activityItems as $item)
                {
                    try
                    {
                        $modelDerivationPathToItem = ActivitiesUtil::getModelDerivationPathToItem($relationModelClassName);
                        $castedDownModel           = $item->castDown(array($modelDerivationPathToItem));
                        if ($content != null)
                        {
                            $content .= ', ';
                        }
                        $content .= strval($castedDownModel);
                        break;
                    }
                    catch (NotFoundException $e)
                    {
                    }
                }
            }
            if($content != null)
            {
                $content .= '<br/>';
            }
            return $content;
        }
    }
?>