$(document).ready(function () {

    $('#image-container').on({
        'mouseenter':function () {
            $(this).children('.element').fadeTo('slow', 0.4);
            //$(this).children('.element').children('.element-data').hide();

        },
        'mouseleave':function (e, arg) {
            if (arg == undefined) arg = 'slow';
            $(this).children('.element').fadeTo(arg, 0.2).children('.element-data').hide();


        }
    }).on('click', '.element', function () {
            //console.log('children: ' + $(this).find('a').length);
            window.location.href = $(this).find('a').attr("href");
        }
    );

    $('.element').on({'mouseenter':function (e) {
        e.stopPropagation();


        $(this).animate({opacity:1}, "fast");


        var cl = $(this).attr('class').split(" ");
        // console.log(cl[1]);


        $("#in-this-picture a." + cl[1]).css('color', 'red');

        // $(this).children('.element-data').fadeIn();


    }, 'mouseleave':function (e) {
        e.stopPropagation();
        $(this).fadeTo('slow', 0.4);
        //$(this).children('.element-data').fadeOut();
        var cl = $(this).attr('class').split(" ");

//TODO change this
        $("#in-this-picture a." + cl[1]).css('color', 'black');
    }});

    var el = null;
    $("#in-this-picture a").on({
        'mouseenter':function () {
            el = $(this).attr("class");
            var cross = $('.element.' + el);
            //cross.fadeIn(false);
            cross.trigger('mouseenter');

        },
        'mouseleave':function () {
            el = $(this).attr("class");
            var cross = $('.element.' + el);
            $(this).css("color", "black");
            $('#image-container').trigger('mouseleave', 'fast');
            //  cross.children('.element-data').hide();
        }
    });

});