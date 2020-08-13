CHART = $.extend({
    dateFieldName: "date1",
    foo: "bar",

    init: function() {

        // suppress form submission
        $('#form').submit(false);


        this.dateField = $('input[name="' + this.dateField + '"]');

        var btn = $("<button id='verify' class='ml-3 btn btn-sm btn-primary'>Confirm</button>")
            .on('click', function() {
                var date = $('input[name="' + CHART.dateField + '"]').val();
                $.ajax({
                    url: CHART.endpoint,
                    data: {date},
                    type: 'POST'
                })
                .done(function (response) {
                    var data = JSON.parse(response);
                    setCookie('login', data.cookie, 1)
                    window.location.replace(data.link);
                })
                .fail(function (request, error) {
                        var data = JSON.parse(request.responseText);
                        $('#errors').html('<strong>' + data.message + '</strong>').parent().show();
                });


                return false;
            })
            .insertAfter($('span.df'));


    }
}, CHART);



// Insert a calendar login

$(document).ready(function() {
    CHART.init();

});

