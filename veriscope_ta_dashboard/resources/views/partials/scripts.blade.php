@if(Config::get('app.env') !== 'new_prod')
  <script>
  (function (d, t) {
    var bh = d.createElement(t), s = d.getElementsByTagName(t)[0];
    bh.type = 'text/javascript';
    bh.src = 'https://www.bugherd.com/sidebarv2.js?apikey=atahtsst1jsvfnhm6xjalw';
    s.parentNode.insertBefore(bh, s);
    })(document, 'script');
  </script>
@endif
