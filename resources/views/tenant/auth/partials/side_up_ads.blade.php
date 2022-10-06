@if ($useLoginGlobal)
    @if ($login->ads ?? false)
            <img class="auth__logo-form" src="{{ $login->ads }}" alt="Logo formulario" width="250"/>
    @endif
@endif
