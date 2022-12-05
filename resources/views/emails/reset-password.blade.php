<x-mail::message>
<p>Hi {{ $mailDetails['name'] }},</p>

<p>Please click on the link below to reset your password.</p>
<p><a href="{{ config('app.url') }}/reset-password?token={{ $mailDetails['token'] }}"><u>Reset Password</u></a></p>

Thanks,<br>
Team {{ config('app.name') }}
</x-mail::message>
