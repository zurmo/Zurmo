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

    $basePath = Yii::app()->getBasePath();
    require_once("$basePath/../../redbean/rb.php");

    /**
     * Abstraction over the top of an application database accessed via
     * <a href="http://www.redbeanphp.com/">RedBean</a>.
     */
    class RedBeanDatabase
    {
        private static $isSetup      = false;
        private static $databaseType = null;

        /**
         * Sets up the database connection.
         * @param $dsn The datasource name. See: <a href="http://nl3.php.net/manual/en/pdo.construct.php">http://nl3.php.net/manual/en/pdo.construct.php</a>
         * @code
         *   "sqlite:database.txt"
         *   "mysql:host=localhost;dbname=oodb"
         *   "pgsql:host=localhost;dbname=oodb"
         * @endcode
         * @param $username The database user's login username.
         * @param $password The database user's login password.
         */
        public static function setup($dsn, $username, $password)
        {
            assert('is_string($dsn) && $dsn != ""');
            assert('$username == null || is_string($username) && $username != ""');
            assert('$password == null || is_string($password)');
            assert('!self::isSetup()');
            try
            {
                R::setup($dsn, $username, $password);
                R::$redbean->addEventListener("update",       new RedBeanBeforeUpdateHintManager(R::$toolbox));
                R::$redbean->addEventListener("after_update", new RedBeanAfterUpdateHintManager (R::$toolbox));
                if(SHOW_QUERY_DATA)
                {
                    Yii::app()->performance->setRedBeanQueryLogger(ZurmoRedBeanPluginQueryLogger::
                                                                   getInstanceAndAttach(R::$adapter ));
                }
                $debug = defined('REDBEAN_DEBUG') && REDBEAN_DEBUG;
                R::debug($debug);
                self::$isSetup      = true;
                self::$databaseType = substr($dsn, 0, strpos($dsn, ':'));
            }
            catch (Exception $e)
            {
                self::close();
                throw $e;
            }
        }

        /**
         * Returns true if the setup() method has been called.
         */
        public static function isSetup()
        {
            return self::$isSetup;
        }

        public static function close()
        {
            // TODO - find out if there is a proper way.
            R::$toolbox            = null;
            R::$redbean            = null;
            R::$writer             = null;
            R::$adapter            = null;
            R::$treeManager        = null;
            R::$associationManager = null;
            R::$extAssocManager    = null;
            R::$linkManager        = null;
            self::$isSetup = false;
        }

        public static function getDatabaseType()
        {
            return self::$databaseType;
        }

        /**
         * Freezes the database. This means there is no need for
         * <a href="http://www.redbeanphp.com/">RedBean</a> to
         * create any more tables or modify table structures. This boosts the
         * performance of the application significantly.
         */
        public static function freeze()
        {
            R::freeze(true);
        }

        /**
         * Returns true if the database is frozen.
         */
        public static function isFrozen()
        {
            return R::$redbean !== null && R::$redbean->isFrozen();
        }

        /**
         * Unfreezes the database. This means that RedBean can dynamically
         * modify the database as it executes during development.
         */
        public static function unfreeze()
        {
            R::freeze(false);
        }
    }
?>
