@component('mail::message')
# You have been invited to join {{ config('app.name') }} by {{ ucfirst(auth()->user()->name) }}

Join {{ config('app.name') }} to experience world's most secure and durable cloud archiving service.

@component('mail::button', ['url' => "{{ config('app.url') }}/?ref={{ auth()->user()->referral_id }}"])
Register Now
@endcomponent


Thanks,<br>
{{ config('app.name') }}
@endcomponent
