<x-mail::message>
<p>Hi {{ $mailDetails['name'] }},</p>

<p>Here's the OTP to validate your email.</p>
<h4>{{ $mailDetails['otp'] }}</h4>
<p>This OTP is valid for next 5 minutes.</p>

Thanks,<br>
Team {{ config('app.name') }}
</x-mail::message>
