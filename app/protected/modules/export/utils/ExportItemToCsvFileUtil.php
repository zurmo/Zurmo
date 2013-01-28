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
                $output = self::arraytoCsv($headerRow, true);

                foreach ($data as $row)
                {
                    $output .= self::arraytoCsv($row);
                }
            }
            if ($download)
            {
                Yii::app()->request->sendFile($exportFilename, $output, self::$mimeType, false);
                exit;
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
         */
        protected static function arrayToCsv($row, $isHeaderRow = false, $delimiter = ',', $enclosure = '"') // Not Coding Standard
        {
            $fp = fopen('php://temp', 'r+'); // Not Coding Standard

            if (fputcsv($fp, $row, $delimiter, $enclosure) === false)
            {
                return false;
            }
            rewind($fp);
            $csv = stream_get_contents($fp);

            if ($isHeaderRow)
            {
                // ModelToExportAdapter->getData() does not add quotes to header rows so we have to do it here.
                // Using fputcsv instead of implode because it does couple of more useful things like escaping
                $csv = str_replace( $enclosure, '', $csv );
            }

            fclose($fp);
            return $csv;
        }
    }
?>
