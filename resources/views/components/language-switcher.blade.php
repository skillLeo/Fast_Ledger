@if(request()->routeIs('company.*'))
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" 
                data-bs-toggle="dropdown" aria-expanded="false">
            @if(app()->getLocale() === 'es')
                <i class="fa fa-language me-1"></i> Español
            @else
                <i class="fa fa-language me-1"></i> English
            @endif
        </button>
        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
            <li>
                <form action="{{ route('language.switch') }}" method="POST" class="dropdown-item-form">
                    @csrf
                    <input type="hidden" name="locale" value="en">
                    <button type="submit" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                        <i class="fa fa-check me-2 {{ app()->getLocale() === 'en' ? '' : 'invisible' }}"></i>
                        English (UK)
                    </button>
                </form>
            </li>
            <li>
                <form action="{{ route('language.switch') }}" method="POST" class="dropdown-item-form">
                    @csrf
                    <input type="hidden" name="locale" value="es">
                    <button type="submit" class="dropdown-item {{ app()->getLocale() === 'es' ? 'active' : '' }}">
                        <i class="fa fa-check me-2 {{ app()->getLocale() === 'es' ? '' : 'invisible' }}"></i>
                        Español
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <style>
        .dropdown-item-form {
            padding: 0;
            margin: 0;
        }
        .dropdown-item-form button {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            cursor: pointer;
        }
        .dropdown-item.active {
            background-color: #e9ecef;
            font-weight: 600;
        }
    </style>
@endif