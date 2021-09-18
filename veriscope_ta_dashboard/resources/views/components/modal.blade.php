{{--
  Usage:
  @component('components.modal', [
    'img' => 'https://via.placeholder.com/620x236',
    'title' => 'Shyft Network — Purchase Agreement',
    'driver' => ['href' => '#0', 'label' => 'I agree']
  ])
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla interdum 
      accumsan nisl rhoncus dapibus. Suspendisse quis elit eu massa sagittis 
      dignissim. Sed a urna a sem placerat luctus. Mauris non diam sem. 
      Donec condimentum neque nec augue elementum tincidunt. In lectus urna, 
      convallis eu convallis eget, molestie quis est. Sed a augue turpis. 
      Suspendisse feugiat pharetra porta. Aenean imperdiet vitae risus a 
      accumsan. Vestibulum ante ipsum primis in faucibus orci luctus et 
      ultrices posuere cubilia.</p>
  @endcomponent
--}}
<div class="modal">
  <div class="modal__frame">
    <span class="modal__close">
      <svg width="22px" height="22px" viewBox="0 0 22 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <g id="Close" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
            <path d="M10.6066017,8.48528137 L19.0918831,-2.84217094e-14 L21.2132034,2.12132034 L12.7279221,10.6066017 L21.2132034,19.0918831 L19.0918831,21.2132034 L10.6066017,12.7279221 L2.12132034,21.2132034 L-2.8643754e-14,19.0918831 L8.48528137,10.6066017 L-2.88657986e-14,2.12132034 L2.12132034,-2.70894418e-14 L10.6066017,8.48528137 Z" id="Desktop-Close" fill="#373241"></path>
        </g>
      </svg>
    </span>
    @isset($img)
      <img src="{{ $img }}" class="modal__img">
    @endisset
    <div class="modal__content">
        @isset($title)
        <p class="modal__title"><strong>{{ $title }}</strong></p>
        @endisset
        {{ $slot }}
    </div>
    @isset($driver)
      <a href="{{ $driver['href'] }}" class="modal__cta btn">{{ $driver['label'] }}</a>
    @endisset
  </div>
</div>