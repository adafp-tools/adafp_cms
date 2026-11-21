@extends($layout)
@section('content')
    <article class="max-w-3xl mx-auto px-4 py-12">
        <header class="mb-8">
            <h1 class="text-3xl font-bold">{{ $translation->title ?? $page->slug }}</h1>
        </header>
        <div class="prose max-w-none">
            {!! $body !!}
        </div>
    </article>
@endsection
