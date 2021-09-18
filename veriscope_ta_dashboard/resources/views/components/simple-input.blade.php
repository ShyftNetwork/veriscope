<div class="form-control form-control--simple">
  <input id="{{ $name }}" type="{{ $type }}" class="{{ $errors->has($name) ? ' error' : '' }}{{ $value ? ' has-value': '' }}" name="{{ $name }}" value="{{ $value }}" required>
  <label for="{{ $name }}">{{ $label }}</label>
  @if ($errors->has($name))
        @component('components.errormessage')
            {{ $errors->first($name) }}
        @endcomponent
  @endif
</div>
