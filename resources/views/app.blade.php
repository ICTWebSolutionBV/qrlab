<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|roboto:400,500,700|open-sans:400,600,700|lato:400,700|montserrat:400,500,600,700|poppins:400,500,600,700|nunito:400,600,700|raleway:400,500,600,700|roboto-mono:400,500,700|source-code-pro:400,500,700|space-mono:400,700|ibm-plex-mono:400,500,700|fira-code:400,500,700" rel="stylesheet" />
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 antialiased">
    @inertia
</body>
</html>
