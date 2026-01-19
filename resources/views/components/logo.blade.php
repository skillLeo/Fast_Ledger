@props(['src', 'alt', 'href' => '#'])

<div class="desktop-dark">
    <a href="{{ $href }}">
        <img src="{{ asset($src) }}" alt="{{ $alt }}">
    </a>
</div>