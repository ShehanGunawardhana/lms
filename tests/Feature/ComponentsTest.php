<?php

use Illuminate\Support\Facades\Blade;

test('button component renders variants, sizes and icons', function () {
    $rendered = Blade::render('<x-button variant="primary" size="md">Click Me</x-button>');
    expect($rendered)->toContain('Click Me', 'bg-brand-600', 'min-h-[42px]');

    $nursery = Blade::render('<x-button variant="nursery" size="lg">Play Activity</x-button>');
    expect($nursery)->toContain('Play Activity', 'bg-amber-500', 'min-h-[48px]');

    $danger = Blade::render('<x-button variant="danger" size="sm">Delete</x-button>');
    expect($danger)->toContain('Delete', 'bg-rose-600', 'min-h-[36px]');
});

test('input component renders label, error and icons', function () {
    $rendered = Blade::render('<x-input name="email" label="Email Address" placeholder="you@domain.com" required />');
    expect($rendered)->toContain('Email Address', 'you@domain.com', 'required', 'text-rose-500');
});

test('select component renders options and label', function () {
    $rendered = Blade::render('<x-select name="role" label="Select Role" placeholder="Choose..."><option value="admin">Admin</option></x-select>');
    expect($rendered)->toContain('Select Role', 'Choose...', 'value="admin"');
});

test('card component renders header, content and hover effects', function () {
    $rendered = Blade::render('<x-card title="Class Roster" subtitle="Grade 1" :hover="true">Card body</x-card>');
    expect($rendered)->toContain('Class Roster', 'Grade 1', 'Card body', 'card-hover');
});

test('modal component renders alpine triggers and title', function () {
    $rendered = Blade::render('<x-modal name="confirm-delete" title="Confirm Delete">Are you sure?</x-modal>');
    expect($rendered)->toContain('confirm-delete', 'Confirm Delete', 'Are you sure?', 'x-show="show"');
});

test('badge component renders status variants and indicator dot', function () {
    $success = Blade::render('<x-badge variant="success" :dot="true">Present</x-badge>');
    expect($success)->toContain('Present', 'bg-emerald-50', 'bg-emerald-500');

    $nursery = Blade::render('<x-badge variant="nursery">Toddler</x-badge>');
    expect($nursery)->toContain('Toddler', 'bg-amber-100');

    $danger = Blade::render('<x-badge variant="danger" :dot="true">Absent</x-badge>');
    expect($danger)->toContain('Absent', 'bg-rose-50', 'bg-rose-500');
});

test('toast component renders message and dismiss button', function () {
    $rendered = Blade::render('<x-toast type="success" title="Saved" message="Changes persisted." />');
    expect($rendered)->toContain('Saved', 'Changes persisted.', 'border-l-emerald-500');
});

test('empty state component renders icon and call to action slot', function () {
    $rendered = Blade::render('<x-empty-state title="No Submissions" description="Students have not submitted work yet."><button>Upload Now</button></x-empty-state>');
    expect($rendered)->toContain('No Submissions', 'Students have not submitted work yet.', 'Upload Now');
});

test('file drop component renders allowed types and 50MB notice', function () {
    $rendered = Blade::render('<x-file-drop name="homework" :maxSizeMb="50" />');
    expect($rendered)->toContain('MP4, PDF, JPG, PNG or ZIP', 'Max 50 MB', 'isDragging');
});

test('countdown component renders countdown target and alpine bindings', function () {
    $rendered = Blade::render('<x-countdown targetDate="2026-12-31 23:59:59" label="Activity Deadline" />');
    expect($rendered)->toContain('Activity Deadline', '2026-12-31 23:59:59', 'updateCountdown');
});
