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

    class MeetingsUtil
    {
        public static function renderDaySummaryContent(Meeting $meeting, $link)
        {
            $content = null;
            $content .= '<h3>' . $meeting->name . '<span>' . $link . '</span></h3>';
            $content .= DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($meeting->startDateTime);
            $localEndDateTime = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($meeting->endDateTime);
            if ($localEndDateTime != null)
            {
                $content .= ' - ' . $localEndDateTime;
            }
            $content .= '<br/>';
            $content .= self::renderActivityItemsContentsExcludingContacts($meeting);
            if (count($meeting->activityItems) > 0)
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
            if ($meeting->description != null)
            {
                $content .= '<br/>';
                $content .= Zurmo::t('MeetingsModule', 'Description') . ':<br/>';
                $content .= $meeting->description;
            }
            return $content;
        }

        protected static function getExistingContactRelationsLabels($activityItems)
        {
            $existingContacts = array();
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Contact');
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
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Contact');
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
                        $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($relationModelClassName);
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
            if ($content != null)
            {
                $content .= '<br/>';
            }
            return $content;
        }
    }
?>