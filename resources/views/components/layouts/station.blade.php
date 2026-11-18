@props(['title' => null, 'stationName' => null])
@include('layouts.station', array_merge($attributes->getAttributes(), ['title' => $title, 'stationName' => $stationName, 'slot' => $slot]))
