$(document).ready( function() {
	var startDate = new Date();
	var endDate = new Date();
	endDate.setDate(startDate.getDate() + 3);
	$.get( '/events.php?startdate=' + startDate.toISOString() + '&enddate=' + endDate.toISOString(), function( data ) {
		var groupedByDate = [];
		var mustacheData = { dates: [] };
		$.each(data.events, function( index, value ) {
			var date = value.date;
			if (groupedByDate[date] === undefined) {
				groupedByDate[date] = {
					date: date,
					events: []
				};
				mustacheData.dates.push(groupedByDate[date]);
			}
			groupedByDate[date].events.push(value);
		});

		var template = $('#mustache-template').html();
		var info = Mustache.render(template, mustacheData);
		$('#mustache-html').append(info);
	});
});