<?php
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
            $userId = Yii::app()->user->userModel->id;
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
                )
            );
            $searchAttributeData['structure'] = '1 and 2';
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