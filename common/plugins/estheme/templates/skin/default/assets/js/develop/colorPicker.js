/*!
 * ColorPicker, https://github.com/ddaghan/ColorPicker, under MIT License
 */
$.fn.colorPicker = function() {
	$(document).mouseup(function(){$('.colorPickerPane').remove();})
	return this.each(function() {
		"use strict";

		$(this).mouseup(function(event){
            if (!$(this).focus()) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
			$('.colorPickerPane').remove();

			var colorPickerHTML=
				'<div class="colorPickerPane">\
                    <div class="HueSaturationWrapper">\
                        <img  data-toggle="tooltip" data-placement="bottom" title="'+getScrollTitle()+'" class="HueSaturation thumbnail" src="'+$('.js-estheme-panel').data('palette')+'"/>\
				</div\
				>\
				<input class="Lightness  vertical" type="range" name="points" min="0" max="100" orient="vertical"/>\
			</div>';

			var $colorPicker = $(colorPickerHTML);

			var colorPicker = $colorPicker[0];
			colorPicker.$input = $(this);
			colorPicker.input = this;
			colorPicker.$Lightness = $('.Lightness',colorPicker);
			colorPicker.Lightness = colorPicker.$Lightness[0];		
			colorPicker.Hue = {value:255};		
			colorPicker.Saturation = {value:255};
			colorPicker.$HueSaturation = $('.HueSaturation',colorPicker);
			colorPicker.HueSaturation = colorPicker.$HueSaturation[0];
			colorPicker.HueSaturation.colorPicker = colorPicker;			
			colorPicker.$input.attr('spellcheck',false);

			var inputHex = colorPicker.input.value;
			if(isValidHexColor(inputHex)){
				var rgb = parseRGB(inputHex);
				var HueSaturationLightness = rgb2hsl(rgb[0],rgb[1],rgb[2]);
				colorPicker.Lightness.value = HueSaturationLightness[2];
			}
			else{
				colorPicker.Lightness.value = 50;
			}


			colorPicker.setHueSaturationColors = function(){
				var lightness = this.Lightness.value;
				$('.HueSaturationWrapper').css('background-color',(lightness>50) ? "white" : "black");
				var opacity = 1-Math.abs(lightness-50)/50;
				$('.HueSaturation').css('opacity',opacity);		
			}

			colorPicker.toInput = function(){
				var colorPicker = this;				
				var Lightness  = parseInt(colorPicker.Lightness.value)
				var Hue = parseInt(colorPicker.Hue.value);
				var Saturation = parseInt(colorPicker.Saturation.value);				
				var rgb = hsl2rgb(Hue,Saturation,Lightness);
				var red   = rgb[0];
				var green = rgb[1];
				var blue  = rgb[2];				
				var cssColor  = '#'+(0x1000000 + red*0x10000 + green*0x100 + blue).toString(16).substr(1);			
				colorPicker.input.style.backgroundColor = cssColor;			
				colorPicker.input.value = cssColor;

				ls.hook.run('color_picker_after_move', [cssColor, colorPicker]);
			}

			colorPicker.$HueSaturation.load(function(){
				this.width = $(this).width();
				this.height = $(this).height();
				this.offset = $(this).offset();			
			});

			colorPicker.$Lightness.on('mousemove change',function(){
				colorPicker.setHueSaturationColors();
				colorPicker.toInput();
			});

			//colorPicker.$input.on('blur', function(event){
                //var cssColor = jQuery.Color( $(this).val()).toHexString();
                //ls.hook.run('color_picker_after_select', [cssColor, colorPicker]);
                //colorPicker.$input.css({
					//'backgroundColor': cssColor
                //});
                //colorPicker.$input.value = cssColor;
                //$colorPicker.remove();
                //event.preventDefault();
                //event.stopPropagation();
			//});

			colorPicker.$input.on('change', function(event){

				var cssColor = jQuery.Color( $(this).val()).toHexString();
				ls.hook.run('color_picker_after_select', [cssColor, colorPicker]);
				colorPicker.$input.css({
					'backgroundColor': cssColor
				});
				colorPicker.$input.value = cssColor;
				$colorPicker.remove();
                event.preventDefault();
                event.stopPropagation();
			});

			colorPicker.$HueSaturation.bind('mousewheel DOMMouseScroll', function(event){
				var colorPicker = this.colorPicker;
				console.log('hello');
				event.preventDefault();
				event.stopPropagation();
				var isWheelUp = event.originalEvent.wheelDelta > 0 || event.originalEvent.detail < 0;

				if (isWheelUp) {				
					colorPicker.Lightness.value = colorPicker.Lightness.value*1+5;
				}
				else {
					colorPicker.Lightness.value = colorPicker.Lightness.value-5;
				}
				colorPicker.setHueSaturationColors();
				colorPicker.toInput();
			});

			colorPicker.$HueSaturation.on('dragstart',function(e) {
				e.preventDefault(); 
			});

			colorPicker.$HueSaturation.mousemove(function(e){
				var colorPicker = this.colorPicker;

				var xPosition =     (e.pageX - (this.offset.left+1))/this.width;
				var yPosition = 1 - (e.pageY -  this.offset.top    )/this.height;

				colorPicker.Hue.value = Math.floor(xPosition*360);
				colorPicker.Saturation.value = Math.floor(yPosition*100);

				colorPicker.toInput();
			});

			colorPicker.$HueSaturation.mouseup(function(event){
				ls.hook.run('color_picker_after_select', [colorPicker.input.value, colorPicker]);
                $colorPicker.remove();
                event.preventDefault();
                event.stopPropagation();
            });
			
			colorPicker.$Lightness.mouseup(function(event){
				event.stopPropagation();
			});

			var inputOffset = colorPicker.$input.parent().offset();
			var inputHeight = colorPicker.$input.outerHeight(true);
			$colorPicker.css({top:inputOffset.top+inputHeight,left:inputOffset.left})
			$('body').append($colorPicker);
			$('.colorPickerPane img').tooltip();
			colorPicker.setHueSaturationColors();
		});

		function getScrollTitle() {
			return ls.lang.get('plugin.br.scroll_to_intensive');
		}

		function isValidHexColor(colorStr){
			return colorStr.match(/^\#[\dA-Fa-f]{6}$/);
		}

		function parseRGB(colorStr){
			var red   = parseInt(colorStr.substr(1,2),16);
			var green = parseInt(colorStr.substr(3,2),16);
			var blue  = parseInt(colorStr.substr(5,2),16);
			return [red,green,blue];
		}

		function hue2rgb(p, q, t){
			if(t < 0) t += 1;
			if(t > 1) t -= 1;
			if(t < 1/6) return p + (q - p) * 6 * t;
			if(t < 1/2) return q;
			if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
			return p;
		}

		function hsl2rgb(h, s, l){
			h/=360;s/=100;l/=100;
			var r, g, b;

			if(s == 0){
				r = g = b = l;
			}else{
				var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
				var p = 2 * l - q;
				r = hue2rgb(p, q, h + 1/3);
				g = hue2rgb(p, q, h);
				b = hue2rgb(p, q, h - 1/3);
			}
			return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
		}

		function rgb2hsl(r, g, b){
			r /= 255, g /= 255, b /= 255;			
			var max = Math.max(r, g, b), min = Math.min(r, g, b);
			var h, s, l = (max + min) / 2;

			if(max == min){
				h = s = 0;
			}else{
				var d = max - min;
				s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
				switch(max){
					case r: h = (g - b) / d + (g < b ? 6 : 0); break;
					case g: h = (b - r) / d + 2; break;
					case b: h = (r - g) / d + 4; break;
				}
				h /= 6;
			}
			return [Math.round(h*360), Math.round(s*100), Math.round(l*100)];
		}		
	});
};
