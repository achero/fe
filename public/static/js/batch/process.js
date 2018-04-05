$(document).on('ready', init());

function init() {
	
	var FORM = (function() {
		var SUPPLIER_ID = $('#n_id_supplier').val();
		var process = function() {
			$('form').on('submit', function(e) {
				e.preventDefault();
				form_serialize = $('form').serialize();
				$('.alert').removeClass('hidden').addClass('hidden');
				$('.overlay').removeClass('hidden');
				$.post('lotes', form_serialize, function(response) {
					$('.overlay').removeClass('hidden').addClass('hidden');
					if (response.status) {
						$('.alert-success').removeClass('hidden').find('p').stop().text(response.message);
					} else {
						$('.alert-danger').removeClass('hidden').find('p').stop().text(response.message);
					}
				}, 'json');
			});
		}
		return {
			init: function() {
				process();
			}
		}
	}())

	FORM.init();
}