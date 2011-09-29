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
     * Helper functions to assist with testing designer walkthroughs specifically for meeting layouts.
     */
    class MeetingsDesignerWalkthroughHelperUtil
    {
        public static function getMeetingEditAndDetailsViewLayoutWithAllCustomFieldsPlaced()
        {
            return array(
                    'panels' => array(
                        array(
                            'title' => 'Panel Title',
                            'panelDetailViewOnly' => 1,
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'name',
                                        ),
                                        array(
                                            'element' => 'startDateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'owner',
                                        ),
                                        array(
                                            'element' => 'location', // Not Coding Standard
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'endDateTime',
                                        ),
                                        array(
                                            'element' => 'ActivityItemsExcludingContacts',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'category',
                                        ),
                                        array(
                                            'element' => 'MultipleContactsForMeeting', // Not Coding Standard
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'description',
                                        ),
                                        array(
                                            'element' => 'Null', // Not Coding Standard
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'detailViewOnly' => true,
                                            'element' => 'DateTimeCreatedUser',
                                        ),
                                        array(
                                            'detailViewOnly' => true,
                                            'element' => 'DateTimeModifiedUser',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'checkbox',
                                        ),
                                        array(
                                            'element' => 'currency',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'date',
                                        ),
                                        array(
                                            'element' => 'datetime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'decimal',
                                        ),
                                        array(
                                            'element' => 'picklist',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'integer',
                                        ),
                                        array(
                                            'element' => 'multiselect',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'phone',
                                        ),
                                        array(
                                            'element' => 'radio',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'text',
                                        ),
                                        array(
                                            'element' => 'textarea',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'url',
                                        ),
                                        array(
                                            'element' => 'Null', // Not Coding Standard
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
            );
        }

        /**
         * Can be use for relatedListView.
         */
        public static function getMeetingsRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced()
        {
            return array(
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'name',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'owner',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'startDateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'endDateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'location',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'category',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'description',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'createdDateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'modifiedDateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'createdByUser',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'modifiedByUser',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'checkbox',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'currency',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'date',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'datetime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'decimal',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'picklist',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'integer',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'multiselect',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'phone',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'radio',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'text',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'textarea',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'url',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
            );
        }
    }
?>