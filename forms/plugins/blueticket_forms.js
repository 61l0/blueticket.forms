/** blueticket® blueticketFORMS v.1.0; 06/2014 */
/** object */
var blueticket_forms = {
	config: function(key) {
		if (blueticket_forms_config[key] !== undefined) {
			return blueticket_forms_config[key];
		} else {
			return key;
		}
	},
	lang: function(key) {
		if (blueticket_forms_config['lang'][key] !== undefined) {
			return blueticket_forms_config['lang'][key];
		} else {
			return key;
		}
	},
	current_task: null,
	request: function(container, data, success_callback) {
		//jQuery(container).trigger("blueticket_formsbeforerequest");
		jQuery(document).trigger("blueticket_formsbeforerequest", [container, data]);
		jQuery.ajax({
			type: "post",
			url: blueticket_forms.config('url'),
			beforeSend: function() {
				blueticket_forms.current_task = data.task;
				blueticket_forms.show_progress(container);
			},
			data: {
				"blueticket_forms": data
			},
			success: function(response) {
				jQuery(container).html(response);
				//jQuery(container).trigger("blueticket_formsafterrequest");
				jQuery(document).trigger("blueticket_formsafterrequest", [container, data]);
				if (success_callback) {
					success_callback(container);
				}
			},
			complete: function() {
				blueticket_forms.hide_progress(container);
			},
			dataType: "html",
			cache: false
		});
	},
	new_window_request: function(container, data) {
		var html = blueticket_forms.data2form(data);
		var w = window.open("", "blueticket_forms_request", "scrollbars,resizable,height=400,width=600");
		w.document.open();
		w.document.write(html);
		w.document.close();
		jQuery(w.document.body).find('form').submit();
	},
	data2form: function(data) {
		var html = '<!DOCTYPE HTML><html><head><meta http-equiv="content-type" content="text/html;charset=utf-8" /></head><body>';
		html += '<form method="post" action="' + blueticket_forms.config('url') + '">';
		jQuery.map(data, function(value, key) {
			if (!jQuery.isPlainObject(value)) {
				html += '<input type="hidden" name="blueticket_forms[' + key + ']" value="' + value + '" />';
			}
		});
		html += '</form></body></html>';
		return html;
	},
	unique_check: function(container, data, success_callback) {
		data.unique = {};
		data.task = "unique";
		if (jQuery(container).find('.blueticket_forms-input[data-unique]').size()) {
			jQuery(container).find('.blueticket_forms-input[data-unique]').each(function(index, element) {
				data.unique[jQuery(element).attr('name')] = jQuery(element).val();
			});
			jQuery.ajax({
				type: "post",
				url: blueticket_forms.config('url'),
				beforeSend: function() {
					blueticket_forms.show_progress(container);
				},
				data: {
					"blueticket_forms": data
				},
				dataType: "json",
				success: function(response) {
					//jQuery(container).find(".blueticket_forms-data[name=key]:first").val(response.key);
					if (response.error) {
						jQuery(container).find(response.error.selector).addClass('validation-error');
						//alert(blueticket_forms.lang('unique_error'));
						blueticket_forms.show_message(container, blueticket_forms.lang('unique_error'), 'error');
						return false;
					}
					if (success_callback) {
						success_callback(container);
					}
				},
				complete: function() {
					blueticket_forms.hide_progress(container);
				},
				cache: false
			});
		} else {
			if (success_callback) {
				success_callback(container);
			}
		}
	},
	show_progress: function(container) {
		jQuery(container).closest(".blueticket_forms").find(".blueticket_forms-overlay").width(jQuery(container).closest(".blueticket_forms-container").width()).stop(true, true).fadeTo(300, 0.6);
	},
	hide_progress: function(container) {
		jQuery(container).closest(".blueticket_forms").find(".blueticket_forms-overlay").stop(true, true).css("display", "none");
	},
	get_container: function(element) {
		return jQuery(element).closest(".blueticket_forms-ajax");
	},
	list_data: function(container, element) {
		var data = {};
		blueticket_forms.validation_error = 0;
		blueticket_forms.save_editor_content(container);
		jQuery(container).find(".blueticket_forms-data").each(function() {
			if (blueticket_forms.check_container(this, container)) {
				data[jQuery(this).attr("name")] = blueticket_forms.prepare_val(this);
			}
		});
        if (element && jQuery.isPlainObject(element)) {
			jQuery.extend(data, element);
		} else if (element) {
			jQuery.extend(data, jQuery(element).data());
		}
		data.postdata = {};
        var validation = data.task == 'save' ? true : false;
        if(validation){
            jQuery(document).trigger("blueticket_formsbeforevalidate",[container]);
        }
		jQuery(container).find('.blueticket_forms-input:not([type="checkbox"],[type="radio"],[disabled])').each(function() {
			if (blueticket_forms.check_container(this, container)) {
				var val = blueticket_forms.prepare_val(this);
				data.postdata[jQuery(this).attr("name")] = val;
				var required = jQuery(this).data('required');
				var pattern = jQuery(this).data('pattern');
				if (validation && required && !blueticket_forms.validation_required(val, required)) {
					blueticket_forms.validation_error = 1;
					jQuery(this).addClass('validation-error');
				} else if (validation && pattern && !blueticket_forms.validation_pattern(val, pattern)) {
					blueticket_forms.validation_error = 1;
					jQuery(this).addClass('validation-error');
				} else {
					jQuery(this).removeClass('validation-error');
				}
			}
		});
		jQuery(container).find('.blueticket_forms-input[data-type="checkboxes"]:not([disabled])').each(function() {
			if (data.postdata[jQuery(this).attr("name")] === undefined) {
				data.postdata[jQuery(this).attr("name")] = '';
			}
			if (blueticket_forms.check_container(this, container) && jQuery(this).prop('checked')) {
				if (!data.postdata[jQuery(this).attr("name")]) {
					data.postdata[jQuery(this).attr("name")] = blueticket_forms.prepare_val(this);
				} else {
					data.postdata[jQuery(this).attr("name")] += "," + blueticket_forms.prepare_val(this);
				}
			}
		});
		jQuery(container).find('.blueticket_forms-input[type="radio"]:not([disabled])').each(function() {
			if (blueticket_forms.check_container(this, container) && jQuery(this).prop('checked')) {
				data.postdata[jQuery(this).attr("name")] = blueticket_forms.prepare_val(this);
			}
		});
		jQuery(container).find('.blueticket_forms-input[data-type="bool"]:not([disabled])').each(function() {
			if (blueticket_forms.check_container(this, container)) {
				data.postdata[jQuery(this).attr("name")] = jQuery(this).prop('checked') ? 1 : 0;
			}
		});
		jQuery(container).find(".blueticket_forms-searchdata.blueticket_forms-search-active").each(function() {
			if (blueticket_forms.check_container(this, container)) {
				data[jQuery(this).attr("name")] = blueticket_forms.prepare_val(this);
			}
		});
		
        if(validation){
            jQuery(document).trigger("blueticket_formsaftervalidate",[container,data]);
        }
		return data;
	},
	list_controls_data: function(container, element) {
		var data = {};
		jQuery(container).find(".blueticket_forms-data").each(function() {
			if (blueticket_forms.check_container(this, container)) {
				data[jQuery(this).attr("name")] = blueticket_forms.prepare_val(this);
			}
		});
		return data;
	},
	check_container: function(element, container) {
		return jQuery(element).closest(".blueticket_forms-ajax").attr('id') == jQuery(container).attr('id') ? true : false;
	},
	save_editor_content: function(container) {
		if (jQuery(container).find('.blueticket_forms-texteditor').size()) {
			if (typeof(tinyMCE) != 'undefined') {
				tinyMCE.triggerSave();
/*for (instance in tinyMCE.editors) {
					if (tinyMCE.editors[instance] && isNaN(instance * 1)) {
						if (jQuery('#' + instance).size()) {
							tinyMCE.editors[instance].save();
						} else {
							//tinyMCE.editors[instance].destroy();
							//tinyMCE.editors[instance] = null;
						}
					}
				}*/
			}
			if (typeof(CKEDITOR) != 'undefined') {
				for (instance in CKEDITOR.instances) {
					if (jQuery('#' + instance).size()) {
						CKEDITOR.instances[instance].updateElement();
					}
/*else {
						CKEDITOR.instances[instance].destroy();
					}*/
				}
			}
		}
	},
	prepare_val: function(element) {
		switch (jQuery(element).data("type")) {
		case 'datetime':
		case 'timestamp':
		case 'date':
		case 'time':
			if (jQuery(element).val()) {
				var d = jQuery(element).datepicker("getDate");
				return d ? Math.round(d.getTime() / 1000) - d.getTimezoneOffset() * 60 : '';
			} else
			return '';
			break;
		default:
			return jQuery.trim(jQuery(element).val());
			break;
		}
	},
	change_filter: function(type, container, fieldname) {
		jQuery(container).find(".blueticket_forms-searchdata").hide().removeClass("blueticket_forms-search-active");
		var name_selector = '';
		switch (type) {
		case 'datetime':
		case 'timestamp':
		case 'date':
		case 'time':
			var fieldtype = 'date';
			break;
		case 'bool':
			var fieldtype = 'bool';
			break;
		case 'select':
		case 'multiselect':
		case 'radio':
		case 'checkboxes':
			var fieldtype = 'dropdown';
			name_selector = '[data-fieldname="' + fieldname + '"]';
			break;
		default:
			var fieldtype = 'default';
			break;
		}
		jQuery(container).find('.blueticket_forms-searchdata[data-fieldtype="' + fieldtype + '"]' + name_selector).show().addClass("blueticket_forms-search-active");
		if (fieldtype == 'date') {
			blueticket_forms.init_datepicker_range(type, container);
		}
	},
	init_datepicker_range: function(type, container) {
		jQuery(container).find('.blueticket_forms-datepicker-from.hasDatepicker,.blueticket_forms-datepicker-to.hasDatepicker').datepicker("destroy");
		var datepicker_config = {
			changeMonth: true,
			changeYear: true,
			showSecond: true,
			dateFormat: blueticket_forms.config('date_format'),
			timeFormat: blueticket_forms.config('time_format')
		};
		switch (type) {
		case 'datetime':
		case 'timestamp':
			// to
			datepicker_config.onClose = function(selectedDate) {
				jQuery(container).find('.blueticket_forms-datepicker-from').datetimepicker("option", "maxDate", selectedDate);
			}
			datepicker_config.onSelect = datepicker_config.onClose;
			jQuery(container).find('.blueticket_forms-datepicker-to').datetimepicker(datepicker_config);
			// from
			datepicker_config.maxDate = jQuery(container).find('.blueticket_forms-datepicker-to').val();
			datepicker_config.onClose = function(selectedDate) {
				jQuery(container).find('.blueticket_forms-datepicker-to').datetimepicker("option", "minDate", selectedDate);
			}
			datepicker_config.onSelect = datepicker_config.onClose;
			jQuery(container).find('.blueticket_forms-datepicker-from').datetimepicker(datepicker_config);
			break;
		case 'date':
			// to
			datepicker_config.onClose = function(selectedDate) {
				jQuery(container).find('.blueticket_forms-datepicker-from').datepicker("option", "maxDate", selectedDate);
			}
			datepicker_config.onSelect = datepicker_config.onClose;
			jQuery(container).find('.blueticket_forms-datepicker-to').datepicker(datepicker_config);
			// from
			datepicker_config.maxDate = jQuery(container).find('.blueticket_forms-datepicker-to').val();
			datepicker_config.onClose = function(selectedDate) {
				jQuery(container).find('.blueticket_forms-datepicker-to').datepicker("option", "minDate", selectedDate);
			}
			datepicker_config.onSelect = datepicker_config.onClose;
			jQuery(container).find('.blueticket_forms-datepicker-from').datepicker(datepicker_config);
			break;
		case 'time':
			jQuery(container).find('.blueticket_forms-datepicker-from,.blueticket_forms-datepicker-to').timepicker(datepicker_config);
			break;
		}
		jQuery(".ui-datepicker").css("font-size", "0.9em"); // reset ui size
	},
	init_datepicker: function(container) {
		if (jQuery(container).find(".blueticket_forms-datepicker").size()) {
			jQuery(container).find(".blueticket_forms-datepicker").each(function() {
				var element = jQuery(this);
				var format_id = jQuery(this).data("type");
				switch (format_id) {
				case 'datetime':
				case 'timestamp':
					element.datetimepicker({
						showSecond: true,
						timeFormat: blueticket_forms.config('time_format'),
						dateFormat: blueticket_forms.config('date_format'),
						firstDay: blueticket_forms.config('date_first_day'),
						changeMonth: true,
						changeYear: true
					});
					break;
				case 'time':
					element.timepicker({
						showSecond: true,
						dateFormat: blueticket_forms.config('date_format'),
						timeFormat: blueticket_forms.config('time_format')
					});
					break;
				case 'date':
				default:
					element.datepicker({
						dateFormat: blueticket_forms.config('date_format'),
						firstDay: blueticket_forms.config('date_first_day'),
						changeMonth: true,
						changeYear: true,
						onClose: function(selectedDate) {
							var range_start = element.data("rangestart");
							var range_end = element.data("rangeend");
							if (range_start) {
								var target = element.closest(".blueticket_forms-ajax").find('input[name="' + range_start + '"]');
								jQuery(target).datepicker("option", "maxDate", selectedDate);
							}
							if (range_end) {
								var target = element.closest(".blueticket_forms-ajax").find('input[name="' + range_end + '"]');
								jQuery(target).datepicker("option", "minDate", selectedDate);
							}
						}
					});
					var range_start = element.data("rangestart");
					var range_end = element.data("rangeend");
					if (range_start && element.val()) {
						var target = element.closest(".blueticket_forms-ajax").find('input[name="' + range_start + '"]');
						jQuery(target).datepicker("option", "maxDate", element.val());
					}
					if (range_end && element.val()) {
						var target = element.closest(".blueticket_forms-ajax").find('input[name="' + range_end + '"]');
						jQuery(target).datepicker("option", "minDate", element.val());
					}
				}
			});
		}
	},
	init_texteditor: function(container) {
		var elements = jQuery(container).find(".blueticket_forms-texteditor:not(.editor-loaded)");
		if (jQuery(elements).size()) {
			if (blueticket_forms.config('editor_url') || blueticket_forms.config('force_editor')) {
				jQuery(elements).addClass("editor-loaded").addClass("editor-instance");
				if (blueticket_forms.config('editor_init_url')) {
					window.setTimeout(function() {
						jQuery.ajax({
							url: blueticket_forms.config('editor_init_url'),
							type: "get",
							dataType: "script",
							success: function(js) {
								jQuery(".blueticket_forms-overlay").stop(true, true).css("display", "none");
								jQuery(elements).removeClass("editor-instance");
							},
							cache: true
						});
					}, 300);
				} else {
					if (typeof(tinyMCE) != 'undefined') {
						tinyMCE.init({
							mode: "textareas",
							editor_selector: "editor-instance",
							height: "250"
						});
					} else if (typeof(CKEDITOR) != 'undefined') {
						CKEDITOR.replaceAll('editor-instance');
					}
					jQuery(elements).removeClass("editor-instance");
				}
			}
		}
	},
	upload_file: function(element, data, container) {
		var upl_container = jQuery(element).closest('.blueticket_forms-upload-container');
		data.field = jQuery(element).data("field");
		data.oldfile = jQuery(upl_container).find('.blueticket_forms-input').val();
		data.task = "upload";
		data.type = jQuery(element).data("type");
		var ext = blueticket_forms.get_extension(jQuery(element).val());
		if (data.type == 'image') {
			switch (ext.toLowerCase()) {
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'png':
				break;
			default:
				blueticket_forms.show_message(container, blueticket_forms.lang('image_type_error'), 'error');
				jQuery(element).val('');
				return false;
				break;
			}
		}
		jQuery(document).trigger("blueticket_formsbeforeupload", [container, data]);
		blueticket_forms.show_progress(container);
		jQuery.ajaxFileUpload({
			secureuri: false,
			fileElementId: jQuery(element).attr('id'),
			data: {
				"blueticket_forms": data
			},
			url: blueticket_forms.config('url'),
			success: function(out) {
				blueticket_forms.hide_progress(container);
				jQuery(upl_container).replaceWith(out);
				jQuery(document).trigger("blueticket_formsafterupload", [container, data]);
				var crop_img = jQuery(out).find("img.blueticket_forms-crop");
				if (jQuery(crop_img).size()) {
					blueticket_forms.show_crop_window(crop_img, container);
				}
			},
			error: function() {
				blueticket_forms.hide_progress(container);
				blueticket_forms.show_message(container, blueticket_forms.lang('undefined_error'), 'error');
			}
		});
	},
	show_crop_window: function(crop_img, container) {
		var upl_container = jQuery(container).find('img.blueticket_forms-crop').closest('.blueticket_forms-upload-container');
		jQuery(crop_img).dialog({
			resizable: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			closeOnEscape: false,
			buttons: {
				"OK": function() {
					var data = blueticket_forms.list_data(container,{"task":"crop_image"});
					jQuery(upl_container).find('.xrud-crop-data').each(function() {
						data[jQuery(this).attr('name')] = jQuery(this).val();
					});
					//data.task = "crop_image";
					jQuery(document).trigger("blueticket_formsbeforeecrop", [container, data]);
					blueticket_forms.show_progress(container);
					jQuery.ajax({
						data: {
							"blueticket_forms": data
						},
						success: function(out) {
							blueticket_forms.hide_progress(container);
							jQuery(upl_container).replaceWith(out);
							jQuery(document).trigger("blueticket_formsaftercrop", [container, data]);
						},
						error: function() {
							blueticket_forms.hide_progress(container);
							blueticket_forms.show_message(container, blueticket_forms.lang('undefined_error'), 'error');
						},
						type: "post",
						url: blueticket_forms.config('url'),
						dataType: "html",
						cache: false,
					});
					jQuery(this).dialog("destroy");
					jQuery(".blueticket_forms-crop").remove();
				}
			},
			close: function(event, ui) {
				var data = blueticket_forms.list_data(container,{"task":"crop_image"});
				jQuery(upl_container).find('.xrud-crop-data').each(function() {
					data[jQuery(this).attr('name')] = jQuery(this).val();
				});
				//data.task = "crop_image";
				data.w = 0;
				data.h = 0;
				blueticket_forms.show_progress(container);
				jQuery.ajax({
					data: {
						"blueticket_forms": data
					},
					success: function(out) {
						blueticket_forms.hide_progress(container);
						jQuery(upl_container).replaceWith(out);
					},
					error: function() {
						blueticket_forms.hide_progress(container);
						blueticket_forms.show_message(container, blueticket_forms.lang('undefined_error'), 'error');
					},
					type: "post",
					url: blueticket_forms.config('url'),
					dataType: "html",
					cache: false,
				});
				jQuery(this).dialog("destroy");
				jQuery(".blueticket_forms-crop").remove();
			},
			open: function(event, ui) {
				blueticket_forms.load_image(crop_img.attr('src'), function(imageObject) {
					var t_w = parseInt(jQuery(crop_img).data('width'));
					var t_h = parseInt(jQuery(crop_img).data('height'));
					var ratio = parseFloat(jQuery(crop_img).data('ratio'));
					var cropset = {};
					cropset.boxWidth = t_w;
					cropset.boxHeight = t_h;
					if (t_h > 500) {
						cropset.boxHeight = 500;
						cropset.boxWidth = Math.round(t_w * 500 / t_h)
					}
					if (cropset.boxWidth > 550) {
						cropset.boxWidth = 550;
						cropset.boxHeight = Math.round(t_h * 550 / t_w);
					}
					/*jQuery(crop_img).css({
						"width": cropset.boxWidth,
						"height": cropset.boxHeight,
						"min-height": cropset.boxHeight
					});*/
					var left = Math.round((jQuery(window).width() - jQuery(".ui-dialog.ui-widget").width()) / 2);
					var top = Math.round((jQuery(window).height() - jQuery(".ui-dialog.ui-widget").height()) / 2);
					jQuery(".ui-dialog.ui-widget").css({
						"position": "fixed",
						"left": left + "px",
						"top": top + "px"
					});
					cropset.minSize = [50, 50];
					if (ratio) {
						cropset.aspectRatio = ratio;
					}
					cropset.onChange = blueticket_forms.get_coordinates;
					cropset.keySupport = false;
					cropset.trueSize = [t_w, t_h];
					var w1 = t_w / 4;
					var h1 = t_h / 4;
					var w2 = w1 * 3;
					var h2 = h1 * 3;
					cropset.setSelect = [w1, h1, w2, h2];
					cropset.allowSelect = false;
					jQuery(".ui-dialog img.blueticket_forms-crop").Jcrop(cropset);
				});
			}
		});
	},
	load_image: function(url, callback) {
		var imageObject = new Image();
		imageObject.src = url;
		if (imageObject.complete) {
			if (callback) {
				callback(imageObject);
			}
		} else {
			jQuery(document).trigger("startload");
			imageObject.onload = function() {
				jQuery(document).trigger("stopload");
				if (callback) {
					callback(imageObject);
				}
			}
			imageObject.onerror = function() {
				jQuery(document).trigger("stopload");
				if (callback) {
					callback(false);
				}
			}
		}
	},
	remove_file: function(element, data, container) {
		var upl_container = jQuery(element).closest('.blueticket_forms-upload-container');
		data.field = jQuery(element).data("field");
		data.file = jQuery(upl_container).find('.blueticket_forms-input').val();
		data.task = "remove_upload";
		blueticket_forms.show_progress(container);
		jQuery.ajax({
			data: {
				"blueticket_forms": data
			},
			success: function(data) {
				blueticket_forms.hide_progress(container);
				jQuery(upl_container).replaceWith(data);
			},
			type: "post",
			url: blueticket_forms.config('url'),
			dataType: "html",
			cache: false,
			error: function() {
				blueticket_forms.hide_progress(container);
				blueticket_forms.show_message(container, blueticket_forms.lang('undefined_error'), 'error');
			}
		});
	},
	get_coordinates: function(c) {
		jQuery(".blueticket_forms").find("input.xrud-crop-data[name=x]").val(Math.round(c.x));
		jQuery(".blueticket_forms").find("input.xrud-crop-data[name=y]").val(Math.round(c.y));
		jQuery(".blueticket_forms").find("input.xrud-crop-data[name=x2]").val(Math.round(c.x2));
		jQuery(".blueticket_forms").find("input.xrud-crop-data[name=y2]").val(Math.round(c.y2));
		jQuery(".blueticket_forms").find("input.xrud-crop-data[name=w]").val(Math.round(c.w));
		jQuery(".blueticket_forms").find("input.xrud-crop-data[name=h]").val(Math.round(c.h));
	},
	validation_required: function(val, length) {
		return jQuery.trim(val).length >= length;
	},
	validation_pattern: function(val, pattern) {
		if (val === '') {
			return true;
		}
		switch (pattern) {
		case 'email':
			reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			return reg.test(jQuery.trim(val));
			break;
		case 'alpha':
			reg = /^([a-z])+$/i;
			return reg.test(jQuery.trim(val));
			break;
		case 'alpha_numeric':
			reg = /^([a-z0-9])+$/i;
			return reg.test(jQuery.trim(val));
			break;
		case 'alpha_dash':
			reg = /^([-a-z0-9_-])+$/i;
			return reg.test(jQuery.trim(val));
			break;
		case 'numeric':
			reg = /^[\-+]?[0-9]*\.?[0-9]+$/;
			return reg.test(jQuery.trim(val));
			break;
		case 'integer':
			reg = /^[\-+]?[0-9]+$/;
			return reg.test(jQuery.trim(val));
			break;
		case 'decimal':
			reg = /^[\-+]?[0-9]+\.[0-9]+$/;
			return reg.test(jQuery.trim(val));
			break;
		case 'point':
			reg = /^[\-+]?[0-9]+\.{0,1}[0-9]*\,[\-+]?[0-9]+\.{0,1}[0-9]*$/;
			return reg.test(jQuery.trim(val));
			break;
		case 'natural':
			reg = /^[0-9]+$/;
			return reg.test(jQuery.trim(val));
			break;
		default:
			reg = new RegExp(pattern);
			return reg.test(jQuery.trim(val));
			break;
		}
		return true;
	},
	pattern_callback: function(e, element) {
		var pattern = jQuery(element).data('pattern');
		if (pattern) {
			var code = e.which;
			if (code < 32 || e.ctrlKey || e.altKey) return true;
			var val = String.fromCharCode(code);
			switch (pattern) {
			case 'alpha':
				reg = /^([a-z])+$/i;
				return reg.test(val);
				break;
			case 'alpha_numeric':
				reg = /^([a-z0-9])+$/i;
				return reg.test(val);
				break;
			case 'alpha_dash':
				reg = /^([-a-z0-9_-])+$/i;
				return reg.test(val);
				break;
			case 'numeric':
			case 'integer':
			case 'decimal':
				reg = /^[0-9\.\-+]+$/;
				return reg.test(val);
				break;
			case 'natural':
				reg = /^[0-9]+$/;
				return reg.test(val);
				break;
			case 'point':
				reg = /^[0-9\.\,\-+]+$/;
				return reg.test(val);
				break;
			}
		}
		return true;
	},
	validation_error: false,
	get_extension: function(filename) {
		var parts = filename.split('.');
		return parts[parts.length - 1];
	},
	check_fixed_buttons: function() {
		jQuery(".blueticket_forms").each(function() {
			if (jQuery(this).find(".blueticket_forms-list:first").width() > jQuery(this).find(".blueticket_forms-list-container:first").width()) {
				var w = jQuery(this).find(".blueticket_forms-actions:not(.blueticket_forms-fix):first").width();
				jQuery(this).find(".blueticket_forms-actions:not(.blueticket_forms-fix):first").css({
					"width": w,
					"min-width": w
				});
				jQuery(this).find(".blueticket_forms-list:first .blueticket_forms-actions.blueticket_forms-fix:not(.blueticket_forms-actions-fixed)").addClass("blueticket_forms-actions-fixed");
			} else
			jQuery(this).find(".blueticket_forms-list:first .blueticket_forms-actions").removeClass("blueticket_forms-actions-fixed");
		});
	},
	block_query: {},
	depend_init: function(container) {
		jQuery(container).off('change.depend');
		var dependencies = {};
		jQuery(container).find('.blueticket_forms-input[data-depend]').each(function() {
			var container = blueticket_forms.get_container(this);
			var data = blueticket_forms.list_controls_data(container, this);
			var depend_on = jQuery(this).data("depend");
			data.task = "depend";
			data.name = jQuery(this).attr('name');
			data.value = jQuery(this).val();
			jQuery(container).on('change.depend', '.blueticket_forms-input[name="' + depend_on + '"]', function() {
				if (blueticket_forms.check_container(this, container)) {
					data.dependval = jQuery(this).val();
					blueticket_forms.depend_query(data, depend_on, container);
				}
			});
			if (depend_on) dependencies[depend_on] = depend_on;
		});
		jQuery.map(dependencies, function(val, key) {
			window.setTimeout(function() {
				jQuery(container).find('.blueticket_forms-input[name="' + val + '"]:not([data-depend])').trigger('change.depend');
			}, 100);
		});
	},
	depend_query: function(data, depend_on, container) {
		if (blueticket_forms.block_query[data.name + depend_on]) {
			return;
		}
		blueticket_forms.block_query[data.name + depend_on] = 1;
		jQuery(document).trigger("blueticket_formsbeforedepend", [container, data]);
		jQuery.ajax({
			data: {
				"blueticket_forms": data
			},
			type: 'post',
			url: blueticket_forms.config('url'),
			success: function(input) {
				jQuery(container).find('.blueticket_forms-input[name="' + data.name + '"]').replaceWith(input);
				window.setTimeout(function() {
					jQuery(document).trigger("blueticket_formsafterdepend", [container, data]);
					jQuery(container).find('.blueticket_forms-input[name="' + data.name + '"]').trigger('change.depend');
					blueticket_forms.block_query[data.name + depend_on] = 0;
				}, 50);
			},
			cache: false
		});
	},
	parse_latlng: function(string) {
		var coords = string.split(',');
		if (coords.length != 2) {
			return null;
		}
		var LatLng = new google.maps.LatLng(parseFloat(coords[0]), parseFloat(coords[1]));
		return LatLng;
	},
	create_map: function(selector, center, zoom, type) {
		var params = {
			zoom: zoom,
			center: center,
			mapTypeId: google.maps.MapTypeId[type]
		}
		var map = new google.maps.Map(jQuery(selector)[0], params);
		return map;
	},
	place_marker: function(map, point, draggable, infowindow, point_field) {
		var marker = new google.maps.Marker({
			position: point,
			map: map,
			animation: google.maps.Animation.DROP,
			draggable: (draggable ? true : false)
		});
		if (infowindow) {
			google.maps.event.addListener(marker, 'click', function() {
				var currentmarker = this;
				var infoWindow = new google.maps.InfoWindow({
					maxWidth: 320
				});
				infoWindow.setContent('<p class="blueticket_forms-infowinow">' + infowindow + '</p>');
				infoWindow.open(map, currentmarker);
			});
		}
		if (draggable && jQuery(point_field).size()) {
			google.maps.event.addListener(marker, 'dragend', function() {
				jQuery(point_field).val(this.getPosition().lat() + ',' + this.getPosition().lng());
			});
			google.maps.event.addListener(map, 'click', function(event) {
				//console.log(oMap);
				marker.setPosition(event.latLng);
				jQuery(point_field).val(marker.getPosition().lat() + ',' + marker.getPosition().lng());
			});
		}
		return marker;
	},
	move_marker: function(map, marker, point, dragable, infowindow) {
		if (marker) {
			marker.setPosition(point);
		} else {
			this.place_marker(map, point, dragable, infowindow)
		}
		map.setCenter(point);
		return marker;
	},
	find_point: function(address, callback) {
		return this.geocode({
			address: address
		}, callback);
	},
	find_address: function(point, callback) {
		return this.geocode({
			latLng: point
		}, callback);
	},
	geocode: function(geocoderRequest, callback, callback_single) {
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode(geocoderRequest, function(results, status) {
			//console.log(results);
			var output = {};
			if (status == google.maps.GeocoderStatus.OK) {
				for (var i = 0; i < results.length; i++) {
					if (results[i].formatted_address) {
						//console.log(results[i]);
						output[i] = {};
						output[i].lat = results[i].geometry.location.lat();
						output[i].lng = results[i].geometry.location.lng();
						output[i].address = results[i].formatted_address;
						if (callback_single) {
							return callback_single(output[i]);
						}
					}
				}
				if (callback) {
					callback(output);
				}
			}
		});
	},
	map_instances: [],
	marker_instances: [],
	map_init: function(container) {
		blueticket_forms.map_instances = [];
		jQuery(container).find('.blueticket_forms-map').each(function() {
			var cont = this;
			var point_field = jQuery(cont).parent().children('.blueticket_forms-input');
			var search_field = jQuery(cont).parent().children('.blueticket_forms-map-search');
			var point = blueticket_forms.parse_latlng(jQuery(point_field).val());
			var map = blueticket_forms.create_map(cont, point, jQuery(cont).data('zoom'), 'ROADMAP');
			var marker = blueticket_forms.place_marker(map, point, jQuery(cont).data('draggable'), jQuery(cont).data('text'), point_field);
			jQuery(point_field).on("keyup", function() {
				var point = blueticket_forms.parse_latlng(jQuery(point_field).val());
				blueticket_forms.move_marker(map, marker, point, jQuery(cont).data('draggable'), jQuery(cont).data('text'));
				return false;
			});
			if (jQuery(search_field).size()) {
				jQuery(search_field).on("keyup", function() {
					var value = jQuery.trim(jQuery(search_field).val());
					if (value) {
						blueticket_forms.find_point(value, function(results) {
							blueticket_forms.map_dropdown(search_field, results, map, marker, point_field, cont);
						});
					}
					return false;
				});
			}
			blueticket_forms.map_instances.push(map);
			blueticket_forms.marker_instances.push(marker);
		});
	},
	map_dropdown: function(element, results, map, marker, point_field, cont) {
		var m_left = jQuery(element).outerWidth();
		var m_top = jQuery(element).outerHeight();
		var pos = jQuery(element).offset();
		jQuery(element).prev(".blueticket_forms-map-dropdown").remove();
		if (results) {
			var list = '<ul class="blueticket_forms-map-dropdown">';
			jQuery.map(results, function(value) {
				list += '<li data-val="' + value.lat + ',' + value.lng + '">' + value.address + '</li>';
			});
			list += '</ul>';
			jQuery(element).before(list);
			jQuery(element).prev(".blueticket_forms-map-dropdown").offset(pos).css({
				"marginTop": m_top + "px",
				"minWidth": m_left + "px"
			}).children('li').on("click", function() {
				var point = blueticket_forms.parse_latlng(jQuery(this).data("val"));
				jQuery(element).val(jQuery(this).text());
				marker = blueticket_forms.move_marker(map, marker, point, jQuery(cont).data('draggable'), jQuery(cont).data('text'));
				jQuery(point_field).val(marker.getPosition().lat() + ',' + marker.getPosition().lng());
				jQuery(this).parent('ul').remove();
				return false;
			});
		}
	},
	map_resize_all: function() {
		if (jQuery(".blueticket_forms-map").size() && blueticket_forms.map_instances.length) {
			for (i = 0; i < blueticket_forms.map_instances.length; i++) {
				var map = blueticket_forms.map_instances[i];
				var marker = blueticket_forms.marker_instances[i];
				google.maps.event.trigger(map, 'resize');
				map.setZoom(map.getZoom());
				map.setCenter(marker.position)
			}
		}
	},
	reload: function(selector_or_object) {
		if (!selector_or_object) {
			selector_or_object = 'body';
		}
		jQuery(selector_or_object).find(".blueticket_forms-ajax").each(function() {
			blueticket_forms.request(this, blueticket_forms.list_data(this));
		});
	},
	bootstrap_modal: function(header, content) {
		jQuery("#blueticket_forms-modal-window").remove();
		jQuery("body").append('<div id="blueticket_forms-modal-window" class="modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
		jQuery("#blueticket_forms-modal-window .modal-content").html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">' + header + '</h4></div>');
		jQuery("#blueticket_forms-modal-window .modal-content").append('<div class="modal-body">' + content + '</div>');
		jQuery("#blueticket_forms-modal-window").modal();
		jQuery('#myModal').on('hidden.bs.modal', function() {
			jQuery("#blueticket_forms-modal-window").remove();
		});
	},
	ui_modal: function(header, content) {
		jQuery("#blueticket_forms-modal-window").remove();
		jQuery("body").append('<div id="blueticket_forms-modal-window">' + content + '</div>');
		jQuery("#blueticket_forms-modal-window").dialog({
			resizable: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			closeOnEscape: true,
			close: function(event, ui) {
				jQuery("#blueticket_forms-modal-window").remove();
			},
			title: header
		});
	},
	modal: function(header, content) {
		if (typeof(jQuery.fn.modal) != 'undefined') {
            if(jQuery(content).first().prop("tagName") == 'IMG'){
                blueticket_forms.load_image(jQuery(content).first().attr('src'),function(imgObj){
                    blueticket_forms.bootstrap_modal(header, content);
                })
            }else{
                blueticket_forms.bootstrap_modal(header, content);
            }
		} else {
            if(jQuery(content).first().prop("tagName") == 'IMG'){
                blueticket_forms.load_image(jQuery(content).first().attr('src'),function(imgObj){
                    blueticket_forms.ui_modal(header, content);
                })
            }else{
                blueticket_forms.ui_modal(header, content);
            }
		}
	},
	init_tabs: function(container) {
		if (jQuery(container).find('.blueticket_forms-tabs').size()) {
			if (typeof(jQuery.fn.tab) != 'undefined') {
				jQuery(container).find('.blueticket_forms-tabs > ul:first > li > a').on("click", function() {
					jQuery(this).tab('show');
					return false;
				});
				jQuery('.blueticket_forms .nav-tabs a').on('shown.bs.tab', function(e) {
					blueticket_forms.map_resize_all();
				});
			} else {
				jQuery(container).find('.blueticket_forms-tabs').tabs({
					activate: function(event, ui) {
						blueticket_forms.map_resize_all();
					}
				});
			}
		}
	},
	init_tooltips: function(container) {
		if (jQuery(container).find('.blueticket_forms-tooltip').size()) {
			jQuery(container).find('.blueticket_forms-tooltip').tooltip();
		}
	},
	show_message: function(container, text, classname, delay) {
		if (container && text) {
			if (!classname) classname = 'info';
			if (!delay) delay = 3;
			var cont = jQuery(container).closest(".blueticket_forms-container");
			jQuery(cont).children('.blueticket_forms-message').stop(true, true).remove();
			jQuery(cont).append('<div class="blueticket_forms-message ' + (classname ? classname : '') + '">' + text + '</div>');
			jQuery(cont).children('.blueticket_forms-message').on("click", function() {
				jQuery(this).stop(true).slideUp(200, function() {
					jQuery(this).remove();
				});
			}).slideDown().delay(delay * 1000).slideUp(200, function() {
				jQuery(this).remove();
			});
		}
	},
	check_message: function(container) {
		var elements = jQuery(container).find(".blueticket_forms-callback-message");
		if (jQuery(elements).size()) {
			elements.each(function() {
				var element = this;
				if (blueticket_forms.check_container(element, container)) {
					blueticket_forms.show_message(container, jQuery(element).val(), jQuery(element).attr("name"));
					jQuery(element).remove();
				}
			});
		}
	}
}; /** events */
jQuery(document).on("ready blueticket_formsreinit", function() {
	var $ = jQuery;
	if ($(".blueticket_forms").size()) {
		$(".blueticket_forms").on("change", ".blueticket_forms-actionlist", function() {
			var container = blueticket_forms.get_container(this);
			var data = blueticket_forms.list_data(container);
			blueticket_forms.request(container, data);
		});
		$(".blueticket_forms").on("change", ".blueticket_forms-daterange", function() {
			var container = blueticket_forms.get_container(this);
			if ($(this).val()) {
				$(container).find(".blueticket_forms-datepicker-from").datepicker("setDate", new Date($(this).find('option:selected').data('from') * 1000));
				$(container).find(".blueticket_forms-datepicker-to").datepicker("setDate", new Date($(this).find('option:selected').data('to') * 1000));
			} else {
				$(container).find(".blueticket_forms-datepicker-from,.blueticket_forms-datepicker-to").val('');
			}
		});
		$(".blueticket_forms").on("change", ".blueticket_forms-columns-select", function() {
			var container = blueticket_forms.get_container(this);
			var type = $(this).children("option:selected").data('type');
			var fieldname = $(this).children("option:selected").val();
			blueticket_forms.change_filter(type, container, fieldname);
		});
		$(".blueticket_forms").on("click", ".blueticket_forms-action", function() {
			var confirm_text = $(this).data('confirm');
			if (confirm_text && !window.confirm(confirm_text)) {
				return;
			} else {
				var container = blueticket_forms.get_container(this);
				var data = blueticket_forms.list_data(container, this);
				if ($(this).hasClass('blueticket_forms-in-new-window')) {
					blueticket_forms.new_window_request(container, data);
				} else {
					if (data.task == 'save') {
						if (!blueticket_forms.validation_error) {
							blueticket_forms.unique_check(container, data, function(container) {
								data.task = 'save';
								blueticket_forms.request(container, data);
							});
						} else {
							blueticket_forms.show_message(container, blueticket_forms.lang('validation_error'), 'error');
						}
					} else {
						blueticket_forms.request(container, data);
					}
				}
			}
			return false;
		});
		$(".blueticket_forms").on("click", ".blueticket_forms-toggle-show", function() {
			var container = $(this).closest(".blueticket_forms").find(".blueticket_forms-container:first");
			var closed = $(this).hasClass("blueticket_forms-toggle-down");
			if (closed) {
				$(container).stop(true, true).delay(100).slideDown(200, function() {
					$(document).trigger("blueticket_formsslidedown");
					$(container).trigger("blueticket_formsslidedown");
				});
				//$(this).removeClass("blueticket_forms-toggle-down");
				//$(this).addClass("blueticket_forms-toggle-up");
				$(this).closest(".blueticket_forms").find(".blueticket_forms-main-tab").slideUp(200);
			} else {
				$(container).stop(true, true).slideUp(200, function() {
					$(document).trigger("blueticket_formsslideup");
					$(container).trigger("blueticket_formsslideup");
				});
				//$(this).removeClass("blueticket_forms-toggle-up");
				//$(this).addClass("blueticket_forms-toggle-down");
				$(this).closest(".blueticket_forms").find(".blueticket_forms-main-tab").delay(100).slideDown(200);
			}
			return false;
		});
		$(".blueticket_forms").on("keypress", ".blueticket_forms-input", function(e) {
			return blueticket_forms.pattern_callback(e, this);
		});
		$(".blueticket_forms").on("click", ".blueticket_forms-search-toggle", function() {
			$(this).hide(200);
			$(this).closest(".blueticket_forms-ajax").find(".blueticket_forms-search").show(200);
			return false;
		});
		$(".blueticket_forms").on("keydown", ".blueticket_forms-searchdata", function(e) {
			if (e.which == 13) {
				var container = blueticket_forms.get_container(this);
				var data = blueticket_forms.list_data(container);
				data.search = 1;
				blueticket_forms.request(container, data);
				return false;
			}
		});
		$(".blueticket_forms").on("change", ".blueticket_forms-upload", function() {
			var container = blueticket_forms.get_container(this);
			var data = blueticket_forms.list_data(container);
			blueticket_forms.upload_file(this, data, container);
			return false;
		});
		$(".blueticket_forms").on("click", ".blueticket_forms-remove-file", function() {
			var container = blueticket_forms.get_container(this);
			var data = blueticket_forms.list_data(container);
			blueticket_forms.remove_file(this, data, container);
			return false;
		});
		$(".blueticket_forms").on("click", ".blueticket_forms_modal", function() {
			var content = $(this).data("content");
			var header = $(this).data("header");
			blueticket_forms.modal(header, content);
			return false;
		});
		$(".blueticket_forms-ajax").each(function() {
			blueticket_forms.init_datepicker(this);
			blueticket_forms.init_datepicker_range($(this).find('.blueticket_forms-columns-select option:selected').data('type'), this);
			blueticket_forms.depend_init(this);
			blueticket_forms.map_init(this);
			blueticket_forms.check_fixed_buttons();
			blueticket_forms.init_tooltips(this);
			blueticket_forms.init_tabs(this);
			blueticket_forms.check_message(this);
			blueticket_forms.hide_progress(this);
		});
	}
});
jQuery(window).on("resize load blueticket_formsslidetoggle", function() {
	blueticket_forms.check_fixed_buttons();
});
jQuery(window).on("load", function() {
	jQuery(".blueticket_forms-ajax").each(function() {
		blueticket_forms.init_texteditor(this);
	});
});
jQuery(document).on("blueticket_formsbeforerequest", function(event, container) {});
jQuery(document).on("blueticket_formsafterrequest", function(event, container) {
	blueticket_forms.init_datepicker(container);
	blueticket_forms.init_texteditor(container);
	blueticket_forms.init_datepicker_range(jQuery(container).find('.blueticket_forms-columns-select option:selected').data('type'), container);
	blueticket_forms.depend_init(container);
	blueticket_forms.map_init(container);
	blueticket_forms.check_fixed_buttons();
	blueticket_forms.init_tooltips(container);
	blueticket_forms.init_tabs(container);
	blueticket_forms.check_message(container);
});
jQuery(document).on("blueticket_formsafterupload", function(event, container) {
	blueticket_forms.check_message(container);
});
//
/** print */
jQuery.extend({
	print_window: function(print_win, blueticket_forms) {
		var data = {};
		jQuery(blueticket_forms).find(".blueticket_forms-data").each(function() {
			data[jQuery(this).attr("name")] = jQuery(this).val();
		});
		data.task = 'print';
		jQuery.ajax({
			data: data,
			success: function(out) {
				print_win.document.open();
				print_win.document.write(out);
				print_win.document.close();
				jQuery(blueticket_forms).find(".blueticket_forms-data[name=key]:first").val(jQuery(print_win.document).find(".blueticket_forms-data[name=key]:first").val());
				var ua = navigator.userAgent.toLowerCase();
				if ((ua.indexOf("opera") != -1)) { // opera fix
					jQuery(print_win).load(function() {
						print_win.print();
					});
				} else {
					jQuery(print_win).ready(function() {
						print_win.print();
					});
				}
			}
		});
	}
});
// 
/** upload */
jQuery.extend({
	createUploadIframe: function(id, uri) {
		var frameId = 'jUploadFrame' + id;
		var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
		if (window.ActiveXObject) {
			if (typeof uri == 'boolean') {
				iframeHtml += ' src="' + 'javascript:false' + '"';
			} else if (typeof uri == 'string') {
				iframeHtml += ' src="' + uri + '"';
			}
		}
		iframeHtml += ' />';
		jQuery(iframeHtml).appendTo(document.body);
		return jQuery('#' + frameId).get(0);
	},
	createUploadForm: function(id, fileElementId, data) {
		var formId = 'jUploadForm' + id;
		var fileId = 'jUploadFile' + id;
		var form = jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');
		if (data) {
			for (var i in data.blueticket_forms) {
				if (data.blueticket_forms[i] == 'postdata') {
/*for (var j in data.blueticket_forms.postdata) {
			             jQuery('<input type="hidden" name="blueticket_forms[postdata][' + j + ']" value="' + data.blueticket_forms.postdata[j] + '" />').appendTo(form);
			         }*/
				} else
				jQuery('<input type="hidden" name="blueticket_forms[' + i + ']" value="' + data.blueticket_forms[i] + '" />').appendTo(form);
			}
		}
		var oldElement = jQuery('#' + fileElementId);
		var newElement = jQuery(oldElement).clone();
		jQuery(oldElement).attr('id', fileId);
		jQuery(oldElement).before(newElement);
		jQuery(oldElement).appendTo(form);
		jQuery(form).css('position', 'absolute');
		jQuery(form).css('top', '-1200px');
		jQuery(form).css('left', '-1200px');
		jQuery(form).appendTo('body');
		return form;
	},
	ajaxFileUpload: function(s) {
		s = jQuery.extend({}, jQuery.ajaxSettings, s);
		var id = new Date().getTime();
		var form = jQuery.createUploadForm(id, s.fileElementId, (typeof(s.data) == 'undefined' ? false : s.data));
		var io = jQuery.createUploadIframe(id, s.secureuri);
		var frameId = 'jUploadFrame' + id;
		var formId = 'jUploadForm' + id;
		if (s.global && !jQuery.active++) {
			jQuery.event.trigger("ajaxStart");
		}
		var requestDone = false;
		var xml = {};
		if (s.global) jQuery.event.trigger("ajaxSend", [xml, s]);
		var uploadCallback = function(isTimeout) {
			var io = document.getElementById(frameId);
			try {
				if (io.contentWindow) {
					xml.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
					xml.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;
				} else if (io.contentDocument) {
					xml.responseText = io.contentDocument.document.body ? io.contentDocument.document.body.innerHTML : null;
					xml.responseXML = io.contentDocument.document.XMLDocument ? io.contentDocument.document.XMLDocument : io.contentDocument.document;
				}
			} catch (e) {}
			if (xml || isTimeout == "timeout") {
				requestDone = true;
				var status;
				try {
					status = isTimeout != "timeout" ? "success" : "error";
					if (status != "error") {
						var data = jQuery.uploadHttpData(xml, s.dataType);
						if (s.success) s.success(data, status);
						if (s.global) jQuery.event.trigger("ajaxSuccess", [xml, s]);
					} else {}
				} catch (e) {
					status = "error";
				}
				if (s.global) jQuery.event.trigger("ajaxComplete", [xml, s]);
				if (s.global && !--jQuery.active) jQuery.event.trigger("ajaxStop");
				if (s.complete) s.complete(xml, status);
				jQuery(io).unbind();
				setTimeout(function() {
					try {
						jQuery(io).remove();
						jQuery(form).remove();
					} catch (e) {}
				}, 100);
				xml = null
			}
		};
		if (s.timeout > 0) {
			setTimeout(function() {
				if (!requestDone) uploadCallback("timeout");
			}, s.timeout);
		}
		try {
			var form = jQuery('#' + formId);
			jQuery(form).attr('action', s.url);
			jQuery(form).attr('method', 'POST');
			jQuery(form).attr('target', frameId);
			if (form.encoding) {
				jQuery(form).attr('encoding', 'multipart/form-data');
			} else {
				jQuery(form).attr('enctype', 'multipart/form-data');
			}
			jQuery(form).submit();
		} catch (e) {}
		var ttt = 0;
		var ua = navigator.userAgent.toLowerCase();
		if ((ua.indexOf("opera") != -1)) { // opera fix
			jQuery('#' + frameId).load(function() {
				ttt++;
				if (ttt == 2) {
					uploadCallback();
				}
			});
		} else {
			jQuery('#' + frameId).on("load", uploadCallback);
		}
		return {
			abort: function() {}
		};
	},
	uploadHttpData: function(r, type) {
		data = (type == "xml" && !type) ? r.responseXML : r.responseText;
		if (type == "script") jQuery.globalEval(data);
		if (type == "json") eval("data = " + data);
		return data;
	}
});