<?php

/**
 * This is the page that the admin sees. It's retrieved in an IFRAME element and shouldn't be reached by the user (front-end)
 */
require_once WP_PLUGIN_DIR . '/' . 'tagger/tagger.php';

?>
<!DOCTYPE html>
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width"/>
    <title></title>
    <script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'></script>
    <style></style>
    <link rel="stylesheet" href="<?php echo site_url() . '/' . PLUGINDIR . '/tagger/assets/dist/css/admin/admin-style.css';?>">
    <?php
    wp_head();
    ?>


</head>
<body class="fancy-tagger">
<h3>Please insert/modify:</h3>
<?php
$message = "";

if ($_GET['post']) {
    $tags = new WPTagCollection($post = get_post($_GET['post']));

    $tags->load();
    
   

    if (isset($_POST['action']) && $_POST['action'] == 'reset') {
        $tags->removeAll();
        $tags->save();
        $message = 'All points have been removed.';
        echo '<div class="message warning">' . $message . '</div>';
    }

    if ((isset($_POST['related']) && isset( $_POST['elements'])) &&   ($_POST['elements']) && ($_POST['related'])) { //submit pressed
        $elements = $_POST['elements'];
        $related = $_POST['related'];

        $tags->removeAll();

        for ($i = 0; $i < count($elements); $i++) {
            $tArray = explode(",", $elements[$i]);
            $tags->addPoint(new TaggerPoint(array('x' => $tArray[0], 'y' => $tArray[1]), $related[$i]));
        }

        $tags->save();
        $message = "All points have been saved! ";
        ?>
        <div class="message"><?php echo $message; ?></div>
        <?php
    } // if post

    ?>

    <div id="image-container">
        <div class='thumb'>
            <img src="<?php echo get_the_post_thumbnail_url($post->ID); ?>" alt="" />
        </div>
        <?php echo $tags->printInPage(); ?>
    </div>

    <div id="elements">
        <form method="post" id="elements-form" action='<?php echo $_SERVER['REQUEST_URI']; ?>'>
            <div class="slider-related-products">
                <?php echo $tags->generateOptions(); ?>
            </div>
            <button class="red-button dont-show" name="submit">SAVE</button>
            <div class="clear"></div>
        </form>
    </div>

    <?php
} // if get

$selection = $tags->generateOptions(true);

?>

</body>
<script>

    $(document).ready(function () {
        //var elementCounter = 0;
        var elementCounter = $('.element').length;
        $('.thumb').click(function (event) {
            var p = $('.thumb').offset();
            var left = p.left;
            var right = p.top;

            var X = event.pageX - left;
            var Y = event.pageY - right;

            $('body.fancy-tagger').addClass('show-list');

            /**
             * creating and adding a new element
             */
            var newElement = $('<div><div class="cnt"></div></div>');
 
            $(newElement).addClass('element ' + elementCounter).attr('count', elementCounter).css({ 'top': Y, 'left': X, 'display': 'block'});

            var image = $('#image-container .thumb img');
            var perc_x = (100 * X) / image.width();
            var perc_y = (100 * Y) / image.height();

            //adding the select box
            $(".slider-related-products").append(createElementBox(elementCounter, perc_x + ", " + perc_y));

            //show the submit button
            $('#elements-form input[type=submit]').show();

            //adding the element over the image
            $('#image-container').append($(newElement)); //shows the red square

            elementCounter++;
        }); //thumb click

        // elementi in immagine
        $('.element').live('mouseover', function () {
            var cl = $(this).attr('count');
            $(this).addClass("element-hover");
            var box = $('#' + cl);
            box.addClass('highlight');
            $('.slider-related-products').animate({
                scrollLeft: box.position().left
            }, 220);
        }).live('mouseout', function () {
            $(this).removeClass("element-hover");
            var cl = $(this).attr('count');
            $('#' + cl).removeClass('highlight');
        });

        /**
         * remove an element
         */
        $('.remove').live('click', function (e) {
            e.preventDefault();
            var cl = $(this).parent().attr("id");
            $('#image-container').children().each(function (index, element) {
                if ($(element).hasClass(cl)) {
                    $(element).remove();
                    return false;
                }
            });

            $(this).parent().remove();
            if ($('#elements-form').find('.select-box').length == 0) {
                $('#elements-form').append(createInputHiddenReset());
            }
        });

        $('#elements-form').on({
            'mouseenter': function () {
                var cl = $(this).attr("id");
                $('#image-container > .element.' + cl).addClass('highlight');
                $(this).addClass('highlight');

            },
            'mouseleave': function () {
                var cl = $(this).attr("id");
                $('#image-container > .element.' + cl).removeClass('highlight');
                $(this).removeClass('highlight');
            }
        }, '.select-box');

        setTimeout(function () {
            $(".message").fadeOut(500);
        }, 2000);

    });

    /**
     * creates a box
     * @param cl the class of the box
     * @param pos position values for the created tag
     */
    function createElementBox(cl, pos) {
        var elemBox = document.createElement('div');
        var href = document.createElement('a');
        $(href).attr({
            'href': '#',
            'class': 'remove'
        }).text("Remove");
        $(elemBox).attr("id", cl).addClass(cl).html('<?php echo $selection; ?>').append($(href));
        $(elemBox).append(createInputHidden(cl, pos)).addClass('select-box');
        return $(elemBox);
    }

    /**
     * Shows a tooltip
     * @param text
     * @param e
     */
    function showTooltip(text, e) {
        var toolTip = document.createElement('div');
        $(toolTip).css({
            'left': e.pageX,
            'top': e.pageY,
            'background-color': '#fff'
        });
        $(toolTip).addClass('toolTip').text(text);
        return $(toolTip);
    }

    /**
     * Used to create an hidden element. This contains the positions of the tag.
     * it's sent together with the select value (dropdown).
     * @param cl
     * @param pos
     */
    function createInputHidden(cl, pos) {
        var input = document.createElement('input');
        $(input).attr({
            'type': 'hidden',
            'name': 'elements[]',
            'value': pos
        });
        return $(input);
    }
    /**
     * creates an input hidden, with action -> reset.
     * This is used when user removes all the tags from the image and
     * submits the form -> deletes all the previous points.
     *
     */
    function createInputHiddenReset() {
        var input = document.createElement('input');
        $(input).attr({
            'type': 'hidden',
            'name': 'action',
            'value': 'reset'
        });
        return $(input);
    }

</script>
</html>