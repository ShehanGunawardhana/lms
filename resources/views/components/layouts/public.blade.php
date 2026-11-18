@props(['title' => null])
@include('layouts.public', array_merge($attributes->getAttributes(), ['title' => $title, 'slot' => $slot]))
