<div class="form__checkbox form__checkbox--alt">
  <input type="checkbox" id="{{ $name }}_{{ $key }}" name="{{ $name }}" value="{{ $value }}" @if($checked) checked @endif />
  <label @if(!$disabled) for="{{ $name }}_{{ $key }}" @endif>
    <span></span>
    {!! $label !!}
  </label>
</div>
