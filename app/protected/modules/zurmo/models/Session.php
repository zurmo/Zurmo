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

    class Session extends RedBeanModel
    {
        /**
        * Gets a session by session_id.
        * @param string $sessionId.
        * @return A model of ZurmoSession or null.
        */
        public static function getBySessionIdIpAddressAndUserAgent($sessionId,
                                                                   $ipAddress = null,
                                                                   $userAgent = null)
        {
            echo RedBeanDatabase::isSetup();
            assert('is_string($sessionId)');
            $now = time();
            // Create query
            $query = "sessionid = '$sessionId'";

            if (isset($ipAddress))
            {
                $query .= " and ipaddress = $ipAddress";
            }
            if (isset($userAgent))
            {
                $query .= " and useragent = '$userAgent'";
            }
            $query .= " and expire >= '$now'";

            $bean = R::findOne('session', $query);

            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                return false;
            }
            return self::makeModel($bean);
        }

        public static function deleteExpiredSessions()
        {
            $now = time();
            $expiredSessions = self::getSubset(null, null, null, "expire < '$now'");
            if(count($expiredSessions))
            {
                foreach ($expiredSessions as $session)
                {
                    $session->delete();
                }
            }
            return true;
        }

        public static function getModuleClassName()
        {
            return 'ZurmoModule';
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'sessionId',
                    'ipAddress',
                    'userAgent',
                    'expire',
                    'data'
                ),
                'rules' => array(
                    array('sessionId', 'required'),
                    array('sessionId', 'unique'),
                    array('sessionId', 'type',   'type' => 'string'),
                    array('sessionId', 'length', 'max' => 32),
                    array('ipAddress', 'required'),
                    array('ipAddress', 'type',   'type' => 'integer'),
                    array('ipAddress', 'length', 'max' => 10),
                    array('userAgent', 'type',   'type' => 'string'),
                    array('userAgent', 'length', 'max' => 32),
                    array('expire',     'required'),
                    array('expire',     'type', 'type' => 'int'),
                    array('data',       'type', 'type' => 'string'),
                ),
                'defaultSortAttribute' => 'id'
            );
            return $metadata;
        }
    }
?>
