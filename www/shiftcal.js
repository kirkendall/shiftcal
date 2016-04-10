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
        setupDatePicker(shiftEvent['dates'] || []);

        $('#edit-header').affix({
            offset: {
                top: 100,
            }
        })
        $('#save-button').click(function() {
            var postVars = {};
            $('form').serializeArray().map(function(x){postVars[x.name] = x.value;}) ;
            postVars['dates'] = dateList();
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

    /* Date Picker JS */
    // Global state variables, initialized in setupDatePicker
    var $dateSelect,
        $loadLater,
        $loadEarlier,
        $monthTable,
        monthTemplate,
        earliestMonth,
        latestMonth,
        dateMap,
        today;

    // Some constants used for generating html. The JOY of javascript stdlib
    var monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    var cellClases = ["color-jan", "color-feb", "color-mar", "color-apr", "color-may", "color-jun",
        "color-jul", "color-aug", "color-sep", "color-oct", "color-nov", "color-dec"];

    // dateList returns the currently selected list of (normalized) dates for consumption by manage_event.php
    function dateList() {
        var dates = [];
        for (var key in dateMap) {
            if (dateMap.hasOwnProperty(key) && dateMap[key]) {
                dates.push(key);
            }
        }
        return dates;
    }

    // setupDatePicker sets up the global variables and populates the date picker element
    function setupDatePicker(dates) {
        // Fill in global variables
        // Set up dateMap
        dateMap = {};
        for (var i=0; i<dates.length; i++) {
            dateMap[normalizeDate(dates[i])] = true;
        }

        // Scrolling container for the table
        $dateSelect = $('#date-select');
        // Placeholder divs that trigger loading when visible
        $loadLater = $('#load-later');
        $loadEarlier = $('#load-earlier');
        // Table that contains months
        $monthTable = $("#month-table");

        monthTemplate = $('#mustache-select-month').html();

        // Tracks when today is for styling purposes
        today = new Date();
        // These track which month to add to the start/end
        earliestMonth = today;
        latestMonth = today;

        // Fill in the first month
        $monthTable.html(getMonthHTML(earliestMonth));
        // Scroll the table to the top of the first month
        $dateSelect.scrollTop(loadLaterTop());

        // Add a click handler for individual days
        $monthTable.click(function(ev) {
            var e = ev.target;
            if (e.hasAttribute('data-date')) {
                var $e = $(e),
                    date = $e.attr('data-date');

                dateMap[date] = !dateMap[date];
                $e.toggleClass('selected', dateMap[date]);
                return false;
            }
            return true;
        });

        // Setup the month table scroll checks
        $dateSelect.scroll(checkBounds);
    }


    function isToday(date) {
        return (date.getDate() == today.getDate()
            && date.getMonth() == today.getMonth()
            && date.getFullYear() == today.getFullYear())
    }

    function normalizeDate(date) {
        var jsd = new Date(date);
        var day = jsd.getDate();
        var monthIndex = jsd.getMonth();
        var year = jsd.getFullYear();

        return year + '-' + (monthIndex+1) + '-' + day;
    }

    function isSelected(date) {
        return !!dateMap[normalizeDate(date)];
    }

    function makeWeekData(date) {
        // date is the first day in the week
        // [{ day - string to display
        //    classes - classes to display },...]
        var week = [];
        for (var i=0;i<7;i++) {
            var day = {};
            day['day'] = date.getDate();
            day['date'] = normalizeDate(date);
            day['classes'] = (isToday(date) ? "today" : "") + " " + (isSelected(date) ? "selected" : "") + " " + cellClases[date.getMonth()] + (date.getDay() % 2 == 0 ? "-odd" : "");
            week.push(day);

            date = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);
        }
        return week;
    }

    function makeMonthData(date) {
        // date is a day in a month to add

        // weeks:
        //   monthTitle - left title, only in first week
        //   weeksInMonth - rows that the title spans
        //   days:
        //     day: title
        //     date: yyyy-m-d (normalized)
        //     selected: cell class if selected

        // Normalize date, ensuring first of month
        var firstOfMonth = new Date(date.getFullYear(), date.getMonth(), 1);
        // Find the first day of the week for the week the 1st falls on
        var firstDay = new Date(firstOfMonth.getFullYear(), firstOfMonth.getMonth(), -firstOfMonth.getDay()+1);

        var firstOfNextMonth = new Date(date.getFullYear(), date.getMonth()+1, 1);
        // Don't include the week next months 1st lands on
        var stopDay = new Date(firstOfNextMonth.getFullYear(), firstOfNextMonth.getMonth(), -firstOfNextMonth.getDay()+1);

        var weeks = [];
        for (var startOfWeek=firstDay;startOfWeek<stopDay;) {
            var week = {};
            week['days'] = makeWeekData(startOfWeek);
            weeks.push(week);

            startOfWeek = new Date(startOfWeek.getFullYear(), startOfWeek.getMonth(), startOfWeek.getDate() + 7);
        }
        weeks[0]['monthTitle'] = monthNames[firstOfMonth.getMonth()] + " " + firstOfMonth.getFullYear();
        weeks[0]['weeksInMonth'] = weeks.length;

        return {"weeks": weeks};
    }

    function getMonthHTML(date) {
        return Mustache.render(monthTemplate, makeMonthData(date));
    }

    function loadLaterTop() {
        return $loadLater.offset().top - $dateSelect.offset().top;
    }

    function loadEarlierBottom() {
        return ($loadEarlier.offset().top + $loadEarlier.prop('scrollHeight')) - $dateSelect.offset().top;
    }

    var checking = false;
    function checkBounds() {
        if (checking) {
            return;
        }
        var added = false;
        if (loadEarlierBottom() >= 0) {
            latestMonth = new Date(latestMonth.getFullYear(), latestMonth.getMonth()-1, 1);
            var preHeight = $monthTable.height();
            $monthTable.prepend(getMonthHTML(latestMonth));
            var heightChange = $monthTable.height() - preHeight;
            $dateSelect.scrollTop($dateSelect.scrollTop() + heightChange);
            added = true;
        }
        if (loadLaterTop() <= $dateSelect.height()) {
            earliestMonth = new Date(earliestMonth.getFullYear(), earliestMonth.getMonth()+1, 1);
            $monthTable.append(getMonthHTML(earliestMonth));
            added = true;
        }
        if (added) {
            checking = true;
            setTimeout(function() {
                checking = false;
                checkBounds();
            }, 10);
        }
    }
    /* /Date Picker JS */

    displayCalendar();
});
