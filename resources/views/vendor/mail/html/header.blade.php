@props(['url'])

@php
    $logoUrl = asset('storage/email/notification-logo.png');
    $appName = config('app.name');
@endphp

<tr>
    <td class="header">
        <a href="{{ $url ?? config('app.url') }}" style="display: inline-block;">
            <img src="{{ $logoUrl }}" class="logo" alt="{{ $appName }}" style="max-height: 150px;">
        </a>
    </td>
</tr>
