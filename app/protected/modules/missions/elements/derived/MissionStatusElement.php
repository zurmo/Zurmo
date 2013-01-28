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
     * Display the mission status with the action button when applicable.
     */
    class MissionStatusElement extends Element implements DerivedElementInterface
    {
        protected function renderEditable()
        {
            throw NotSupportedException();
        }

        protected function renderControlEditable()
        {
            throw NotSupportedException();
        }

        /**
         * Render the full name as a non-editable display
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            assert('$this->attribute == "status"');
            assert('$this->model instanceof Mission');
            return self::renderStatusTextAndActionArea($this->model);
        }

        public static function renderStatusTextAndActionArea(Mission $mission)
        {
            $statusText        = self::renderStatusTextContent($mission);
            $statusAction      = self::renderStatusActionContent($mission, self::getStatusChangeDivId($mission->id));
            if ($statusAction != null)
            {
                $content = $statusAction;
            }
            else
            {
                $content = $statusText;
            }
            return ZurmoHtml::tag('div', array('id' => self::getStatusChangeDivId($mission->id), 'class' => 'missionStatusChangeArea'), $content);
        }

        public static function getStatusChangeDivId($missionId)
        {
            return  'MissionStatusChangeArea-' . $missionId;
        }

        public static function renderStatusTextContent(Mission $mission)
        {
            if ($mission->status == Mission::STATUS_AVAILABLE)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('MissionsModule', 'Available'), 'mission-status');
            }
            elseif ($mission->status == Mission::STATUS_TAKEN)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('MissionsModule', 'In Progress'), 'mission-status');
            }
            elseif ($mission->status == Mission::STATUS_COMPLETED)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('MissionsModule', 'Awaiting Acceptance'), 'mission-status');
            }
            elseif ($mission->status == Mission::STATUS_REJECTED)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('MissionsModule', 'Rejected'), 'mission-status');
            }
            elseif ($mission->status == Mission::STATUS_ACCEPTED)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('MissionsModule', 'Accepted'), 'mission-status');
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public static function renderStatusActionContent(Mission $mission, $updateDivId)
        {
            assert('is_string($updateDivId)');
            if ($mission->status == Mission::STATUS_AVAILABLE &&
               !$mission->owner->isSame(Yii::app()->user->userModel))
            {
                return self::renderAjaxStatusActionChangeLink(Mission::STATUS_TAKEN, $mission->id,
                                                              Zurmo::t('MissionsModule', 'Start'), $updateDivId);
            }
            elseif ($mission->status == Mission::STATUS_TAKEN &&
                   $mission->takenByUser->isSame(Yii::app()->user->userModel))
            {
                return self::renderAjaxStatusActionChangeLink(Mission::STATUS_COMPLETED, $mission->id,
                                                              Zurmo::t('MissionsModule', 'Complete'), $updateDivId);
            }
            elseif ($mission->status == Mission::STATUS_COMPLETED &&
                   $mission->owner->isSame(Yii::app()->user->userModel))
            {
                $content  = self::renderAjaxStatusActionChangeLink(      Mission::STATUS_ACCEPTED, $mission->id,
                                                                         Zurmo::t('MissionsModule', 'Accept'), $updateDivId);
                $content .= ' ' . self::renderAjaxStatusActionChangeLink(Mission::STATUS_REJECTED, $mission->id,
                                                                         Zurmo::t('MissionsModule', 'Reject'), $updateDivId);
                return $content;
            }
            elseif ($mission->status == Mission::STATUS_REJECTED &&
                   $mission->takenByUser->isSame(Yii::app()->user->userModel))
            {
                return self::renderAjaxStatusActionChangeLink(Mission::STATUS_COMPLETED, $mission->id,
                                                              Zurmo::t('MissionsModule', 'Complete'), $updateDivId);
            }
        }

        protected static function renderAjaxStatusActionChangeLink($newStatus, $missionId, $label, $updateDivId)
        {
            assert('is_int($newStatus)');
            assert('is_int($missionId)');
            assert('is_string($label)');
            assert('is_string($updateDivId)');
            $url     =   Yii::app()->createUrl('missions/default/ajaxChangeStatus',
                                               array('status' => $newStatus, 'id' => $missionId));
            $aContent                = ZurmoHtml::wrapLink($label);
            return       ZurmoHtml::ajaxLink($aContent, $url,
                         array('type'       => 'GET',
                               'success'    => 'function(data){$("#' . $updateDivId . '").replaceWith(data)}'
                             ),
                         array('class'      => 'mission-change-status-link attachLoading z-button ' .
                                               self::resolveLinkSpecificCssClassNameByNewStatus($newStatus),
                                'namespace' => 'update',
                                'onclick'   => 'js:$(this).addClass("loading").addClass("loading-ajax-submit");
                                                        attachLoadingSpinner($(this).attr("id"), true);'));
        }

        protected static function resolveLinkSpecificCssClassNameByNewStatus($status)
        {
            assert('is_integer($status)');
            if ($status == Mission::STATUS_TAKEN)
            {
                return 'action-take';
            }
            elseif ($status == Mission::STATUS_COMPLETED)
            {
                return 'action-complete';
            }
            elseif ($status == Mission::STATUS_ACCEPTED)
            {
                return 'action-accept';
            }
            elseif ($status == Mission::STATUS_REJECTED)
            {
                return 'action-reject';
            }
        }

        protected function renderLabel()
        {
            return Zurmo::t('MissionsModule', 'Status');
        }

        public static function getDisplayName()
        {
            return Zurmo::t('MissionsModule', 'Status');
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                'status',
            );
        }
    }
?>