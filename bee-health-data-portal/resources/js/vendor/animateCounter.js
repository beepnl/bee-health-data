export default function animateNumbers() {
	$('.count').each(function () {
		let isBytesToSize = $(this).hasClass('bytes-to-size');
		$(this).prop('Counter',0).animate({
			Counter: $(this).text()
		}, {
			duration: 500,
			easing: 'swing',
			step: function (now) {
				let value = Math.ceil(now)
				
				if(isBytesToSize){
					value =  window.utils.bytesToSize(value)
				}
				$(this).text(value);
			}
		});
	});
}
