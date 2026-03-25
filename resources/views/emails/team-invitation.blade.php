@component('mail::message')
# You've been invited!

You have been invited to join the sales team on FlowCRM.

@component('mail::button', ['url' => $url])
Accept Invitation
@endcomponent

If you did not expect this invitation, you can discard this email.

Thanks,<br>
Manager: {{ $managerName }}
@endcomponent