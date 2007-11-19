
var state = '';
var map = null;
var map_scale;
var geocoder = null;

function load() {
    if (GBrowserIsCompatible()) {
        geocoder = new GClientGeocoder();
        map = new GMap2(document.getElementById("map"));
        map.addControl(new GLargeMapControl());
        map.addControl(new GMapTypeControl());
        map.enableDoubleClickZoom();
        map.setCenter(new GLatLng(15, 10), 2);
        GEvent.addListener(map, "click", function(overlay, point)	{
            if (overlay && overlay.summary) overlay.openInfoWindowHtml(overlay.summary);
        });
    }
}

function searchGlobal(i) {
    state = 'global';
    put_html('loading', loading);
    put_val('mapsearch', 0);
    put_val('CityName', '');
    put_val('IdCountry', '');
    loadMap(i);
}

function searchByMap(i) {
    state = 'map';
    put_html('loading', loading);
    put_val('mapsearch', 1);
    var bounds = map.getBounds();
    put_val('bounds_zoom', map.getZoom());
    var bounds_center = bounds.getCenter();
    put_val('bounds_center_lat', bounds_center.lat());
    put_val('bounds_center_lng', bounds_center.lng());
    var bounds_sw = bounds.getSouthWest();
    var bounds_ne = bounds.getNorthEast();
    put_val('bounds_sw_lat', bounds_sw.lat());
    put_val('bounds_ne_lat', bounds_ne.lat());
    put_val('bounds_sw_lng', bounds_sw.lng());
    put_val('bounds_ne_lng', bounds_ne.lng());
    put_val('CityName', '');
    put_val('IdCountry', '');
    loadMap(i);
}

function searchByText(address, i) {
    state = 'text';
    put_html('loading', loading);
    put_val('mapsearch', 0);
    if(geocoder) {
        geocoder.getLocations(
            address,
            function(response) {
                if(!response || response.Status.code != 200) put_html('loading', addressNotFound);
                else {
                    var place = response.Placemark[0];
                    var point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
                    put_val('CityName', '');
                    put_val('IdCountry', '');
                    map_scale = 3;
                    scanObject(place);
                    if(!mapoff) {
                    	map.setCenter(point, map_scale);
                    	map.addOverlay(new GMarker(point));
                    }
                    loadMap(i);
                }
            }
        );
    }
}

function scanObject(object)
{
    if(typeof(object) != 'object') return;
    for(key in object) {
        var item = object[key];
        if(typeof(item) == "object") scanObject(item);
        else if(key == "ThoroughfareName") map_scale = (map_scale < 11 ? 11 : map_scale);
        else if(key == "LocalityName" || key == "DependentLocalityName") {
            put_val('CityName', item);
            map_scale = (map_scale < 10 ? 10 : map_scale);
        }
        else if(key == "SubAdministrativeAreaName") map_scale = (map_scale < 9 ? 9 : map_scale);
        else if(key == "AdministrativeAreaName") map_scale = (map_scale < 7 ? 7 : map_scale);
        else if(key == "CountryNameCode") {
            put_val('IdCountry', item);
            map_scale = (map_scale < 5 ? 5 : map_scale);
            if(map_scale <= 5) switch(item) {
                case 'RU': map_scale = 3; break;
                case 'US': map_scale = 3; break;
                case 'CA': map_scale = 3; break;
                case 'CN': map_scale = 3; break;
                case 'BR': map_scale = 3; break;
                case 'AU': map_scale = 3; break;
            }
        }
    }
}

