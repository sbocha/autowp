define(
    'default/maintenance',
    ['jquery', 'bootstrap'],
    function($, Bootstrap) {
        return {
            init: function() {
                setInterval(function() {
                    $.getJSON('/maintenance/progress', function(json) {
                        $('.progress-bar')
                            .width(json.progress + '%')
                            .text(json.progress.toFixed(4) + '%');
                    });
                }, 10000);
            }
        }
    }
);