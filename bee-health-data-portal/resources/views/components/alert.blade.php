<div {{ $attributes->merge(['class' => 'alert alert-'.$type.' fade show']) }} role="alert" >
    {{ $slot }}
  </button>
</div>
