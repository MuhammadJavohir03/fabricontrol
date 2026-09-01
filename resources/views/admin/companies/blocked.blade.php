<x-layouts.sidebar title="Muddat tugagan">

    <div class="content" style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
        <div class="card" style="max-width:420px; width:100%;">
            <div class="card__body" style="text-align:center;">
                <div style="font-size:44px; margin-bottom:8px;">⏳</div>
                <h2 style="margin-bottom:10px;">Obuna muddati tugagan</h2>
                <p style="color:var(--ink-soft); margin:0 0 24px;">
                    @if ($company)
                        «{{ $company->nomi }}» companiyasi uchun kirish muddati tugagan.
                    @else
                        Sizning companiyangiz uchun kirish muddati tugagan.
                    @endif
                    Davom etish uchun to'lovni amalga oshiring va tizim administratori bilan bog'laning.
                    <br>
                    +998942003584
                </p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn--ghost">Chiqish</button>
                </form>
            </div>
        </div>
    </div>

</x-layouts.sidebar>