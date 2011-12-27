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
     * Class to hold a GeoCode result for return from a given API
     *
     * @package GeoCoder
     * @author Brian Armstrong <brian@barmstrongconsulting.com>
     * @copyright (C) 2009 Brian Armstrong
     * @link http://barmstrongconsulting.com/
     * @version 1.0
     */
    class GeoCode_Result
    {
        /**
         * The driver associated with this result
         * @var GeoCode_Driver
         */
        private $_driver = null;

        /**
         * Holds the types of the containers that have maps in them
         * @var array
         */
        private static $_map_containers = array();

        /**
         * The number of maps we have rendered
         * @var integer
         */
        private static $_map_count = 0;

        /**
         * The number of points we have rendered
         * @var integer
         */
        private static $_point_count = 0;

        /**
         * The attributes array to hold all of our data
         * @var array
         */
        protected $attributes = array(
            /**
             * The accuracy code
             * @var integer
             */
            'accuracy' => null,
            /**
             * The latitude in degrees
             * @var float
             */
            'latitude' => null,
            /**
             * The longitude in degrees
             * @var float
             */
            'longitude' => null,
            /**
             * The query that was sent to the API
             * @var string
             */
            'query' => null,
            /**
             * The processed query, as returned by the API
             * @var string
             */
            'clean_query' => null,
            /**
             * The state name
             * @var string
             */
            'state' => null,
            /**
             * The county name
             * @var string
             */
            'county' => null,
            /**
             * The city name
             * @var string
             */
            'city' => null,
            /**
             * The zip code
             * NOTE: This needs to be treated as a string. Zip codes outside of the
             *  United states are not guaranteed to be numeric. Example: Canada
             * @var string
             */
            'zip' => null,
            /**
             * The street address
             * @var string
             */
            'street' => null,
            /**
             * The country
             * @var string
             */
            'country' => null,
        );

        /**
         * Initialize the result object
         *
         * @param GeoCode_Driver $driver
         * @param array $data
         * @return GeoCode_Result
         */
        public function __construct(GeoCode_Driver $driver, $data = array())
        {
            $this->_driver = $driver;

            // Assign all the data
            foreach ($data as $key => $value)
            {
                // Pass off to the set method
                $this->__set($key, $value);
            }
        }

        /**
         * Render a map of this result
         *
         * NOTE: For map types simply send the constant string, any
         *  prefix will be appended automatically
         *
         * @param string $container_id - the id of the container object
         * @param array $options - the map options
         * @param string $type - the type of map to render
         */
        public function renderMap($container_id, $options = array(), $type = null)
        {
            if (!isset(self::$_map_containers[$container_id]))
            {
                // Get the type for this if it was not sent
                if ($type === null) $type = $this->_driver->getDriverName();
                // Lowercase the map type
                $type = strtolower($type);

                // Save the container id and the type
                self::$_map_containers[$container_id] = $type;

                // Render the view
                $map_script = $this->render("{$type}/map", array(
                    'query' => $this->query,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'container_id' => $container_id,
                    'options' => $options
                ), true);

                // Register the javascript
                Yii::app()->getClientScript()->registerScript("{$type}_map_js".(self::$_map_count++), $map_script, CClientScript::POS_READY);
            }

            // Render the map point
            $this->renderPoint($this->latitude, $this->longitude, $container_id, $this->clean_query);
        }

        /**
         * Render a point on the map. By default it will render the points associated
         * with the result in the last used container with the last used type. Make sure
         * that you have already made a call to render map with the given container_id
         *
         * @param float $lat
         * @param float $lon
         * @param string $container_id
         * @param string $description
         */
        public function renderPoint($lat, $lon, $container_id, $description=null)
        {
            // Validate the container
            if (!isset(self::$_map_containers[$container_id]))
                throw new GeoCode_Exception("Unknown map container '{$container_id}'");

            // Get our type
            $type = self::$_map_containers[$container_id];
            // Render the view
            $point_script = $this->render("{$type}/point", array(
                'query' => $description,
                'latitude' => ($lat === null) ? $this->latitude : $lat,
                'longitude' => ($lon === null) ? $this->longitude : $lon,
            ), true);

            // Register the javascript
            Yii::app()->clientScript->registerScript("{$type}_point_js".(self::$_point_count++), $point_script, CClientScript::POS_READY);
        }

        /**
         * Get the string associated with the accuracy constant
         *
         * @return string
         */
        public function getAccuracyString()
        {
            return $this->_driver->getAccuracyString($this->accuracy);
        }

        /**
         * Get the latitude in radians
         *
         * @return float
         */
        public function getLatitudeRadians()
        {
            return isset($this->latitude) ? deg2rad((float)$this->latitude) : null;
        }

        /**
         * Get the longitude in radians
         *
         * @return float
         */
        public function getLongitudeRadians()
        {
            return isset($this->longitude) ? deg2rad((float)$this->longitude) : null;
        }

        /**
         * Render one of this extension's views
         *
         * @param string $_view_
         * @param mixed $_data_
         * @param boolean $_return_
         */
        private function render($_view_, $_data_=null, $_return_=false)
        {
            // Variable names are special so we don't get collisions with extract
            // Get the filename to render
            $_viewFile_ = $this->getViewFile($_view_);

            // Handle the data we passed
            if (is_array($_data_))
                extract($_data_, EXTR_PREFIX_SAME, 'data');
            else
                $_data_ = $data;

            // Render the file
            if ($_return_)
            {
                ob_start();
                ob_implicit_flush(false);
                require(str_replace("\\", "/", $_viewFile_));
                return ob_get_clean();
            }
            else
            {
                require(str_replace("\\", "/", $_viewFile_));
            }
        }

        /**
         * Get the path to the view file
         *
         * @param string $view_name
         * @return string
         */
        private function getViewFile($view_name)
        {
            // The base path of the view files
            $path = dirname(__FILE__) .
                    DIRECTORY_SEPARATOR .'..'.
                    DIRECTORY_SEPARATOR . 'views';
            $base_path = realpath($path);

            // View file not found
            if ($base_path === false)
                throw new CException("Cannot find view path '{$path}'");

            return $base_path . DIRECTORY_SEPARATOR . $view_name .'.php';
        }

        /**
         * Magic get method. If a get<Name> method exists, then it is called.
         * Otherwise we look in the attributes array for the key.
         * If nothing is found, an exception is thrown
         *
         * @param string $name
         * @return mixed
         */
        public function __get($name)
        {
            $method = 'get'.str_replace('_', '', $name);
            if (method_exists($this, $method))
            {
                return call_user_func(array($this, $method));
            }
            elseif (array_key_exists($name, $this->attributes))
            {
                return $this->attributes[$name];
            }
            else
            {
                throw new CException("Property '{$name}' does not exists");
            }
        }

        /**
         * Magic set method. If a set<Name> method exists, then it is called.
         * Otherwise we look in the attributes array for the key.
         * If nothing is found, an exception is thrown
         *
         * @param string $name
         * @param mixed $value
         */
        public function __set($name, $value)
        {
            $method = 'set'.str_replace('_', '', $name);
            if (method_exists($this, $method))
            {
                $this->$method($value);
            }
            elseif (array_key_exists($name, $this->attributes))
            {
                $this->attributes[$name] = $value;
            }
            else
            {
                throw new CException("Property '{$name}' does not exists");
            }
        }

        /**
         * Magic isset function. Checks if an attribute is set
         *
         * @param string $name
         * @return boolean
         */
        public function __isset($name)
        {
            $method = 'get'.str_replace('_', '', $name);
            if (method_exists($this, $method))
            {
                return $this->$method() !== null; // NULL returns FALSE
            }
            else
            {
                return (isset($this->attributes[$name]));
            }
        }

        /**
         * Magic unset function. Removes a given attribute
         *
         * @param string $name
         */
        public function __unset($name)
        {
            $method = 'set'.str_replace('_', '', $name);
            if (method_exists($this, $method))
            {
                $this->$method(null);
            }
            else
            {
                // Set to NULL, isset will return false
                $this->attributes[$name] = null;
            }
        }
    }
?>