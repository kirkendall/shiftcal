(function($) {
    
    $.fn.getMapLink = function(address) {
        return 'http://maps.google.com/' +
            '?bounds=45.389771,-122.829208|45.659647,-122.404175&q=' +
            encodeURIComponent(address);
    }

    $.fn.formatDate = function(dateString) {
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
    
} (jQuery));