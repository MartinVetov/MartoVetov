@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'schema' => [],
    'breadcrumbs' => [],
    'noindex' => false,
])

@php
    $pageTitle = $title ? $title.' | '.config('nt.brand') : config('nt.brand').' — намери техниката за твоята задача';
    $metaDescription = $description ?: 'Опиши какво трябва да свършиш, къде и кога. Ще намерим подходящи доставчици на строителна и специализирана техника в твоя район.';
    $canonicalUrl = $canonical ?: url()->current();
    $schemaGraph = collect($schema)->filter()->values();

    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('nt.brand'),
        'url' => url('/'),
        'email' => config('nt.contact.email'),
        'telephone' => config('nt.contact.phone'),
        'areaServed' => 'BG',
    ];

    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
@endphp

<!DOCTYPE html>
<html lang="bg" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if ($noindex)
        <meta name="robots" content="noindex, nofollow">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('nt.brand') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:locale" content="bg_BG">
    @if ($image)
        <meta property="og:image" content="{{ $image }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script type="application/ld+json">{!! json_encode($organizationSchema, $jsonFlags) !!}</script>

    @foreach ($schemaGraph as $item)
        <script type="application/ld+json">{!! json_encode($item, $jsonFlags) !!}</script>
    @endforeach

    @stack('head')
</head>
<body class="flex min-h-full flex-col bg-white">
    <x-site.header />

    @if (! empty($breadcrumbs))
        <x-ui.breadcrumbs :items="$breadcrumbs" />
    @endif

    <main class="flex-1">
        <x-ui.flash />
        {{ $slot }}
    </main>

    <x-site.footer />
</body>
</html>
