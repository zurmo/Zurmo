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
     * Helper functionality for use in debugging.
     */
    if (defined('YII_DEBUG'))
    {
        class DebugUtil
        {
            /**
             * Shows a 'Hit enter to continue.' message.
             * This is for temporary use when debugging.
             * DO NOT CHECK IN A CALL TO THIS FUNCTION.
             */
            public static function hitEnterToContinue()
            {
                $backtrace = debug_backtrace();
                $file = $backtrace[0]['file'];
                $file = substr($file, strrpos($file, '/') + 1);
                $line = $backtrace[0]['line'];
                echo "$file on line $line, hit enter to continue.";
                fgets(fopen('/dev/stdin', 'r'), 1024);
            }

            /**
             * Starts trace to trace.xt in the current directory.
             * This is for temporary use when debugging.
             * DO NOT CHECK IN A CALL TO THIS FUNCTION.
             */
            public static function startTrace()
            {
                xdebug_start_trace('./trace');
            }

            /**
             * Stops trace.
             * This is for temporary use when debugging.
             * DO NOT CHECK IN A CALL TO THIS FUNCTION.
             */
            public static function stopTrace()
            {
                xdebug_stop_trace();
            }

            /**
             * This is for use when you have the UBER-LAME...
             * "PHP Fatal error:  Maximum function nesting level of '100' reached,
             * aborting!  in Lame.php(1273)
             * ...which just craps out leaving you without a stack trace.
             * Add at the line in the file where it finally spazzes out add
             * something like...
             * DebugUtil::dumpStack('/tmp/lame');
             * It will write the stack into that file every time it passes that
             * point and when it eventually blows up (and probably long before) you
             * will be able to see where the problem really is.
             */
            public static function dumpStack($fileName)
            {
                $stack = "";
                foreach (debug_backtrace() as $trace)
                {
                    if (isset($trace['file']) &&
                        isset($trace['line']) &&
                        isset($trace['class']) &&
                        isset($trace['function']))
                    {
                        $stack .= $trace['file']     . '#' .
                                  $trace['line']     . ':' .
                                  $trace['class']    . '.' .
                                  $trace['function'] . "\n";
                    }
                }
                file_put_contents($fileName, $stack);
            }
        }
    }
?>
