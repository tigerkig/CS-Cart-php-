(function (_, $) {
  $(document).ready(function () {
    if (!$('.navbar-right li').length) {
      return;
    } // Navbar submenu position


    $(document).on('mouseenter', '.navbar-right li, .subnav .nav-pills:not(.mobile-visible) li', function () {
      var adminContentWidth = $('.admin-content .navbar-admin-top').width();
      $dropdownMenu = $(this).children('.dropdown-menu');

      if ($dropdownMenu.length) {
        var elmLeftPosition = $dropdownMenu.offset().left;
        elmPosition = elmLeftPosition + $dropdownMenu.width();
        addedСlass = $(this).hasClass('dropdown-submenu') ? 'dropdown-menu-to-right' : 'pull-right';

        if (_.language_direction === 'ltr' && elmPosition > adminContentWidth || _.language_direction === 'rtl' && elmLeftPosition < 0) {
          $dropdownMenu.addClass(addedСlass);
        }
      }
    });
    $(document).on('mouseleave', '.navbar-right li, .subnav .nav-pills:not(.mobile-visible) li', function () {
      var toggleClass = $(this).hasClass('dropdown-top-menu-item') || $(this).hasClass('notifications-center__opener-wrapper') ? 'dropdown-menu-to-right' : 'dropdown-menu-to-right pull-right';
      $(this).children('.dropdown-menu').removeClass(toggleClass);
    });
  });
})(Tygh, Tygh.$);