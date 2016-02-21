$(document).ready( function() {
	var startDate = new Date();
	var endDate = new Date();
	endDate.setDate(startDate.getDate() + 3);
	$.get( 'events.php?startdate=' + startDate.toISOString() + '&enddate=' + endDate.toISOString(), function( data ) {
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
			var timeParts = value.time.split(':');
			var hour = parseInt(timeParts[0]);
			var meridian = 'AM';
			if ( hour > 12 ) {
				hour = hour - 12;
				meridian = 'PM';
			}
			value.displayTime = hour + ':' + timeParts[1] + ' ' + meridian;
			value.mapLink = 'http://maps.google.com/?bounds=45.389771,-122.829208|45.659647,-122.404175&q=';
			value.mapLink += encodeURIComponent( value.address );
			groupedByDate[date].events.push(value);
		});

		var compareEvents = function ( event1, event2 ) {
			if ( event1.time < event2.time ) {
				return -1;
			}
			if ( event1.time > event2.time ) {
				return 1;
			}
			return 0;
		};
		for ( var date in groupedByDate )  {
			groupedByDate[date].events.sort(compareEvents);
		}
		var template = $('#mustache-template').html();
		var info = Mustache.render(template, mustacheData);
		$('#mustache-html').append(info);
	});
});