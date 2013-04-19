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

    $basePath = Yii::app()->getBasePath();
    require_once("$basePath/../../redbean/rb.php");

    /**
     * Base class to allow observers to raise events on models.
     */
    class ObservableComponent extends CComponent
    {
        private static $_events = array();

        /**
         * Attach exists events while model creation
         */
        public function init()
        {
            $this->attachEvents($this->events());
        }

        /**
         * Attach events
         *
         * @param array $events
         */
        public function attachEvents($events)
        {
            foreach ($events as $event)
            {
                if ($event['component'] == get_class($this))
                {
                    parent::attachEventHandler($event['name'], $event['handler']);
                }
            }
        }

        /**
         * Get exists events
         *
         * @return array
         */
        public function events()
        {
          //  echo "<pre>";
           // print_r(self::$_events);
          //  echo "</pre>";
            return self::$_events;
        }

        /**
         * Attach event handler
         *
         * @param string $name Event name
         * @param mixed $handler Event handler
         */
        public function attachEventHandler($name, $handler)
        {
            self::$_events[] = array(
                'component' => get_class($this),
                'name' => $name,
                'handler' => $handler
            );
            parent::attachEventHandler($name, $handler);
        }

        /**
         * Dettach event hander
         *
         * @param string $name Event name
         * @param mixed $handler Event handler
         * @return bool
         */
        public function detachEventHandler($name, $handler)
        {
            foreach (self::$_events as $index => $event)
            {
                if ($event['name'] == $name && $event['handler'] == $handler)
                {
                    unset(self::$_events[$index]);
                }
            }
            return parent::detachEventHandler($name, $handler);
        }
    }
?>