@component('mail::message')
# **{{ $data['email_from'] }}** has transfered some files to you.

{{ $data['message'] }}

@foreach ($data['links'] as $key => $node)
- **File Name: {{ $key }}**
- {{ config("app.url") }}/download/{{ $node }}
@endforeach

Thanks,<br>
{{ config('app.name') }}
@endcomponent
