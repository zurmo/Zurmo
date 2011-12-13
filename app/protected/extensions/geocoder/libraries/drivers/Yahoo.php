<?php
/**
 * Copyright (c) 2009 Brian Armstrong
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Driver to handle geocoding using the google API
 *
 * An API Key is obtained from http://developer.yahoo.com/maps/rest/V1/geocode.html
 *
 * @package GeoCoder
 * @author Brian Armstrong <brian@barmstrongconsulting.com>
 * @copyright (C) 2009 Brian Armstrong
 * @link http://barmstrongconsulting.com/
 * @version 1.0
 */
class GeoCode_Driver_Yahoo extends GeoCode_Driver
{
	// Error constants
	const ERROR_BAD_RESPONSE = 10;

	// Status constants
	const STATUS_WARNING = 15;
	const STATUS_BAD_REQUEST = 400;
	const STATUS_FORBIDDEN = 403;
	const STATUS_SERVICE_UNAVAILABLE = 503;

	// The accuracy constants
	const ACCURACY_UNKNOWN = 0;
	const ACCURACY_ADDRESS = 1;
	const ACCURACY_STREET = 2;
	const ACCURACY_ZIP_4 = 3;
	const ACCURACY_ZIP_2 = 4;
	const ACCURACY_ZIP = 5;
	const ACCURACY_CITY = 6;
	const ACCURACY_STATE = 7;
	const ACCURACY_COUNTRY = 8;

	/**
	 * The strings associated with each accuracy
	 * @var array
	 */
	protected $accuracy_strings = array(
		self::ACCURACY_UNKNOWN => 'unknown',
		self::ACCURACY_ADDRESS => 'address',
		self::ACCURACY_STREET => 'street',
		self::ACCURACY_ZIP_4 => 'zip+4',
		self::ACCURACY_ZIP_2 => 'zip+2',
		self::ACCURACY_ZIP => 'zip',
		self::ACCURACY_CITY => 'city',
		self::ACCURACY_STATE => 'state',
		self::ACCURACY_COUNTRY => 'country',
	);

	/**
	 * The URL for the API calls
	 * @var stromg
	 */
	protected $api_url = 'http://local.yahooapis.com/MapsService/V1/geocode?appid=';

	/**
	 * The variable to hold the last query string that we used
	 * @var string
	 */
	protected $query_str = null;

	/**
	 * The variable to hold the raw_response for the last query
	 * @var string
	 */
	protected $raw_response = null;

	/**
	 * The string to hold any status messages sent by the API
	 * @var string
	 */
	protected $status_message = null;

	/**
	 * Initialize the driver
	 */
	public function init()
	{
		// Nothing to see here
	}

	/**
	 * Send a query to the geocode API
	 *
	 * @param mixed $query
	 * @return GeoCode_Result
	 */
	public function query($query)
	{
		// Reset the status message to be empty, so that it won't
		//  cascade between calls
		$this->status_message = null;

		// Generate the URL for our API query
		$url = $this->createUrl($query);

		// Run the query
		$this->raw_response = file_get_contents($url);

		// Unserialize the result
		$response = unserialize($this->raw_response);

		// If we don't have a result set or our result was invalid
		if ($response === false || !isset($response['ResultSet']))
		{
			// Construct and throw the exception
			$status = self::ERROR_BAD_RESPONSE;
			$errMsg = self::getErrorMessage($status);
			throw new GeoCode_Exception($errMsg, $status, $this);
		}

		// Get the first result
		$result = current($response['ResultSet']);

		// If we have a warning
		if (isset($result['warning']))
		{
			// Set the status message for use with the warning constant
			$this->status_message = $result['warning'];
		}

		// Set the clean query to the address returned
		// NOTE: Don't worry if they are empty entries, they will be removed by the trim()
		$clean_query = $result['Address'] .', '. $result['City'] .', ';

		// Special handling for state/zip combox to allow them to
		//  have a space between them and a comma after
		if (!empty($result['State']))
		{
			$clean_query .= $result['State'];
			if (!empty($result['Zip']))
			{
				$clean_query .= ' '.$result['Zip'];
			}
			$clean_query .= ', ';
		}
		elseif (!empty($result['Zip']))
		{
			$clean_query .= $result['Zip'].', ';
		}

		// Add contry
		$clean_query .= $result['Country'];

		// Clean anything extra
		$clean_query = trim($clean_query, ' ,');

		// Format the data
		$data = array(
			'query' => $query,
			'clean_query' => $clean_query,
			'accuracy' => $this->getAccuracyConst($result['precision']),
			'latitude' => $result['Latitude'],
			'longitude' => $result['Longitude'],
			'street' => $result['Address'],
			'state' => $result['State'],
			'city' => $result['City'],
			'zip' => $result['Zip'],
			'country' => $result['Country'],
		);

		// Create and return the result
		return new GeoCode_Result($this, $data);
	}

