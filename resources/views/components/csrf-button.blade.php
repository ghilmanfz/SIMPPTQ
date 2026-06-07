@csrf
<button {{ $attributes->merge(['class' => 'h-9 w-9 rounded-lg text-white text-sm flex items-center justify-center transition']) }}>
    {{ $slot }}
</button>
