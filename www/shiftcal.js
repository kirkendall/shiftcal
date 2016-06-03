$(document).ready( function() {
   
    var container = $('#mustache-html');

    function getEventHTML(startDate, endDate, callback) {

        $.get( 'events.php?startdate=' + startDate.toISOString() + '&enddate=' + endDate.toISOString(), function( data ) {
            var groupedByDate = [];
            var mustacheData = { dates: [] };            
            $.each(data.events, function( index, value ) {

                var date = formatDate(value.date);
                if (groupedByDate[date] === undefined) {
                    groupedByDate[date] = {
                        yyyymmdd: value.date,
                        date: date,
                        events: []
                    };
                    mustacheData.dates.push(groupedByDate[date]);
                }
                var timeParts = value.time.split(':');
                var hour = parseInt(timeParts[0]);
                var meridian = 'AM';
                if ( hour === 0 ) {
                    hour = 12;
                } else if ( hour >= 12 ) {
                    meridian = 'PM';
                    if ( hour > 12 ) {
                        hour = hour - 12;
                    }
                }
                value.displayTime = hour + ':' + timeParts[1] + ' ' + meridian;
                value.mapLink = getMapLink(value.address);
                // value.showEditButton = true; // TODO: permissions
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
            var template = $('#view-events-template').html();
            var info = Mustache.render(template, mustacheData);
           	callback(info);
        });
    }

    function getMapLink(address) {
        return 'http://maps.google.com/' +
            '?bounds=45.389771,-122.829208|45.659647,-122.404175&q=' +
            encodeURIComponent(address);
    }

    function displayEditForm( id , secret ) {
        if (id && secret) {
            // TODO: loading spinner
            $.get( 'retrieve_event.php?id=' + id + "&secret=" + secret, function( data ) {
                data.secret = secret;
                data.readComic = true;
                populateEditForm( data );
            });
        } else {
            populateEditForm({ dates: [] });
        }
    }

    function populateEditForm( shiftEvent ) {
        var i, h, m, meridian,
            displayHour, displayMinute, timeChoice,
            template, rendered, item,
            lengths = [ '0-3', '3-8', '8-15', '15+'],
            audiences = [{code: 'F', text: 'Family friendly. Adults bring children.'},
                         {code: 'G', text: 'General. For adults, but kids welcome.'},
                         {code: 'A', text: '21+ only. Alcohol involved.'}];

        shiftEvent.lengthOptions = [];
        for ( i = 0; i < lengths.length; i++ ) {
            item = {range: lengths[i]};
            if (shiftEvent.length == lengths[i]) {
                item.isSelected = true;
            }
            shiftEvent.lengthOptions.push(item);
        }

        shiftEvent.timeOptions = [];
        meridian = 'AM';
        for ( h = 0; h < 24; h++ ) {
            for ( m = 0; m < 60; m += 15 ) {
                if ( h > 11 ) {
                    meridian = 'PM';
                }
                if ( h === 0 ) {
                    displayHour = 12;
                } else if ( h > 12 ) {
                    displayHour = h - 12;
                } else {
                    displayHour = h;
                }
                displayMinute = m;
                if ( displayMinute === 0 ) {
                    displayMinute = '00';
                }
                timeChoice = {
                    time: displayHour + ':' + displayMinute + ' ' + meridian,
                    value: h + ':' + displayMinute + ':00'
                };
                if (h < 10) {
                    timeChoice.value = '0' + timeChoice.value;
                }
                if (shiftEvent.time === timeChoice.value) {
                    timeChoice.isSelected = true;
                }
                shiftEvent.timeOptions.push(timeChoice);
            }
        }
        shiftEvent.timeOptions.push({ time: "11:59 PM" });

        shiftEvent.audienceOptions = [];
        if (!shiftEvent.audience) {
            shiftEvent.audience = 'G';
        }
        shiftEvent.audienceOptions = [];
        for ( i = 0; i < audiences.length; i++ ) {
            if (shiftEvent.audience == audiences[i].code) {
                audiences[i].isSelected = true;
            }
            shiftEvent.audienceOptions.push(audiences[i]);
        }

        template = $('#mustache-edit').html();
        rendered = Mustache.render(template, shiftEvent);
        container.empty().append(rendered);
        $('#date-select').setupDatePicker(shiftEvent['dates'] || []);

        $('#edit-header').affix({
            offset: {
                top: 100
            }
        });
        if (shiftEvent.dates.length === 0) {
            $('#save-button').prop('disabled', true);
            $('#preview-button').prop('disabled', true);
        }
        $('#save-button').click(function() {
            var postVars,
                isNew = !shiftEvent.id;
            $('.form-group').removeClass('has-error');
            $('.help-block').remove();
            $('#save-result').removeClass('text-danger').text('');
            postVars = eventFromForm();
            if (!isNew) {
                postVars['id'] = shiftEvent.id;
            }
            var data = new FormData();
            $.each($('#image')[0].files, function(i, file) {
                data.append('file', file);
            });
            data.append('json', JSON.stringify(postVars));
            var opts = {
                type: 'POST',
                url: 'manage_event.php',
                contentType: false,
                processData: false,
                cache: false,
                data: data,
                success: function(returnVal) {
                    var msg = isNew ?
                        'Thank you! A link with a URL to edit and manage the ' +
                            'event has been emailed to ' + postVars.email + '.' :
                        'Your event has been updated!';

                    if (returnVal.secret) {
                        location.hash = '#editEvent/' + returnVal.id + '/' + returnVal.secret;
                        $('#secret').val(returnVal.secret);
                        msg += ' You may also bookmark the current URL before you click OK.'
                    }
                    $('#success-message').text(msg);
                    $('#success-modal').modal('show');
                    shiftEvent.id = returnVal.id;
                },
                error: function(returnVal) {
                    var err = returnVal.responseJSON
                                ? returnVal.responseJSON.error
                                : { message: 'Server error saving event!' };
                    $('#save-result').addClass('text-danger').text(err.message);
                    // Collapse all groups
                    $('.panel-collapse').removeClass('in');
                    $.each(err.fields, function(fieldName, message) {
                        var input = $('input[name=' + fieldName + ']');
                        input.closest('.form-group,.checkbox')
                            .addClass('has-error')
                            .append('<div class="help-block">' + message + '</div>');
                        // Then re-expand any group with errors
                        input.closest('.panel-collapse')
                            .addClass('in');
                    });
                }
            };
            if(data.fake) {
                opts.xhr = function() { var xhr = jQuery.ajaxSettings.xhr(); xhr.send = xhr.sendAsBinary; return xhr; }
                opts.contentType = "multipart/form-data; boundary="+data.boundary;
                opts.data = data.toString();
            }
            $.ajax(opts);
        });

        $(document).off('click', '#preview-button')
            .on('click', '#preview-button', function(e) {
            previewEvent(shiftEvent);
        });
    }

    function eventFromForm() {
        var harvestedEvent = {};
        $('form').serializeArray().map(function (x) {
            harvestedEvent[x.name] = x.value;
        });
        harvestedEvent['dates'] = $('#date-picker').dateList();
        return harvestedEvent;
    }

    function previewEvent(shiftEvent) {
        var previewEvent = {},
            mustacheData;
        $.extend(previewEvent, shiftEvent, eventFromForm());
        previewEvent.displayTime = previewEvent.time;
        previewEvent['length'] += ' miles';
        previewEvent['mapLink'] = getMapLink(previewEvent['address']);
        $('#event-entry').hide();
        mustacheData = {
            dates: [{
                date: formatDate(previewEvent.dates[0]),
                events: [previewEvent]
            }],
            preview: true
        };
        $('#preview-button').hide();
        $('#preview-edit-button').show();
        var template = $('#view-events-template').html();
        var info = Mustache.render(template, mustacheData);
        container.append(info);
    }

    function formatDate(dateString) {
        var parts = dateString.split('-'),
            date = new Date(parts[0], parseInt(parts[1]) - 1, parts[2]);

        return date.toLocaleDateString(
            navigator.language,
            {
                weekday: 'long',
                month: 'long',
                day: 'numeric'
            }
        );
    }

    function viewEvents(){
        location.hash = 'viewEvents';
        var startDate = new Date(); 
        var endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + 9);

        container.empty()
             .append($('#legend-template').html());

        getEventHTML(startDate, endDate, function (eventHTML) {
             container.append(eventHTML);
             container.append($('#load-more-template').html());               
             $(document).on('click', '#load-more', function(e) {
                  startDate.setDate(startDate.getDate() + 10);
                  endDate.setDate(startDate.getDate() + 9);
                  getEventHTML(startDate, endDate, function(eventHTML) {
                       $('#load-more').before(eventHTML);        
                  });
             });          
        });
    }
    
    function displayAbout() {
        var content = $('#aboutUs').html();
        container.empty().append(content);
        $(document).scrollTop();
    }
    
    function displayPedalpalooza() {
        location.hash = 'pedalpalooza';    
        var startDate = new Date("June 9, 2016");
        var endDate = new Date("July 4, 2016 23:59:59");
        var pedalpalooza = './images/pp2016.jpg';
        container.empty()
             .append($('#pedalpalooza-header').html())
             .append($('#legend-template').html());
        getEventHTML(startDate, endDate, function (eventHTML) {
             container.append(eventHTML);
             container.append($('#pedalpalooza-prior').html());         
        });
    }
    
    function dateJumpPedalpalooza(ev) {
        var e = ev.target;
        if (e.hasAttribute('data-date')) {
            var $e = $(e);
            var yyyymmdd = $e.attr('data-date');
            var $jumpTo = $("div[data-date='" + yyyymmdd + "']");
            console.log($jumpTo.children().length);
            if ($jumpTo){
                $('html, body').animate({
                    scrollTop: $jumpTo.offset().top
                }, 500);
            }
        }    
    }
    
    $(document).on('click', 'a#add-event-button', function(e) {
        displayEditForm();
    });

    $(document).on('click', 'a#view-events-button, #confirm-cancel, #success-ok', viewEvents);
    
    $(document).on('click', 'a#about-button', function(e) {
        displayAbout();
    });

    $(document).on('click', 'a#oldSite-button', function(e) {
        window.location.href = "http://shift2bikes.com/cal";
    });
    
    $(document).on('click', 'a#pedalpalooza-button', function(e) {
        displayPedalpalooza();
    });


    $(document).on('click', '#date-picker-pedalpalooza', function(ev) {
        dateJumpPedalpalooza(ev);
    });
    
    $(document).on('click','.navbar-collapse.collapse.in',function(e) {
        if( $(e.target).is('a') ) {
            $(this).collapse('hide');
        }
    });

    $(document).on('click', 'a.expandDetails', function(e) {
        e.preventDefault();
        return false;
    });

    $(document).on('click', 'button.edit', function(e) {
        var id = $(e.target).closest('div.event').data('event-id');
        displayEditForm(id);
    });

    $(document).on('click', '#preview-edit-button', function() {
        $('#event-entry').show();
        $('.date').remove();
        $('#preview-button').show();
        $('#preview-edit-button').hide();
    });

    if (/^#addEvent/.test(location.hash)) {
        displayEditForm();
    } else if (
        /^#editEvent/.test(location.hash) &&
        location.hash.indexOf('/') > 0
    ) {
        var locationHashParts = location.hash.split('/');
        displayEditForm(locationHashParts[1], locationHashParts[2]);
    } else {
        
        viewEvents();
    }
            
});
