@props(['title' => null, 'heading' => null, 'subheading' => null])
@include('layouts.guest', array_merge($attributes->getAttributes(), ['title' => $title, 'heading' => $heading, 'subheading' => $subheading, 'slot' => $slot]))
