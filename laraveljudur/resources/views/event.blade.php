{{-- resources/views/event.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title }}</title>

    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="{{ $event->title }}" />
    <meta property="og:description" content="{{ $event->description }}" />
    <meta property="og:image" content="{{ asset($event->image_url) }}" /> {{-- Adjust if image_url is relative --}}
    <meta property="og:url" content="{{ url('/events/' . $event->id) }}" />

    {{-- Add other meta tags and links here --}}
</head>
<body>
    {{-- Your event details HTML goes here --}}
</body>
</html>
