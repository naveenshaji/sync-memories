$('#nav').affix({
    offset: {
        top: $('header').height()
    }
});

var $container = $('.isotope');
$container.isotope({
    itemSelector: '.item',
    layoutMode: 'fitRows'
});

$('#filters').on('click', 'a', function () {
    var filterValue = $(this).attr('data-filter');
    $container.isotope({
        filter: filterValue
    });
});

$(function () {
    //$('#notes-selector').css.minHeight=$(window).width();  
})



(function ($) {
    var $container = $('.isotope'),
        colWidth = function () {
            var w = $container.width(),
                columnNum = 1,
                columnWidth = 0;
            if (w > 1200) {
                columnNum = 5;
            } else if (w > 900) {
                columnNum = 4;
            } else if (w > 600) {
                columnNum = 3;
            } else if (w > 300) {
                columnNum = 2;
            }
            columnWidth = Math.floor(w / columnNum);
            $container.find('.item').each(function () {
                var $item = $(this),
                    multiplier_w = $item.attr('class').match(/w(\d)/),
                    //multiplier_h = $item.attr('class').match(/item-h(\d)/),
                    width = multiplier_w ? columnWidth * multiplier_w[1] - 4 : columnWidth - 4;
                //height = multiplier_h ? columnWidth*multiplier_h[1]*0.5-4 : columnWidth*0.5-4;
                $item.css({
                    width: width,
                    //height: height
                });
            });
            return columnWidth;
        },
        isotope = function () {
            $container.isotope({
                resizable: false,
                itemSelector: '.item',
                masonry: {
                    columnWidth: colWidth(),
                    gutterWidth: 4
                }
            });
        };
    isotope();
    $(window).smartresize(isotope);
}(jQuery));