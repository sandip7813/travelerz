<x-mail::message>
<p>Hi {{ $mailDetails['name'] }},</p>

<p>Congratulations!</p>
<p>Your account has been verified successfully!</p>
<p>You can now login.</p>

Thanks,<br>
Team {{ config('app.name') }}
</x-mail::message>
