@props(['method' => 'POST', 'action', 'params' => []])

<form action="{{ Str::contains($action, '.') ? route($action, $params) : $action }}" method="{{ strtoupper($method) }}" class="">
    @if ($method !== 'GET')
        @csrf
        @method($method)
    @endif

    {{ $slot }}
</form>