	/**
	 * Create the URL to be used for our geocode query
	 *
	 * @param mixed $query
	 * @return string
	 */
	protected function createUrl($query)
	{
		// If this is an array, process it
		if (is_array($query))
		{
			$str = '';
			foreach ($query as $key => $value)
			{
				// Ignore numeric values
				if (is_numeric($key)) continue;

				// Clean the value
				$value = preg_replace('/\s{2,}/', ' ', $value);

				// Don't allow multiple spaces in query string
				$str .= $key .'='. urlencode($value) .'&';
			}

			// Set the query string
			$this->query_str = trim($str, '&');
		}
		else
		{
			// Don't allow multiple spaces in query string
			$this->query_str = 'location='.urlencode( preg_replace('/\s{2,}/', ' ', $query) );
		}

		// Create and return the url
		return $this->api_url . $this->api_key . '&output=php&' . $this->query_str;
	}

	/**
	 * Get the driver name
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return 'Yahoo';
	}

	/**
	 * Get the last query that we ran
	 *
	 * @return string
	 */
	public function getLastQuery()
	{
		return $this->query_str;
	}

	/**
	 * Get the raw response as returned by the API call
	 *
	 * @return string
	 */
	public function getRawResponse()
	{
		return $this->raw_response;
	}

	/**
	 * This method is used to translate a given status constant
	 * into a human-readable and meaningful string
	 *
	 * @param integer $const
	 * @return string
	 */
	public function getStatusString($const)
	{
		$str = '';

		switch ($const)
		{
			case self::STATUS_WARNING:
				$str = ($this->status_message === null) ? 'Warning' : $this->status_message;
				break;

			case self::STATUS_BAD_REQUEST:
				$str = 'Bad Request';
				break;

			case self::STATUS_FORBIDDEN:
				$str = 'Forbidden';
				break;

			case self::STATUS_SERVICE_UNAVAILABLE:
				$str = 'Service Unavailable';
				break;

			default:
				$str = 'Unknown';
				break;
		}

		return $str;
	}

	/**
	 * This method is used to translate a given accuracy constant
	 * into a human-readable and meaningful string
	 *
	 * @param integer $const
	 * @return string
	 */
	public function getAccuracyString($const)
	{
		return (isset($this->accuracy_strings[$const]))
				? ucfirst($this->accuracy_strings[$const])
				: 'Unknown';
	}

	/**
	 * Get the constant value associated with each constant
	 *
	 * @param string $str
	 * @return integer
	 */
	public function getAccuracyConst($str)
	{
		$str = strtolower($str);
		$lookup = array_flip($this->accuracy_strings);
		return (isset($lookup[$str])) ? $lookup[$str] : 0;
	}

	/**
	 * This method is used to translate a given error or status constant
	 * into a human-readable and meaningful error string that can be
	 * displayed to the end user.
	 *
	 * @param integer $const
	 * @return string
	 */
	public function getErrorMessage($const)
	{
		$msg ='';

		switch ($const)
		{
			case self::ERROR_BAD_RESPONSE:
				$msg = 'Bad Response from Server';
				break;

			case self::STATUS_WARNING:
				$msg = ($this->status_message === null) ? 'Geocode Warning' : $this->status_message;
				break;

			case self::STATUS_BAD_REQUEST:
				$msg = 'Bad GeoCode Request';
				break;

			case self::STATUS_FORBIDDEN:
				$msg = 'Access Forbidden';
				break;

			case self::STATUS_SERVICE_UNAVAILABLE:
				$msg = 'GeoCode Service Unavailable';
				break;

			default:
				$msg = 'Unknown Error Code: '.$const;
				break;
		}

		return $msg;
	}
}
