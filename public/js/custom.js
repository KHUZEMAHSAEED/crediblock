(function($) {
    'use strict';
    // Function to trigger click on enter
    $('#url').keypress(function(e) {
        var key = e.which;
        if (key == 13) // the enter key code
        {
            $('#scrapperbtn').click();
            return false;
        }
    });

    // Function to render data on the screen
    jQuery(document).on('click', '#scrapperbtn', function() {
        let x = $("#url").val();
        $("#categories_dropdown").val($("#categories_dropdown option:first").val());
        if ($("#multisite_dropdown").length) {
            $("#multisite_dropdown").val($("#multisite_dropdown option:first").val());
        }
        // Check if url input is empty
        if (x == '' || x == null) {
            $('#error_1').show().delay(3000).fadeOut();
            $("#rendered_data").html('');
            $('#post_create').hide();
            $("#categories_dropdown").val($("#categories_dropdown option:first").val());
            if ($("#multisite_dropdown").length) {
                $("#multisite_dropdown").val($("#multisite_dropdown option:first").val());
            }
            return false;
        }
        // If url doesnt contain https:// include it by default
        if (x.includes('http')) {} else {
            x = 'https://' + x;
        }
        // Check Validation afterwards
        var url_validation = /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
        var url_validation1 = /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
        // If validation with https is success
        if (url_validation.test(x)) {
            $("#ajaxSpinnerImage").show();
            $('#post_create').hide();
            $("#rendered_data").html('');
            jQuery.ajax({
                type: "POST",
                url: obj.ajaxurl,
                data: {
                    url_1: x,
                    action: 'scrape_data_php',
                }
            }).done(function(msg) {
                if (msg == 'error') {
                    $("#ajaxSpinnerImage").hide();
                    $("#error_2").show().delay(3000).fadeOut();
                    $('#post_create').hide();

                } else {

                    var title = $(msg).filter('title').text();

                    $("#ajaxSpinnerImage").hide();
                    $("#rendered_data").html(msg);

                    $('#rendered_data').prepend('<h1>' + title + '</h1>');

                    $('#rendered_data').find('div').each(function(i, elm) {
                        if ($(this).text() == 'Expand') {
                            $(this).wrap('<p></p>');
                        }
                    });
                    // $('#rendered_data').prepend('<h2 id="main_div" class="hr_hover">Plain text</h2>').prepend('<span class="dashicons dashicons-no icon_class"></span>');
                    $('#post_create').show();
                    if ($('#categories_dropdown').has('option').length > 0) {
                        $('#categories_dropdown').show();
                    } else {
                        $('#categories_dropdown').hide();
                    }
                    $("#rendered_data").find('h1, h2, h3, h4, h5, h6, div, span, p, li').filter(function(i, elem) {

                        for (elem = elem.firstChild; elem; elem = elem.nextSibling) {
                            if (elem.nodeType < 6) {
                                return false;
                            }
                        }
                        return true;

                    }).hide();

                    $('#rendered_data a').contents().unwrap().wrap('<p></p>');
                    $('#rendered_data img').wrap('<p></p>');
                    $('#rendered_data li').contents().unwrap().wrap('<p></p>');
                    $('#rendered_data ul').contents().unwrap().wrap('<p></p>');

                    $("#rendered_data span").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data div").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data p").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data a").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h1").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h2").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h3").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h4").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h5").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h6").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $('#rendered_data ').find('h1, h2, h3, h4, h5, h6').append('<hr style="height:2px;border-width:0;color:gray;background-color:gray"/>').css('margin-top', '2%').attr('heading', 'custom');

                    $('#rendered_data').find('span, p, h1, h2, h3, h4, h5, h6').addClass('hr_hover');
                    $('#rendered_data').find('span, p, h1, h2, h3, h4, h5, h6').prepend('<span class="dashicons dashicons-no icon_class"></span>');

                    $("#rendered_data p").children().find('span').removeClass('dashicons dashicons-no icon_class');
                    $("#rendered_data span").children().find('span').removeClass('dashicons dashicons-no icon_class');

                }
            });
        }
        //validation if http is success
        else if (url_validation1.test(x)) {
            $("#ajaxSpinnerImage").show();
            $('#post_create').hide();
            $("#rendered_data").html('');
            jQuery.ajax({
                type: "POST",
                url: obj.ajaxurl,
                data: {
                    url_1: x,
                    action: 'scrape_data_php',
                }
            }).done(function(msg) {
                if (msg == 'error') {
                    $("#ajaxSpinnerImage").hide();
                    $("#error_2").show().delay(3000).fadeOut();
                    $('#post_create').hide();

                } else {
                    $("#ajaxSpinnerImage").hide();
                    var title = $(msg).filter('title').text();
                    // $('#rendered_data').prepend('<h2 id="main_div" class="hr_hover">Plain text</h2>').prepend('<span class="dashicons dashicons-no icon_class"></span>');
                    $("#rendered_data").html(msg);
                    $('#rendered_data').prepend('<h1>' + title + '</h1>');

                    $('#rendered_data').find('div').each(function(i, elm) {
                        if ($(this).text() == 'Expand') {
                            $(this).wrap('<p></p>');
                        }
                    });
                    $('#post_create').show();
                    if ($('#categories_dropdown').has('option').length > 0) {
                        $('#categories_dropdown').show();
                    } else {
                        $('#categories_dropdown').hide();
                    }
                    // Hide Empty HTML tags
                    $("#rendered_data").find('h1, h2, h3, h4, h5, h6, div, span, p, li').filter(function(i, elem) {

                        for (elem = elem.firstChild; elem; elem = elem.nextSibling) {
                            if (elem.nodeType < 6) {
                                return false;
                            }
                        }
                        return true;

                    }).hide();

                    $('#rendered_data a').contents().unwrap().wrap('<p></p>');
                    $('#rendered_data img').wrap('<p></p>');
                    $('#rendered_data li').contents().unwrap().wrap('<p></p>');
                    $('#rendered_data ul').contents().unwrap().wrap('<p></p>');

                    $("#rendered_data span").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data div").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data p").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data a").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h1").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h2").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h3").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h4").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h5").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $("#rendered_data h6").each(function() {
                        if ($(this).html().trim() == "") {
                            $(this).remove();
                        }
                    });
                    $('#rendered_data ').find('h1, h2, h3, h4, h5, h6').append('<hr style="height:2px;border-width:0;color:gray;background-color:gray"/>').css('margin-top', '2%').attr('heading', 'custom');

                    $('#rendered_data').find('p, h1, h2, h3, h4, h5, h6').addClass('hr_hover');
                    $('#rendered_data').find('p, h1, h2, h3, h4, h5, h6').prepend('<span class="dashicons dashicons-no icon_class"></span>');

                    $("#rendered_data p").children().find('span').removeClass('dashicons dashicons-no icon_class');
                    // $('#rendered_data div').children().children().children().children().find('div').addClass('hr_hover').prepend('<span class="dashicons dashicons-no icon_class"></span>');
                }
            });
        }
        // Invalid url
        else {
            $('#error_2').show().delay(3000).fadeOut();
            $("#rendered_data").html('');
            $('#post_create').hide();
            $("#categories_dropdown").val($("#categories_dropdown option:first").val());
            if ($("#multisite_dropdown").length) {
                $("#multisite_dropdown").val($("#multisite_dropdown option:first").val());
            }
        }
    });
    // Function to save data in posts
    jQuery(document).on('click', '#save_post', function() {
        $("#ajaxSpinnerImage_1").show();
        $('#rendered_data span').removeClass('dashicons dashicons-no icon_class');
        $('#rendered_data').find('p, h1, h2, h3, h4, h5, h6, span').removeClass('hr_hover');
        // Remove Empty Tags
        $('#rendered_data p:empty').remove();
        $('#rendered_data span:empty').remove();
        $('#rendered_data div:empty').remove();
        $('#rendered_data h1:empty').remove();
        $('#rendered_data h2:empty').remove();
        $('#rendered_data h3:empty').remove();
        $('#rendered_data h4:empty').remove();
        $('#rendered_data h5:empty').remove();
        $('#rendered_data h6:empty').remove();
        // Remove Empty Tags Repeat so that each and every empty tag is removed
        $('#rendered_data p:empty').remove();
        $('#rendered_data span:empty').remove();
        $('#rendered_data div:empty').remove();
        $('#rendered_data h1:empty').remove();
        $('#rendered_data h2:empty').remove();
        $('#rendered_data h3:empty').remove();
        $('#rendered_data h4:empty').remove();
        $('#rendered_data h5:empty').remove();
        $('#rendered_data h6:empty').remove();
        // Remove Empty Tags Repeat so that each and every empty tag is removed
        $('#rendered_data p:empty').remove();
        $('#rendered_data span:empty').remove();
        $('#rendered_data div:empty').remove();
        $('#rendered_data h1:empty').remove();
        $('#rendered_data h2:empty').remove();
        $('#rendered_data h3:empty').remove();
        $('#rendered_data h4:empty').remove();
        $('#rendered_data h5:empty').remove();
        $('#rendered_data h6:empty').remove();

        let site1 = '';
        if ($("#multisite_dropdown").length) {

            let check_site = $('select#multisite_dropdown option').filter(':selected').val();

            if (check_site.indexOf('Please select a site to post') > -1) {

                site1 = '';
            } else {

                site1 = check_site;
            }

        } else {

            site1 = '';
        }
        jQuery.ajax({
            type: "POST",
            url: obj.ajaxurl,
            data: {
                html_1: $('#rendered_data').html(),
                cats: $("select#categories_dropdown option").filter(":selected").val(),
                site: site1,
                title_1: $('#rendered_data title').html(),
                action: 'save_as_post',
                site_url_text: $("#url").val(),
            }
        }).done(function(msg) {
            $("#ajaxSpinnerImage_1").hide();
            $("#success_msg").show().delay(3000).fadeOut();
            $('#url').val('');
            $('#post_create').hide();
            $("#rendered_data").html('');
            $("#categories_dropdown").val($("#categories_dropdown option:first").val());
            if ($("#multisite_dropdown").length) {
                $("#multisite_dropdown").val($("#multisite_dropdown option:first").val());
            }
        });
    });

    jQuery(document).on('click', '.icon_class', function() {

        jQuery(this).parent().remove();
    });


    // Get Embed code
    jQuery(document).on('click', '#embed_code', function() {
        jQuery.ajax({
            type: "POST",
            url: obj.ajaxurl,
            data: {
                site_url_text: $("#url").val(),
                action: 'embed_code',
            }
        }).done(function(msg) {
            if (msg != 'error') {
                $("#rendered_data").html('<iframe width="640" height="360" src="' + msg + '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>');
                $('#post_create').show();

            } else {
                $("#ajaxSpinnerImage").hide();
                $("#error_4").show().delay(3000).fadeOut();
                $('#post_create').hide();
                $('#rendered_data').html('');

            }
        });
    });

})(jQuery);