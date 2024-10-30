(function(window, $){
	"use strict";
	var Calendarista = window['Calendarista'] || {};
	Calendarista.imageSelector = function(options){
		this.options = options || {};
		this.$root = $(options['id']);
		this.previewImageUrl = options['previewImageUrl'];
		this.unloadDelegate = Calendarista.createDelegate(this, this.unloadHandler);
		$(window).on('unload', this.unloadDelegate);
		this.initialize();
	};
	Calendarista.imageSelector.prototype.initialize = function(){
		var context = this;
		this.$iconUrlRemove = this.$root.find('button[name="iconUrlRemove"]');
		this.$previewThumbnail = this.$root.find('.preview-icon');
		this.galleryWindow = window['wp'].media({
			'title': 'Select an icon'
			, 'library': {'type': 'image'}
			, 'multiple': false
			, button: {'text': 'Select'}
		});
		this.$previewThumbnail.on('click', function(e){
			context.$selectedPreviewElement = $(this);
			e.preventDefault();
			context.galleryWindow.open();
		});
		this.$iconUrlRemove.on('click', function(e){
			e.stopPropagation();
			var url = context.previewImageUrl
				, $this = $(this)
				, fieldName = $this.attr('data-calendarista-preview-icon')
				, $field = context.$root.find('input[name="' + fieldName + '"]')
				, $previewElement = context.$root.find('div[data-calendarista-preview-icon="' + fieldName + '"]');
			$previewElement.css({'background-image': 'url(' + url + ')'});
			$this.prop('disabled', true);
			$field.val('');
		});
		this.galleryWindow.on('select', function(){
			var userSelection = context.galleryWindow.state().get('selection').first().toJSON();
			context.imagePickerSelectionChanged(userSelection['url'], userSelection['height'], userSelection['width']);
		});
		this.removeButtonState();
	};
	Calendarista.imageSelector.prototype.imagePickerSelectionChanged = function(url, height, width){
		var fieldName = this.$selectedPreviewElement.attr('data-calendarista-preview-icon')
			, $field = this.$root.find('input[name="' + fieldName + '"]')
			, styles = {'background-image': 'url(' + url + ')'};
		this.$selectedPreviewElement.css(styles);
		$field.val(url);
		this.removeButtonState();
	};
	Calendarista.imageSelector.prototype.removeButtonState = function(){
		var i
			, $icon
			, fieldName
			, val;
		for(i = 0; i < this.$iconUrlRemove.length; i++){
			$icon = $(this.$iconUrlRemove[i]);
			fieldName = $icon.attr('data-calendarista-preview-icon');
			val = this.$root.find('input[name="' + fieldName + '"]').val();
			if(val){
				$icon.prop('disabled', false);
			}else{
				$icon.prop('disabled', true);
			}
		}
	};
	Calendarista.imageSelector.prototype.unloadHandler = function () {
		if(this.$iconUrlRemove){
			this.$iconUrlRemove.off();
		}
		if(this.$previewThumbnail){
			this.$previewThumbnail.off();
		}
		delete this.unloadDelegate;
	};
	if(!window['Calendarista']){
		window['Calendarista'] = Calendarista;
	}
}(window, window['jQuery']));
(function(window, $){
	"use strict";
	var Calendarista = window['Calendarista'] || {};
	Calendarista.listPager = function(options){
		this.options = options || {};
		this.id = options['id'];
		this.$root = $(options['id']);
		this.callback = options['callback'];
		this.unloadDelegate = Calendarista.createDelegate(this, this.unloadHandler);
		$(window).on('unload', this.unloadDelegate);
		this.initialize();
	};
	Calendarista.listPager.prototype.initialize = function(){
		var context = this;
	};
	Calendarista.listPager.prototype.pagerButtonDelegates = function(){
		var context = this;
		this.$root = $(this.id);
		this.$nextPage = this.$root.find('a[class="next-page"]');
		this.$lastPage = this.$root.find('a[class="last-page"]');
		this.$prevPage = this.$root.find('a[class="prev-page"]');
		this.$firstPage = this.$root.find('a[class="first-page"]');
		this.$nextPage.on('click', function(e){
			context.gotoPage(e);
		});
		this.$lastPage.on('click', function(e){
			context.gotoPage(e);
		});
		this.$prevPage.on('click', function(e){
			context.gotoPage(e);
		});
		this.$firstPage.on('click', function(e){
			context.gotoPage(e);
		});
	};
	Calendarista.listPager.prototype.gotoPage = function(e){
		var pagedValue = this.getUrlParameter('paged', $(e.currentTarget).attr('href'))
			, model = pagedValue ? [{ 'name': 'paged', 'value': pagedValue }] : [];
		this.$nextPage.off();
		this.$lastPage.off();
		this.$prevPage.off();
		this.$firstPage.off();
		if(this.callback){
			this.callback(/*false, */model);
		}
		e.preventDefault();
		return false;
	};
	Calendarista.listPager.prototype.removeURLParameter = function(parameter) {
		 var url = window.location.href;
		//prefer to use l.search if you have a location/link object
		var urlparts= url.split('?');   
		if (urlparts.length>=2) {

			var prefix= encodeURIComponent(parameter)+'=';
			var pars= urlparts[1].split(/[&;]/g);

			//reverse iteration as may be destructive
			for (var i= pars.length; i-- > 0;) {    
				//idiom for string.startsWith
				if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
					pars.splice(i, 1);
				}
			}

			url= urlparts[0]+'?'+pars.join('&');
		}
		window.history.replaceState({}, document.title, url);
	};
	Calendarista.listPager.prototype.getUrlParameter = function(param, url) {
		var regex, results;
		param = param.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
		regex = new RegExp('[\\?&]' + param + '=([^&#]*)');
		results = regex.exec(url);
		return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
	};
	Calendarista.listPager.prototype.unloadHandler = function () {
		if(this.$nextPage){
			this.$nextPage.off();
		}
		if(this.$lastPage){
			this.$lastPage.off();
		}
		if(this.$prevPage){
			this.$prevPage.off();
		}
		if(this.$firstPage){
			this.$firstPage.off();
		}
		delete this.unloadDelegate;
	};
	if(!window['Calendarista']){
		window['Calendarista'] = Calendarista;
	}
}(window, window['jQuery']));
