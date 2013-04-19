<?php
/**
 * CSV parser
 * Currently the string matching doesn't work
 * if the output encoding is not ASCII or UTF-8
 *
 * original work: http://minghong.blogspot.com/2006/07/csv-parser-for-php.html?m=1
 */

class CsvParser
{
  /**
   * Parse CSV from a File
   *
   * @param string  $filename
   * @param string  $delimiter      (optional)
   * @param string  $enclosure      (optional)
   * @param string  $inputEncoding  (optional)
   * @param string  $outputEncoding (optional)
   * @param string  $hasHeader      (optional)
   * @param boolean $hasBOM         (optional)
   * @return false or array
   */
  public static function parseFromFile( $filename, $delimiter = ",", $enclosure = '"', $inputEncoding = "ISO-8859-1", $outputEncoding = "ISO-8859-1", $hasHeader = true, $hasBOM = false )
  {
    if ( !is_readable($filename) )
    {
      return false;
    }
    //TODO: check if the latest fgetcsv honors enclosed linebreaks, if it does probably better to use that for performance reasons.
    return self::parseFromString( file_get_contents($filename), $delimiter, $enclosure, $inputEncoding, $outputEncoding, $hasBOM );
  }


  /**
   * Parse CSV from string
   *
   * @param string  $content
   * @param string  $delimiter      (optional)
   * @param string  $enclosure      (optional)
   * @param string  $inputEncoding  (optional)
   * @param string  $outputEncoding (optional)
   * @param string  $hasHeader      (optional)
   * @param boolean $hasBOM         (optional)
   * @return false or array
   */
  public static function parseFromString( $content, $delimiter = ",", $enclosure = '"', $inputEncoding = "ISO-8859-1", $outputEncoding = "ISO-8859-1", $hasHeader = true, $hasBOM = false )
  {
    $content = iconv( $inputEncoding, $outputEncoding, $content );

    // Fixing improper line endings
    $content = str_replace( "\r\n", "\n", $content );
    $content = str_replace( "\r", "\n", $content );

    if ( $hasBOM )                                // Remove the BOM (first 3 bytes)
    {
      $content = substr( $content, 3 );
    }

    if ( $content[strlen( $content )-1] != "\n" )   // Make sure it always end with a newline
    {
      $content .= "\n";
    }


    // Parse column names from header.
    if ( $hasHeader )
    {
      $headerRow = strtok( $content, "\n" );
      $columnNames = explode( ',', $headerRow );
      // we have processed the header, now delete that from content
      $content = preg_replace( "/^(.*\n){1}/", "", $content );
    }


    // Parse the content character by character
    $row = array( "" );
    $data = array();
    $idx = 0;
    $quoted = false;

    for ( $i = 0; $i < strlen( $content ); $i++ )
    {
      $ch = $content[$i];
      if ( $ch == $enclosure )
      {
        $quoted = !$quoted;
      }

      // End of line
      if ( $ch == "\n" && !$quoted )
      {
        // Remove enclosure delimiters for every field
        // why? because also read the quotes from csv as part of field so it would be ""Quoted ""Field Data""
        for ( $k = 0; $k < count( $row ); $k++ )
        {
          if ( $row[$k] != "" && $row[$k][0] == $enclosure )
          {
            $row[$k] = substr( $row[$k], 1, strlen( $row[$k] ) - 2 ); //start at 1 so we skip the fist " and end at length-2 to remove final quote
          }
          $row[$k] = str_replace( str_repeat( $enclosure, 2 ), $enclosure, $row[$k] ); //replace duplicated encloser tags inside the actual string

        }

        // Add column names as indexes instead of integer ones if we have header row present.
        if ( $hasHeader )
        {
          $row = array_combine( $columnNames, $row );
        }

        // Append row into table
        $data[] = $row;
        $row = array( "" );
        $idx = 0;
      }


      // End of field
      elseif ( $ch == $delimiter && !$quoted )
      {
          $row[++$idx] = ""; //add an empty string for next field to start
      }

      // Inside the field
      else
      {
        $row[$idx] .= $ch;
      }
    }

    return $data;
  }


}
