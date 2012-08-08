function showMap( mapID, markerHTML ) {
   var map = new GMap2(document.getElementById(mapID));
   map.addControl(new GSmallMapControl());
   map.addControl(new GScaleControl());
   map.addControl(new GMapTypeControl());
   var location = new GLatLng(lat, lng);
   map.setCenter(location, zoom);
   var point = new GLatLng(lat, lng);
   marker = new GMarker(point);
   map.addOverlay(marker);
   GEvent.addListener(marker,"click", function(){
      map.panTo(marker.getLatLng());
      var markerOffset = map.fromLatLngToDivPixel(marker.getPoint());
      marker.openInfoWindowHtml(markerHTML);
   });
}
