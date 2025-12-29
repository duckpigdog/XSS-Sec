document.addEventListener('DOMContentLoaded', function () {
  setTimeout(function () {
    var el = document.getElementById('x');
    if (el && typeof el.focus === 'function') {
      try { el.focus(); } catch (e) {}
    }
  }, 50);
  var clear = document.getElementById('clear-btn');
  if (clear) {
    clear.addEventListener('click', function (e) {
      e.preventDefault();
      window.location.href = 'index.php';
    });
  }
});
