<x-mail::message>
<p>Hi {{ $mailDetails['name'] }},</p>

<p>Congratulations!</p>
<p>You have changed your passwor successfully!</p>
<p>You can login now with your new password.</p>

Thanks,<br>
Team {{ config('app.name') }}
</x-mail::message>
