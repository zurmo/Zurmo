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
     * Portlets is a model utilized by some view types to display
     * information.  Portlets have display states on a per user basis
     * including hide/collapse and portlet position.  The home page dashboard
     * and subviews are comprised of portlets.  A portlet contains 2 views, a display view
     * and a configuration view.
     */
    class Portlet extends RedBeanModel
    {
        protected $view;

        /**
         * TODO
         */
        public $params;

        public static function getByLayoutIdAndUserSortedByColumnIdAndPosition($layoutId, $userId, $params)
        {
            $portletCollection = array();
            assert('is_integer($userId) && $userId >= 1');
            $quote = DatabaseCompatibilityUtil::getQuote();
            $sql = "select id, {$quote}column$quote, position "           .
                   'from portlet '                                        .
                   "where layoutid = '$layoutId' and _user_id = $userId " .
                   "order by {$quote}column$quote, position;";
            $rows = R::getAll($sql);
            if (!empty($rows))
            {
                foreach ($rows as $row)
                {
                    $portlet = Portlet::getById(intval($row['id']));
                    $portlet->params = $params;
                    $portletCollection[intval($row['column'])][intval($row['position'])] = $portlet;
                }
            }
            return $portletCollection;
        }

        public static function getByLayoutIdAndUserSortedById($layoutId, $userId)
        {
            $portletCollection = array();
            assert('is_integer($userId) && $userId >= 1');
            $quote = DatabaseCompatibilityUtil::getQuote();
            $sql = "select id, {$quote}column$quote, position "          .
                   'from portlet '                                       .
                   "where layoutid = '$layoutId' and _user_id = $userId " .
                   'order by id;';
            foreach (R::getAll($sql) as $row)
            {
                $portlet = Portlet::getById(intval($row['id']));
                $portletCollection[$row['id']] = $portlet;
            }
            return $portletCollection;
        }

        public static function makePortletsUsingMetadataSortedByColumnIdAndPosition($layoutId, $metadata, $user, $params)
        {
            $portletCollection = array();
            foreach ($metadata['global']['columns'] as $column => $columns)
            {
                foreach ($columns['rows'] as $position => $portletMetadata)
                {
                    $portlet = new Portlet();
                    $portlet->params    = $params;
                    $portlet->column    = $column + 1;
                    $portlet->position  = $position + 1;
                    $portlet->layoutId  = $layoutId;
                    $portlet->collapsed = false;
                    $portlet->viewType  = $portletMetadata['type'];
                    $portlet->user      = $user;
                    $portletCollection[$column + 1][$position + 1] = $portlet;
                }
            }
            return $portletCollection;
        }

        public static function savePortlets($portletCollection)
        {
            foreach ($portletCollection as $column => $columns)
            {
                foreach ($columns as $position => $portlet)
                {
                    $saved = $portlet->save();
                    assert('$saved'); // TODO - deal with this properly.
                }
            }
        }

        public static function shiftPositionsBasedOnColumnReduction($portletCollection, $newColumnCount)
        {
            $currentColumnCount = count($portletCollection);
            if (!empty($portletCollection[1]))
            {
                $maxPositionInColumn1 = count($portletCollection[1]);
            }
            $shiftToPosition = $maxPositionInColumn1 + 1;
            for ($i = ($newColumnCount + 1); $i <= $currentColumnCount; $i++)
            {
                foreach ($portletCollection[$i] as $position => $portlet)
                {
                    $portlet->column = 1;
                    $portlet->position = $shiftToPosition;
                    $portlet->save();
                    $shiftToPosition++;
                }
            }
        }

        /**
         * Make a portlet with default values.
         */
        public static function makePortletUsingViewType($viewType, $uniqueLayoutId, $user)
        {
            $portlet = new Portlet();
            $portlet->column    = 1;
            $portlet->position  = 1;
            $portlet->layoutId = $uniqueLayoutId;
            $portlet->collapsed = false;
            $portlet->viewType = $viewType;
            $portlet->user = $user;
            $portlet->save();
        }

        public static function getDefaultMetadata()
        {
            $metadata[__CLASS__] = array(
                'members' => array(
                    'column',
                    'position',
                    'layoutId',
                    'viewType',
                    'serializedViewData',
                    'collapsed',
                ),
                'relations' => array(
                    'user' => array(RedBeanModel::HAS_ONE, 'User'),
                ),
                'rules' => array(
                    array('column',             'required'),
                    array('column',             'type',   'type' => 'integer'),
                    array('position',           'required'),
                    array('position',           'type',   'type' => 'integer'),
                    array('layoutId',           'required'),
                    array('layoutId',           'type',   'type' => 'string'),
                    array('layoutId',           'length', 'max'  => 100),
                    array('viewType',           'required'),
                    array('viewType',           'type',   'type' => 'string'),
                    array('serializedViewData', 'type',   'type' => 'string'),
                    array('collapsed',          'boolean'),
                )
            );
            return $metadata;
        }

        public function getView()
        {
            $className = $this->viewType . 'View';
            $this->params['portletId'] = $this->id;
            $this->view = new $className(unserialize($this->serializedViewData), $this->params, $this->getUniquePortletPageId());
            return $this->view;
        }

        /**
         * Gets a unique identifier to allow for
         * multiple portlets on the same page.
         */
        public function getUniquePortletPageId()
        {
            return $this->layoutId . "_" . $this->id;
        }

        public function getCssClasses()
        {
            return $this->getView()->getCssClasses();
        }

        public function getTitle()
        {
            return $this->getView()->getTitle();
        }

        public function renderContent()
        {
            //return '<div>test</div>';
            return $this->getView()->render();
        }

        public function isEditable()
        {
            $className = get_class($this->getView());
            return $className::canUserConfigure();
        }

        public function beforeDelete()
        {
            $className = $this->viewType . 'View';
            if (@class_exists($className))
            {
                $class = new ReflectionClass($className);
                if ($class->implementsInterface('UserPersistentSettingsCleanupForPortletInterface'))
                {
                    $className::processBeforeDelete($this->id);
                }
            }
            return parent::beforeDelete();
        }
    }
?>
