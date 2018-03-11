$(document).ready(function () {

	var get_related_from_count = function (el) {
		var cl = el.attr('count');
		return {
			desktop: $('.showroom-related .related-product.' + cl),
			mobile: $('#in-this-picture .related-product.' + cl)
		};
	};

	$('.single-room_setting #image-container .element').on({
		'mouseleave': function (evt) {
			if($(evt.target).parents('.element').length < 0){
				var rels = $('.showroom-related .related-product');
				rels.removeClass('show');
				rels.css({
					left: 'auto',
					top: 'auto'
				});
			}
		}
	});

	$('#in-this-picture .related-product').on({
		'mouseenter': function () {
			var cl = $(this).attr("id");
			$('#image-container > .element.' + cl).addClass('highlight');
			$(this).addClass('highlight');

		},
		'mouseleave': function () {
			var cl = $(this).attr("id");
			$('#image-container > .element.' + cl).removeClass('highlight');
			$(this).removeClass('highlight');
		},
		'click': function () {
			var link = $(this).data("link");
			window.location.href = link;
		}
	});

	$('.single-room_setting #image-container .element').on({
		'mouseenter': function (evt) {
			var _this = $(this);
			var rel = get_related_from_count(_this).desktop;
			var rel_mobile = get_related_from_count(_this).mobile;

			// desktop
			if(!$('#in-this-picture').is(':visible')) {

				var x = _this.position().left;
				var y = _this.position().top;
				var width_rel = rel.width();
				var height_rel = rel.height();
				var width_cnt = $('#image-container').width();
				var height_cnt = $('#image-container').height();
				var posX = x - width_rel/2;
				var posY = y - height_rel/2;


				// posizionamento orizzontale
				if(posX < 10){
					posX = 10;
				}
				if(posX + width_rel > width_cnt) {
					posX = width_cnt - 10 - width_rel;
				}

				// posizionamento verticale
				if(posY < 10){
					posY = 10;
				}
				else if(posY + height_rel > height_cnt) {
					posY = height_cnt - 10 - height_rel;
				}

				// setta la posizione e la visibilità
				rel.addClass('show');
				rel.css({
					left: posX,
					top: posY
				});

			}
			// mobile
			else {

				rel_mobile.addClass('highlight');
				var left = rel_mobile.position().left + $('#in-this-picture').scrollLeft();
				$('#in-this-picture').animate({
					scrollLeft: left
				}, 220);

			}

		},
		'mouseleave': function () {
			// mobile
			if($('#in-this-picture').is(':visible')) {
				$('#in-this-picture .related-product').removeClass('highlight');
			}
		}
	});

	$('.showroom .showroom-related article').on({
		'click': function (evt) {
			window.location.href = $(this).data("link");
		},
		'mouseenter': function (evt) {

		},
		'mouseleave': function () {
			var _this = $(this);
			_this.removeClass('show');
		}
	});

});