<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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

    class ExportItemToCsvFileUtil extends ExportItemToOutputUtil
    {
        /**
         * Export data array into csv format and send generated file to web browser
         * or return csv string, depending on $download parameter.
         * @param array $data
         * @param boolean $download. Should send generated csv string to output or not.
         */
        public static function export(& $data, $exportFilename = 'exports.csv', $download = false)
        {
            $output = '';

            if (count($data) > 0)
            {
                $headerRow = array();
                foreach ($data[0] as $key => $value)
                {
                    $headerRow[] = $key;
                }
                $output = self::arraytoCsv($headerRow);

                foreach ($data as $row)
                {
                    $output .= self::arraytoCsv($row);
                }
            }

            if ($download)
            {
                Yii::app()->request->sendFile($exportFilename, $output, self::$mimeType, false);
            }
            else
            {
                return $output;
            }
        }

        /**
         * Convert array into csv string.
         * @param array $row
         * @param boolean $isHeaderRow
         * @param string $delimiter
         * @param string $enclosure
         * @param string $eol
         */
        protected static function arrayToCsv($row, $isHeaderRow = false, $delimiter = ',', $enclosure = '"', $eol = "\n")
        {
            $fp = fopen('php://temp', 'r+');

            if (fputcsv($fp, $row, $delimiter, $enclosure) === false)
            {
                return false;
            }

            rewind($fp);
            $csv = fgets($fp);

            if ($eol != PHP_EOL)
            {
                $csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
            }
            return $csv;
        }

        public static function csvToArray($csv, $delimiter = ',', $enclosure = '"', $escape = '\\', $terminator = "\n")
        {
            $result = array();
            $rows = explode($terminator, trim($csv));
            $columnNames = array_shift($rows);
            $columnNames = str_getcsv($columnNames, $delimiter, $enclosure, $escape);
            $numberOfColumns = count($columnNames);
            foreach ($rows as $row)
            {
                if (trim($row))
                {
                    $values = str_getcsv($row, $delimiter, $enclosure, $escape);
                    if (!$values)
                    {
                        $values = array_fill(0, $numberOfColumns, null);
                    }
                    $result[] = array_combine($columnNames, $values);
                }
            }
            return $result;
        }
    }
?>
