<x-mail::message>
# Benvenuto su {{ config('app.name') }}!

Ciao {{ $user->name }}, siamo felici di averti a bordo.

<x-mail::button :url="'https://tuosito.it/login'">
Accedi al tuo account
</x-mail::button>

Grazie,<br>
Lo staff di {{ config('app.name') }}
</x-mail::message>