function loadMap(i)
{
    if(!mapoff) map.clearOverlays();
    put_val('start_rec', i);
    new Ajax.Request('searchmembers/ajax', {
        parameters: $('searchmembers').serialize(true),
        onSuccess: function(req) {
            //alert(req.responseText);return;
            var xmlDoc = req.responseXML;
            var header = getxmlEl(xmlDoc, "header");
            var detail = header[0].getAttribute("header");
            var markers = getxmlEl(xmlDoc, "marker");
            var i, point, marker, accomodation;
            for(i = 0; i < markers.length; i++) {
                point = new GPoint(
                    parseFloat(markers[i].getAttribute("Longitude")),
                    parseFloat(markers[i].getAttribute("Latitude"))
                );
                detail += markers[i].getAttribute("detail");
                if(!mapoff) {
                    accomodation = markers[i].getAttribute("accomodation");
                    if(accomodation == 'anytime') marker = new GMarker(point, icon);
                    else if(accomodation == 'neverask') marker = new GMarker(point, icon2);
                    else marker = new GMarker(point, icon3);
                    marker.summary = markers[i].getAttribute("summary");
                    map.addOverlay(marker);
                }
            }
            if(!mapoff && state == 'global' && markers.length) {
                var minLat = 90, maxLat = -90;
                var aveLat = 0, delLat, lat, lng;
                for(i = 0; i < markers.length; i++) {
                    lat = parseFloat(markers[i].getAttribute("Latitude"));
                    if(lat > maxLat) maxLat = lat;
                    if(lat < minLat) minLat = lat;
                    aveLat += lat;
                }
                delLat = maxLat - minLat;
                aveLat /= markers.length;
                var minLng1 = 180, maxLng1 = -180, aveLng1 = 0;
                var minLng2 = 360, maxLng2 = 0, aveLng2 = 0;
                for(i = 0; i < markers.length; i++) {
                    lng = parseFloat(markers[i].getAttribute("Longitude"));
                    if(lng > maxLng1) maxLng1 = lng;
                    if(lng < minLng1) minLng1 = lng;
                    aveLng1 += lng;
                    if(lng < 0) lng += 180;
                    else lng -= 180;
                    if(lng > maxLng2) maxLng2 = lng;
                    if(lng < minLng2) minLng2 = lng;
                    aveLng2 += lng;
                }
                var delLng1 = maxLng1 - minLng1;
                var delLng2 = maxLng2 - minLng2;
                aveLng1 /= markers.length;
                aveLng2 /= markers.length;
                if(lng <= 0) aveLng2 -= 180;
                else aveLng2 += 180;
                var aveLng, delLng;
                if(delLng2 < delLng1) {
                    delLng = delLng2;
                    aveLng = aveLng2;
                }
                else {
                     delLng = delLng1;
                     aveLng = aveLng1;
                }
                if(delLat > delLng) delLng = delLat;
                if(delLng > 70) map_scale = 2;
                else if(delLng > 50) map_scale = 3;
                else if(delLng > 25) map_scale = 4;
                else if(delLng > 5) map_scale = 5;
                else map_scale = 6;
                point = new GLatLng(aveLat, aveLng);
               	map.setCenter(point, map_scale);
            }
            var footer = getxmlEl(xmlDoc, "footer");
            detail += footer[0].getAttribute("footer");
            var page = getxmlEl(xmlDoc, "page");
            detail += page[0].getAttribute("page");
            put_html("member_list", detail);
            put_html('loading', '');
        }
    });
}

function getxmlEl(x, s) {return x.documentElement.getElementsByTagName(s);}

function page_navigate(i)
{
    if(state == 'text') searchByText(get_val("address"), i);
    else if(state == 'map') searchByMap(i);
    else loadMap(i);
}

function get_val(field) {return document.getElementById(field).value;}

function put_val(field, s) {document.getElementById(field).value = s;}

function get_html(field) {return document.getElementById(field).innerHTML;}

function put_html(field, s) {document.getElementById(field).innerHTML = s;}

function chkEnt(field, e)
{
    var keycode;
    if (window.event) keycode = window.event.keyCode;
    else if (e) keycode = e.which;
    else return false;

    if(keycode == 13) return true;
    return false;
}

// Create our "tiny" marker icon - SMALL VERSION

var icon = new GIcon(); // green - agreeing
icon.image = "images/icons/gicon1.png";
icon.shadow = "images/icons/gicon1_shadow.png";
icon.iconSize = new GSize(29, 40);
icon.shadowSize = new GSize(38, 40);
icon.iconAnchor = new GPoint(17, 40);
icon.infoWindowAnchor = new GPoint(17, 40);

var icon2 = new GIcon(); // black
icon2.image = "images/icons/gicon2.png";
icon2.shadow = "images/icons/gicon1_shadow.png";
icon2.iconSize = new GSize(29, 40);
icon2.shadowSize = new GSize(38, 40);
icon2.iconAnchor = new GPoint(17, 40);
icon2.infoWindowAnchor = new GPoint(17, 40);

var icon3 = new GIcon(); // grey - doubting
icon3.image = "images/icons/gicon3.png";
icon3.shadow = "images/icons/gicon1_shadow.png";
icon3.iconSize = new GSize(29, 40);
icon3.shadowSize = new GSize(38, 40);
icon3.iconAnchor = new GPoint(17, 40);
icon3.infoWindowAnchor = new GPoint(17, 40);

// Create our "tiny" marker icon - BIG version
/*
var icon = new GIcon(); // green - agreeing
icon.image = "images/icons/gicon1.png";
icon.shadow = "images/icons/gicon1_shadow.png";
icon.iconSize = new GSize(50, 60);
icon.shadowSize = new GSize(50, 60);
icon.iconAnchor = new GPoint(25, 58);
icon.infoWindowAnchor = new GPoint(25, 58);

var icon2 = new GIcon(); // black
icon2.image = "images/icons/gicon2.png";
icon2.shadow = "images/icons/gicon1_shadow.png";
icon2.iconSize = new GSize(50, 60);
icon2.shadowSize = new GSize(50, 60);
icon2.iconAnchor = new GPoint(25, 58);
icon2.infoWindowAnchor = new GPoint(25, 58);

var icon3 = new GIcon(); // grey - doubting
icon3.image = "images/icons/gicon3.png";
icon3.shadow = "images/icons/gicon1_shadow.png";
icon3.iconSize = new GSize(50, 60);
icon3.shadowSize = new GSize(50, 60);
icon3.iconAnchor = new GPoint(25, 58);
icon3.infoWindowAnchor = new GPoint(25, 58);
*/

window.onload = load;