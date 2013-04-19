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
                if (SHOW_QUERY_DATA)
                {
                    Yii::app()->performance->setRedBeanQueryLogger(ZurmoRedBeanPluginQueryLogger::
                                                                   getInstanceAndAttach(R::$adapter ));
                }

                if (defined('REDBEAN_DEBUG_TO_FILE') && REDBEAN_DEBUG_TO_FILE)
                {
                    $queryLoggerComponent = Yii::createComponent(array(
                        'class' => 'application.core.models.ZurmoRedBeanQueryFileLogger',
                    ));
                    $queryLoggerComponent->init();
                    Yii::app()->setComponent('queryFileLogger', $queryLoggerComponent);
                    R::debug(true, Yii::app()->queryFileLogger);
                }
                else
                {
                    R::debug(defined('REDBEAN_DEBUG') && REDBEAN_DEBUG);
                }

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
            R::$toolboxes          = array();
            R::$toolbox            = null;
            R::$redbean            = null;
            R::$writer             = null;
            R::$adapter            = null;
            R::$associationManager = null;
            R::$extAssocManager    = null;
            R::$exporter           = null;
            R::$tagManager         = null;
            R::$currentDB          = '';
            R::$f                  = null;
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

        /**
         * Get database type from connection string(dsn)
         * @param string $dsn
         * @throws NotSupportedException
         */
        public static function getDatabaseTypeFromDsnString($dsn)
        {
            $databaseType = substr($dsn, 0, strpos($dsn, ':'));
            if ($databaseType)
            {
                return $databaseType;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         *
         * @param string $dsn
         * @throws NotSupportedException
         * @return multitype:array
         */
        public static function getDatabaseInfoFromDsnString($dsn)
        {
            assert(preg_match("/^([^;]+):host=([^;]+);(?:port=([^;]+);)?dbname=([^;]+)/", $dsn, $matches) == 1); // Not Coding Standard
            if (count($matches) == 5)
            {
                if (empty($matches['3']))
                {
                    $databaseType = $matches['1'];
                    $databasePort = DatabaseCompatibilityUtil::getDatabaseDefaultPort($databaseType);
                }
                else
                {
                    $databasePort = $matches['3'];
                }
                $databaseConnectionInfo = array(
                    'databaseType' => $matches['1'],
                    'databaseHost' => $matches['2'],
                    'databasePort' => intval($databasePort),
                    'databaseName' => $matches['4']
                );
                return $databaseConnectionInfo;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Get database name from connection string
         * Function allow two connection formats because backward compatibility
         * 1. "host=localhost;port=3306;dbname=zurmo"
         * 2. "host=localhost;dbname=zurmo"
         * @param string $dsn
         * @throws NotSupportedException
         * @return string
         */
        public static function getDatabaseNameFromDsnString($dsn)
        {
            assert(preg_match("/host=([^;]+);(?:port=([^;]+);)?dbname=([^;]+)/", $dsn, $matches) == 1); // Not Coding Standard
            if (count($matches) == 4)
            {
                return $matches[3];
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>
