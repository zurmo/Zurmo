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

// Register the api javascript
Yii::app()->getClientScript()->registerScriptFile('http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false');

// Translate any special types
if (isset($options['mapTypeId']))
	$options['mapTypeId'] = 'google.maps.MapTypeId.'.$options['mapTypeId'];
?>
map = new GMap2(document.getElementById("<?php echo $container_id; ?>"));
map.setCenter(new GLatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>), 13);
var latlng = new GLatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>);
map.addOverlay(new GMarker(latlng));
map.setUIToDefault();