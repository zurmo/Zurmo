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
 * This view file adds a marker to the centered map to the required lat / long
 * coordinates. It also adds a event listener on click of which address
 * is displayed in the info window.
 */
?>
var marker = new google.maps.Marker(latlng);
map.addOverlay(marker);
var infowindow = new google.maps.InfoWindow({
                 content: address+'<br /><br /><a href="http://maps.google.com/maps?saddr=&daddr=' + latlng.toUrlValue() + '" target ="_blank">Get Directions<\/a>'
});
google.maps.Event.addListener(marker, "click", function() {infowindow.open(map, marker);});