@if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-800 rounded text-sm">
        <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
