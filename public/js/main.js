var configureDatepickers = function() {
	$('.datepicker-control').datepicker({ format: 'dd/mm/yyyy', weekStart: 1, startDate: "2017-07-01" });
}

var init = function() {
	configureDatepickers();
}

$(document).ready(init);
