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
     * Helper class to render a list of audit events called from a model controller.
     */
    class AuditEventsListControllerUtil
    {
        /**
         * @return rendered content from view as string.
         */
        public static function renderList(CController $controller, $dataProvider, $pageTitle = null)
        {
            assert('$dataProvider instanceof RedBeanModelDataProvider');
            assert('$pageTitle == null || is_string($pageTitle)');
            $auditEventsListView = new AuditEventsModalListView(
                $controller->getId(),
                $controller->getModule()->getId(),
                'AuditEvent',
                $dataProvider,
                'modal'
            );
            $view = new ModalView($controller,
                $auditEventsListView,
                'modalContainer',
                $pageTitle);
            return $view->render();
        }

        /**
         * Creates the appropriate filtering of audit events by the specified model.
         * @param object $model AuditEvent
         * @return array $searchAttributeData
         */
        public static function makeSearchAttributeDataByAuditedModel($model)
        {
            assert('$model instanceof Item');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'modelClassName',
                    'operatorType'         => 'equals',
                    'value'                => get_class($model),
                ),
                2 => array(
                    'attributeName'        => 'modelId',
                    'operatorType'         => 'equals',
                    'value'                => $model->id,
                ),
                3 => array(
                    'attributeName'        => 'eventName',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                )
            );
            $searchAttributeData['structure'] = '1 and 2 and 3';
            return $searchAttributeData;
        }

        /**
         * Given an array of searchAttributeData, a RedBeanModelDataProvider is created and returned.
         * @param array $searchAttributeData
         * @return object $RedBeanModelDataProvider
         */
        public static function makeDataProviderBySearchAttributeData($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            return new RedBeanModelDataProvider( 'AuditEvent', 'dateTime', true,
                                                                $searchAttributeData, array(
                                                                    'pagination' => array(
                                                                        'pageSize' => $pageSize,
                                                                    )
                                                                ));
        }
    }
?>