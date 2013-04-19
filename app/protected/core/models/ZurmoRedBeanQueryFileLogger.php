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
     * Log sql queries into file.
     * ZurmoRedBeanPluginQueryLogger doesn't contain all data we need to log, so we had to extend this class.
     * Code is optimized, so data are written only once to file, in EndRequestBehavior
     */
    class ZurmoRedBeanQueryFileLogger extends CApplicationComponent implements RedBean_ILogger
    {
        /**
         * @var integer maximum log file size
         */
        protected $maxFileSize = 1024; // in KB

        /**
         * @var integer number of log files used for rotation
         */
        protected $maxLogFiles = 5;

        /**
         * @var string directory storing log files
         */
        protected $logPath;

        /**
         * @var string log file name
         */
        protected $logFile = 'sqlQueries.log';

        /**
         * @var string logs - contain sql query details
         */
        protected $logs = '';

        /**
         * Initializes the route.
         * This method is invoked after the route is created by the route manager.
         */
        public function init()
        {
            if ($this->getLogPath() === null)
            {
                $this->setLogPath(Yii::app()->getRuntimePath());
            }
        }

        /**
         * @return string directory storing log files. Defaults to application runtime path.
         */
        public function getLogPath()
        {
            return $this->logPath;
        }

        /**
         * @param string $value directory for storing log files.
         * @throws CException if the path is invalid
         */
        public function setLogPath($value)
        {
            $this->logPath = realpath($value);
            if ($this->logPath === false || !is_dir($this->logPath) || !is_writable($this->logPath))
            {
                throw new CException(Zurmo::t('Default', 'CFileLogRoute.logPath "{path}" does not point to a valid directory. Make sure the directory exists and is writable by the Web server process.',
                    array('{path}' => $value)));
            }
        }

        /**
         * @return string log file name. Defaults to 'application.log'.
         */
        public function getLogFile()
        {
            return $this->logFile;
        }

        /**
         * @param string $value log file name
         */
        public function setLogFile($value)
        {
            $this->logFile = $value;
        }

        /**
         * @return integer maximum log file size in kilo-bytes (KB). Defaults to 1024 (1MB).
         */
        public function getMaxFileSize()
        {
            return $this->maxFileSize;
        }

        /**
         * @param integer $value maximum log file size in kilo-bytes (KB).
         */
        public function setMaxFileSize($value)
        {
            if (($this->maxFileSize = (int)$value) < 1)
            {
                $this->maxFileSize = 1;
            }
        }

        /**
         * @return integer number of files used for rotation. Defaults to 5.
         */
        public function getMaxLogFiles()
        {
            return $this->maxLogFiles;
        }

        /**
         * @param integer $value number of files used for rotation.
         */
        public function setMaxLogFiles($value)
        {
            if (($this->maxLogFiles = (int)$value) < 1)
            {
                $this->maxLogFiles = 1;
            }
        }

        /**
         * @return string
         */
        public function getLogs()
        {
            return $this->logs;
        }

        /**
         * @param string $logs
         */
        public function setLogs($logs = '')
        {
            $this->logs = $logs;
        }

        /**
         * Add log at the end of current logs
         * @param $data
         */
        protected function addLog($data)
        {
            $logs  = $this->getLogs();
            $logs .= $data . PHP_EOL;
            $this->setLogs($logs);
        }

        /**
         * Save log into memory.
         * On EndRequest, this logs will be saved into file.
         */
        public function log()
        {
            if (func_num_args() > 0)
            {
                foreach (func_get_args() as $argument)
                {
                    if (is_array($argument))
                    {
                        $data = print_r($argument, true);
                    }
                    else
                    {
                        $data = $argument;
                    }
                    $this->addLog($data);
                }
            }
        }

        /**
         * Save sql query logs into file
         */
        public function processLogs()
        {
            $logFile = $this->getLogPath() . DIRECTORY_SEPARATOR . $this->getLogFile();
            if (@filesize($logFile) > $this->getMaxFileSize()*1024)
            {
                $this->rotateFiles();
            }
            $fp = @fopen($logFile, 'a');
            @flock($fp, LOCK_EX);
            @fwrite($fp, $this->getRequestInfoDetails());
            @fwrite($fp, $this->logs);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }

        /**
         * Create header info for query logs
         * @return string
         */
        protected function getRequestInfoDetails()
        {
            $requestInfoString = '';
            if (isset(Yii::app()->request))
            {
                $pathInfo = Yii::app()->request->getPathInfo();
                $queryInfo = Yii::app()->request->getQueryString();

                $requestInfoString .= '--------------------------------' .         PHP_EOL;
                $requestInfoString .= 'Request Date: ' . date('F j, Y, g:i:s a') . PHP_EOL;
                $requestInfoString .= 'Request Url: '  . $pathInfo .               PHP_EOL;
                $requestInfoString .= 'Query String: ' . $queryInfo .              PHP_EOL;
                $requestInfoString .= '-------------------------------' .          PHP_EOL;
            }
            return $requestInfoString;
        }

        /**
         * Rotates log files.
         */
        protected function rotateFiles()
        {
            $file = $this->getLogPath() . DIRECTORY_SEPARATOR . $this->getLogFile();
            $max = $this->getMaxLogFiles();
            for ($i = $max; $i>0; --$i)
            {
                $rotateFile = $file . '.' . $i;
                if (is_file($rotateFile))
                {
                    // suppress errors because it's possible multiple processes enter into this section
                    if ($i === $max)
                    {
                        @unlink($rotateFile);
                    }
                    else
                    {
                        @rename($rotateFile, $file . '.' . ($i + 1));
                    }
                }
            }
            if (is_file($file))
            {
                @rename($file, $file . '.1'); // suppress errors because it's possible multiple processes enter into this section
            }
        }
    }
?>