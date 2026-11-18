<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/test-public-layout', function () {
        return view('layouts.public', ['title' => 'Test Public']);
    });

    Route::get('/test-app-layout', function () {
        return view('layouts.app', ['title' => 'Test App', 'header' => 'My Dashboard']);
    });

    Route::get('/test-station-layout', function () {
        return view('layouts.station', ['title' => 'Test Station', 'stationTitle' => 'Attendance Kiosk']);
    });

    Route::get('/test-guest-layout', function () {
        return view('layouts.guest', ['title' => 'Test Guest', 'heading' => 'Login to Portal']);
    });
});

test('public layout renders brand, navigation and livewire assets', function () {
    $response = $this->get('/test-public-layout');

    $response->assertStatus(200);
    $response->assertSee('EduPortal');
    $response->assertSee('Institute & Nursery', false);
    $response->assertSee('livewire');
});

test('app layout renders navigation and header', function () {
    $response = $this->get('/test-app-layout');

    $response->assertStatus(200);
    $response->assertSee('My Dashboard');
    $response->assertSee('Classes &amp; Groups', false);
});

test('station layout renders station title and kiosk controls', function () {
    $response = $this->get('/test-station-layout');

    $response->assertStatus(200);
    $response->assertSee('Attendance Kiosk');
    $response->assertSee('ONLINE');
});

test('guest layout renders authentication card', function () {
    $response = $this->get('/test-guest-layout');

    $response->assertStatus(200);
    $response->assertSee('Login to Portal');
});
