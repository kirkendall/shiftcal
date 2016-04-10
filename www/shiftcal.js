$(document).ready( function() {
    function displayCalendar() {
        $(document).on('click', 'a.expandDetails', function(e) {
            e.preventDefault();
            return false;
        });
        $(document).on('click', 'a#add-event-button', function(e) {
            displayEditForm();
        });
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
    }
    function displayEditForm( id ) {
        var shiftEvent = {
            id: id
        };
        shiftEvent.lengthOptions = [
            {
                range: "0-3"
            },
            {
                range: "3-8"
            },
            {
                range: "8-15"
            },
            {
                range: "15+"
            }
        ];
        shiftEvent.timeOptions = [];
        for ( var a = 0; a < 2; a++ ) {
            for ( var h = 0; h < 12; h++ ) {
                for ( var m = 0; m < 60; m += 15 ) {
                    var displayHour = h;
                    if ( displayHour === 0 ) {
                        displayHour = 12;
                    }
                    var displayMinute = m;
                    if ( displayMinute == 0 ) {
                        displayMinute = "00";
                    }
                    var meridian = ( a == 0 ) ? "AM" : "PM";
                    shiftEvent.timeOptions.push({
                        time: displayHour + ":" + displayMinute + " " + meridian
                    });
                }
            }
        }
        shiftEvent.timeOptions.push({ time: "11:59 PM" });
        var template = $('#mustache-edit').html();
        var info = Mustache.render(template, shiftEvent);
        $('#mustache-html').empty().append(info);
        $('#edit-header').affix({
            offset: {
                top: 100,
            }
        })
        $('#save-button').click(function() {
            var postVars = {};
            $('form').serializeArray().map(function(x){postVars[x.name] = x.value;}) ;
            $.ajax({
                type: 'POST',
                url: 'manage_event.php',
                data: JSON.stringify(postVars),
                success: function(returnVal) {
                        alert('saved!');
                    },
                error: function(returnVal) {
                        alert(returnVal.responseText);
                    },
                dataType: 'json',
                contentType: 'application/json'
            });
        });
    }

    displayCalendar();
});
