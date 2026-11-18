@props(['title' => null, 'header' => null])
@include('layouts.app', array_merge($attributes->getAttributes(), ['title' => $title, 'header' => $header, 'slot' => $slot]))
