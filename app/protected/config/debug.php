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

    // Keep this on ALL THE TIME WHEN DEVELOPING. Turn it off in production.
    // Check it in as true!
    $debugOn = true;

    // Turn this on to see additional performance information. Turn it off in production.
    // Check it in as true!
    $performanceOn = true;

    // Turn this on to see RedBean queries. Turn it off in production.
    // Check it in as false!
    $redBeanDebugOn = false;

    // Turn this off to use php to do permissions, rights, and polices.
    // Use this to comparatively test the mysql stored functions and procedures.
    // Check it in as true!
    $securityOptimized = true;

    // Turn this off to use AuditEvent to do write audit entries when
    // the database is frozen. When it is not frozen it will always be used.
    // Use this to comparatively test.
    // Check it in as true!
    $auditingOptimized = true;

    // Turn this off to test without php level caching.
    // Php level caching is required so that only one instance of
    // any model is in memory at once. Turning it off is only useful
    // in limited debugging scenarios.
    // Check it in as true!
    $phpLevelCaching = true;

    // Turn this off to test without memcache level caching.
    // Memcache level caching works in conjunction with php level
    // caching. When a model is pulled from memcache its related
    // models will subsequently be pull from the php level cache
    // if they already exist in memory, or will be pulled from
    // memcache. A model's related models are not serialized
    // along with it.
    // Check it in as true!
    $memcacheLevelCaching = true;

    // Turn this off to test without db level caching of permissions.
    // Check it in as true!
    $dbLevelCaching = true;

    if ($debugOn)
    {
        error_reporting(E_ALL | E_STRICT);
    }

    define('YII_DEBUG',          $debugOn);
    define('YII_TRACE_LEVEL',    $debugOn ? 3 : 0);
    define('SHOW_PERFORMANCE',   $performanceOn);
    define('REDBEAN_DEBUG',      $redBeanDebugOn);
    define('SECURITY_OPTIMIZED', $securityOptimized);
    define('AUDITING_OPTIMIZED', $auditingOptimized);
    define('PHP_CACHING_ON',     $phpLevelCaching);
    define('MEMCACHE_ON',        $memcacheLevelCaching);
    define('DB_CACHING_ON',      $dbLevelCaching);

    assert_options(ASSERT_ACTIVE,   $debugOn); // Don't even think about disabling asserts!
    assert_options(ASSERT_WARNING,  $debugOn);
    assert_options(ASSERT_BAIL,     false);
    if (php_sapi_name() != 'cli')
    {
        assert_options(ASSERT_CALLBACK, 'assertFailureInBrowser');
    }
    else
    {
        assert_options(ASSERT_CALLBACK, 'assertFailureInCli');
    }

    function assertFailureInBrowser($file, $line, $message)
    {
        echo '<span style="background-color: red; color: yellow; font-weight:bold;">';
        echo "ASSERTION FAILED in $file on line $line";
        if (is_string($message) && !empty($message))
        {
            echo ": assert('$message'); ";
        }
        echo '</span><br />';
    }

    function assertFailureInCli($file, $line, $message)
    {
        throw new FailedAssertionException($file, $line, $message);
    }
?>
