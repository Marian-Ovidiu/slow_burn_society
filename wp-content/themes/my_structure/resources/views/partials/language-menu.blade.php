<div class="flex justify-end pt-6">
    @foreach($menu as $language)
    <div class="flag mx-3">
        <a href="{{ $language->url }}">
            {!! $language->title !!}
        </a>
    </div>
    @endforeach
</div>
