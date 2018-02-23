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
    <style>

        body {
            font-family : Helvetica, Arial;
            font-size   : 12px;
        }

        .thumb {
            position : absolute;
            width    : 584px;
            height   : 513px;
        }

        .element {
            /*display: none;*/
            position      : absolute;
            z-index       : 10000;
            /*border: 1px solid red;*/
            /*width: 42px;*/
            /*height:42px;*/
            cursor        : pointer;
            /*background-color: #fafafa;*/
            /*background: url(images/add-icon.png);*/
            border-radius : 5px;
        }

        .element-data {
            display          : none;
            background-color : #fafafa;
            color            : #000;
            padding          : 10px;
            width            : 120px;
            position         : relative;
            border-radius    : 5px;
            border           : 1px solid #ccc;
            box-shadow       : 3px 3px 3px #878787;
        }

        #image-container {
            /*border: 1px solid blue;*/
            width    : 584px;
            height   : 513px;
            position : relative;
            float    : left;
        }

        .dont-show {
            /*display: none;*/
        }

        #elements {
            float       : left;
            margin-left : 10px;
        }

        .toolTip {
            position : absolute;
            z-index  : 10000;
        }

        .highlight {
            border           : 1px solid red;
            background-color : #ffeb87;
        }

        .green-border {
            border : 1px solid green;
        }

        .select-box {
            padding       : 10px;
            border-radius : 5px;
            border        : 1px solid #ccc;
            margin-bottom : 5px;
        }

        a.remove, a.remove:visited {
            color : #000;
        }

        a.remove:hover {
            color           : #fff;
            text-decoration : underline;
        }

        a.remove {
            padding-left : 10px;
        }

        .element-hover {
            background-color : #ccc;
        }

        .red-button {
            border-radius    : 5px;
            text-shadow      : 1px 1px 1px #ccc;
            color            : #fff;
            padding          : 5px;
            width            : 50px;
            border           : none;
            background-color : #ff4500;
            float            : right;
            cursor           : pointer;
        }

        .clear {
            clear : both;
        }

        .red-button:hover {
            background-color : #2ea5ff;
        }

        .message {
            padding          : 10px;
            background-color : #00a800;
            color            : #fff;
            border-radius    : 5px;
            margin           : 20px;
        }

        .warning {
            background-color : #ff7b3d;
        }

    </style>
    <?php
    wp_head();
    ?>


</head>
<body>
<h3>Please insert/modify:</h3>
<?php
$message = "";

if ($_GET['post']) {
    $tags = new tagsPlugin($post = get_post($_GET['post']));

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
            $tags->addPoint(new pointPlugin(array('x' => $tArray[0], 'y' => $tArray[1]), $related[$i]));
        }

        $tags->save();
        $message = "All points have been saved! ";
        ?>
        <div class="message"><?php echo $message; ?></div>
        <?php
    } // if post

    ?>
    <div id="image-container">
        <div class='thumb'><?php echo get_the_post_thumbnail($post->ID, 'large'); ?> </div>
        <?php
        echo $tags->printInPage();
        ?>
    </div>

    <div id="elements">
        <form method="post" id="elements-form" action='<?php echo $_SERVER['REQUEST_URI']; ?>'>
            <?php
            echo $tags->generateOptions();
            ?>
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

            console.log("X: " + X + " Y: " + Y);

            /**
             * creating and adding a new element
             */
            var newElement = document.createElement('div');
            var icon = document.createElement('img');
            //adding the + icon
            $(icon).attr("src", '<?php echo site_url().'/'.PLUGINDIR.'/tagger/images/add-icon.png'; ?>');
            /**
             * @todo get the icon path using options instead of hardcoding it
             */

            Y = Y - 20;
            X = X - 20;

            $(newElement).addClass('element').addClass("" + elementCounter + "").css(
                {
                    'top': Y,
                    'left': X,
                    'display': 'block'
                }
            ).prepend($(icon));

            var newElementBox = document.createElement('div');

            //adding the select box
            //$('#elements-form').append(createElementBox(""+elementCounter+"", X + ", " + Y ));
            $(createElementBox("" + elementCounter + "", X + ", " + Y)).insertBefore(".red-button");

            //show the submit button
            $('#elements-form input[type=submit]').show();

            //adding the element over the image
            $('#image-container').append($(newElement)); //shows the red square

            //$('#elements').prepend($(newElementBox)); //

            elementCounter++;
        }); //thumb click

        $('.element').live('mouseover', function () {

            var cl = $(this).clone().removeClass('element').attr("class");
            $(this).addClass("element-hover");
            $('#' + cl).addClass('highlight');
        }).live('mouseout', function () {
            $(this).removeClass("element-hover");
            var cl = $(this).clone().removeClass('element').attr("class");
            $('#' + cl).removeClass('highlight');
        });

        /**
         * remove an element
         */
        $('.remove').live('click',
            function (e) {
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
                $('#image-container > .' + cl).css("background-color", "#ccc");
                $(this).addClass('highlight');

            },
            'mouseleave': function () {
                var cl = $(this).attr("id");
                $('#image-container > .' + cl).css("background-color", "");
                $(this).removeClass('highlight');
            }
        }, '.select-box');

        $(".message").fadeOut(2000);

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