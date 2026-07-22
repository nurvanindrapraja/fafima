@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
<span style="font-size: 24px; font-weight: bold; color: #3d4852;">{!! $slot !!}</span>
<div style="font-size: 14px; font-weight: normal; color: #718096; margin-top: 4px; text-decoration: none;">Family Finance Management</div>
@endif
</a>
</td>
</tr>
