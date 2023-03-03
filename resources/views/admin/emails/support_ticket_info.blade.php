<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>{{ __('Suppor Ticket Information') }}</title>
</head>
<body>
	<p>
		{{ __('Thank you') }} {{ ucfirst($user->name) }} {{ __('for contacting our support team. A support ticket has been opened for you. You will be notified when a response is made by email. The details of your ticket are shown below') }}:
	</p>

	<p>{{ __('Title') }}: {{ $ticket->subject }}</p>
	<p>{{ __('Priority') }}: {{ $ticket->priority }}</p>
	<p>{{ __('Status') }}: {{ $ticket->status }}</p>

	<p>
		{{ __('You can view the ticket at any time at') }} {{ url('tickets/'. $ticket->ticket_id) }}
	</p>

</body>
</html>