$(window).scroll(function() {
    if ($(".navbar").offset().top > 10) {
        $(".navbar-fixed-top").addClass("top-nav-collapse");
        $('.navbar-nav>li>a').on('click', function(){
            $('.navbar-collapse').collapse('hide');
        });
    } else {
        $(".navbar-fixed-top").removeClass("top-nav-collapse");
        $('.navbar-nav>li>a').on('click', function(){
            $('.navbar-collapse').collapse('hide');
        });
    }
});

$(function() {
    $('.page-scroll a').bind('click', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: $($anchor.attr('href')).offset().top
        }, 1000, 'easeInOutExpo');
        event.preventDefault();
    });
});

$(window).resize(function(){
    drawVisualization();
    drawVisualization2();
  });