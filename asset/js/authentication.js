CHART = {
    endpoint: "",
    recordId: "",

    init: function () {

        // // suppress form submission
        // $('#form').submit(false);
        //
        //
        // this.dateField = $('input[name="' + this.dateField + '"]');
        //
        // var btn = $("<button id='verify' class='ml-3 btn btn-sm btn-primary'>Confirm</button>")
        //     .on('click', function() {
        //         var date = $('input[name="' + CHART.dateField + '"]').val();
        //         $.ajax({
        //             url: CHART.endpoint,
        //             data: {date},
        //             type: 'POST'
        //         })
        //         .done(function (response) {
        //             var data = JSON.parse(response);
        //             setCookie('login', data.cookie, 1)
        //             window.location.replace(data.link);
        //         })
        //         .fail(function (request, error) {
        //                 var data = JSON.parse(request.responseText);
        //                 $('#errors').html('<strong>' + data.message + '</strong>').parent().show();
        //         });
        //
        //
        //         return false;
        //     })
        //     .insertAfter($('span.df'));


        setTimeout(function () {
            new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
        }, 500);

        // delete submit element
        setTimeout(function(){
            $(document).find('.surveysubmit').remove()
        }, 100)

        $(document).on('click', '#verify-user', function (e) {
            e.stopPropagation();
            e.preventDefault();
            e.stopImmediatePropagation();
            var elem = $('input[name="verification_field"]');
            var value = $(elem).val();
            $.ajax({
                url: CHART.endpoint,
                data: {verification_field: value, record_id: CHART.recordId},
                type: 'POST'
            })
                .done(function (response) {
                    var data = JSON.parse(response);
                    //setCookie('login', data.cookie, 1)
                    window.location.replace(data.link);
                })
                .fail(function (request, error) {
                    var data = JSON.parse(request.responseText);
                    $('#surveyinstructions').append('<div class="alert-danger alert">' + data.message + '</div>').parent().show();
                });
        });

    }
};


// Insert a calendar login
CHART.init();

