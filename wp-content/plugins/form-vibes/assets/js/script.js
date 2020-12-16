jQuery(document).ready(function(jQuery){
    jQuery('.fv-dashboard-toggler').live('click',function(){
        jQuery(this).parent().children().toggle();  //swaps the display:none between the two spans
        jQuery(this).parent().parent().parent().find('.fv-dash-totalEntries-content').slideToggle();  //swap the display of the main content with slide action

    });


    jQuery('.page-title-action:first').on('click',function (event) {
        event.preventDefault();
        var box = jQuery(document).find('#fv_add_new_box');
        tb_show("Select New Post Type", "#TB_inline?inlineId=fv_add_new_box");
    });

    jQuery('#fv_new_template_next').on('click',function (event) {
        event.preventDefault();
        var d_type = jQuery("[name='d_type']:checked").val();

        jQuery.ajax({
            type: 'POST',
            url: fvGlobalVar.ajax_url,
            data: {
                action : 'fv_add_new_post',
                d_type: d_type,
            },
        })
            .done(function(data) {
                window.location = data.edit_link;
            }.bind(this))
            .fail(function(jqXhr) {
            });
    });

    var jQuerytoggle = jQuery('.fv-toggle-controls');
    //var jQuerytoggle = jQuery('#fv-configure');

    jQuery.each(jQuerytoggle,function(index,value){
        jQuery(value).append('<i class="dashicons dashicons-admin-generic"></i>');
        jQuery(value).parent().parent().prev().append(value);
        jQuery(value).show().click(function(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            jQuery(this).parent().toggleClass('controlVisible');
            jQuery('#fv-db-controls-'+index).slideToggle();
        });
    });

});



jQuery(document).ready(checkContainer);
    function checkContainer () {

        // use dynamic binding divs name here
        if (jQuery('.shortcode-tab').is(':visible') || jQuery('.gsheet-tab').is(':visible') || jQuery('.fv-box-content-wrapper').is(':visible')){

            const parent = jQuery('.fv_tab_container').attr('id');
            const back =jQuery('#'+parent).find("#btnPrevious");
            const next = jQuery('#'+parent).find("#btnNext");
            const tabs = jQuery('#'+parent).find(".tab-pane");
            const navLinks = jQuery('#'+parent).find(".nav-link");
            const progressBar = jQuery('#'+parent).find('.progress-bar');
            const submit = jQuery('#'+parent).find('#btn_submit');
            // initial with of progress bar.
            progressBar.css('width', ((100/navLinks.length)+'%'));

            if(jQuery(".fv_tab_container").find(".style-content").length > 0){
                function sticky_relocate() {
                    var window_top = jQuery(window).scrollTop();
                    var div_top = jQuery('#fv-sticky-anchor').offset().top;
                    if (window_top > div_top) {
                        jQuery('#fv-sticky').addClass('stick');
                        jQuery('#fv-previewHead').addClass('livePreviewHeading');
                    } else {
                        jQuery('#fv-sticky').removeClass('stick');
                        jQuery('#fv-previewHead').removeClass('livePreviewHeading');
                    }
                }

                jQuery(function() {
                    jQuery(window).scroll(sticky_relocate);
                    sticky_relocate();
                });
            }

            next.bind("click", function() {
                back.prop('disabled', false);
                back.removeClass("disabled");

                jQuery.each( navLinks, function( i ) {
                    if (!jQuery(navLinks[i]).hasClass('active') && !jQuery(navLinks[i]).hasClass('done')) {
                        progressBar.css('width', ((100/navLinks.length) * (i+1))+'%');


                        jQuery(tabs[i]).addClass('active');
                        jQuery(tabs[i - 1]).removeClass('active').addClass('done');

                        jQuery(navLinks[i]).addClass('active');
                        jQuery(navLinks[i - 1]).removeClass('active').addClass('done');

                        if(tabs.length === (i+1)){
                            next.prop('disabled', true);
                            next.addClass("disabled");
                            next.hide();

                            submit.show();

                        }else{
                            next.prop('disabled', false);
                            next.removeClass("disabled");
                            next.show();

                            submit.hide();
                        }

                        return false;
                    }
                })
            });

            back.bind("click", function() {
                next.prop('disabled', false);
                next.removeClass("disabled");
                next.show();
                submit.hide();
                jQuery.each( navLinks, function( i ) {
                    if (jQuery(navLinks[i]).hasClass('done') && jQuery(navLinks[i + 1]).hasClass('active')) {

                        progressBar.css('width', ((100/navLinks.length) * (i+1))+'%');

                        jQuery(tabs[i + 1]).removeClass('active');
                        jQuery(tabs[i]).removeClass('done').addClass('active');

                        jQuery(navLinks[i + 1]).removeClass('active');
                        jQuery(navLinks[i]).removeClass('done').addClass('active');

                        if(i === 0){
                            back.prop('disabled', true);
                            back.addClass("disabled");
                        }else{
                            back.prop('disabled', false);
                            back.removeClass("disabled");
                        }


                        return false;
                    }
                })
            });

            if(jQuery(".fv_tab_container").find("#PreviewTable").length > 0){
                let bInfo = jQuery('#PreviewTable').data('binfo');
                let bPaginate = jQuery('#PreviewTable').data('bpaginate');


                jQuery(document).ready( function () {
                    jQuery('#PreviewTable').DataTable({
                        "order": [[ 1, 'desc' ]],
                        "bInfo": bInfo,
                        "bPaginate": bPaginate
                    });
                } );
            }

            // js for inner tabs
            jQuery('.nav-link-inner').on('click', function (e) {
                e.preventDefault();
                const target = jQuery(this).attr('href');

                jQuery(".fv_tab_container").find(target).addClass('active');

                jQuery(".fv_tab_container").find(target).siblings().removeClass('active');

                jQuery(this).addClass('active').removeClass('done');

                jQuery(this).parent().nextAll('.nav-item').find('.nav-link-inner').removeClass('done').removeClass('active');

                jQuery(this).parent().prevAll('.nav-item').find('.nav-link-inner').addClass('done').removeClass('active');

            });

            //js for outer main tabs.
            jQuery('.nav-link').on('click', function (e) {
            e.preventDefault();
            const target = jQuery(this).attr('href');

            for (let i=0; i<navLinks.length; i++){
                if(navLinks[i] === jQuery(this)[0]){
                    progressBar.css('width', ((100/navLinks.length) * (i+1))+'%');
                }
            }

            jQuery(".fv_tab_container").find(target).addClass('active');
            jQuery(".fv_tab_container").find(target).siblings().removeClass('active');

            jQuery(this).addClass('active').removeClass('done');

            jQuery(this).parent().nextAll('.nav-item').find('.nav-link').removeClass('done').removeClass('active');

            jQuery(this).parent().prevAll('.nav-item').find('.nav-link').addClass('done').removeClass('active');


                if(jQuery(this).attr('href') == navLinks.first()[0].hash){
                    //jQuery(".fv_tab_container").find(target).nextAll().removeClass('done');
                    back.prop('disabled', true);
                    back.addClass("disabled");

                    next.prop('disabled', false);
                    next.removeClass("disabled");
                    next.show();

                    submit.hide();
                }
                else if(jQuery(this).attr('href') == navLinks.last()[0].hash){
                    next.prop('disabled', true);
                    next.addClass("disabled");
                    next.hide();

                    back.prop('disabled', false);
                    back.removeClass("disabled");

                    submit.show();

                }
                else{
                    back.prop('disabled', false);
                    back.removeClass("disabled");

                    next.prop('disabled', false);
                    next.removeClass("disabled");
                    next.show();
                    submit.hide();
                }
            jQuery(target).fadeIn(600);

            });


            /*const parent = jQuery(this).closest('ul').attr('id');
            const back =jQuery("'#" + parent + "'").find("#btnPrevious");
            const next = jQuery("'#" + parent + "'").find("#btnNext");
            const tabs = jQuery("'#" + parent + "'").find(".tab-pane");
            const navLinks = jQuery("'#" + parent + "'").find(".nav-link");
            const progressBar = jQuery("'#" + parent + "'").find('.progress-bar');

            console.log('parent -> ', parent);
            console.log('next -> ', navLinks);

            if(jQuery(".fv_tab_container").find(".style-content").length > 0){
                function sticky_relocate() {
                    var window_top = jQuery(window).scrollTop();
                    var div_top = jQuery('#fv-sticky-anchor').offset().top;
                    if (window_top > div_top) {
                        jQuery('#fv-sticky').addClass('stick');
                        jQuery('#fv-previewHead').addClass('livePreviewHeading');
                    } else {
                        jQuery('#fv-sticky').removeClass('stick');
                        jQuery('#fv-previewHead').removeClass('livePreviewHeading');
                    }
                }

                jQuery(function() {
                    jQuery(window).scroll(sticky_relocate);
                    sticky_relocate();
                });
            }

            next.bind("click", function() {
                back.prop('disabled', false);
                back.removeClass("disabled");

                jQuery.each( navLinks, function( i ) {
                    if (!jQuery(navLinks[i]).hasClass('active') && !jQuery(navLinks[i]).hasClass('done')) {
                        progressBar.css('width', ((100/navLinks.length) * (i+1))+'%');


                        jQuery(tabs[i]).addClass('active');
                        jQuery(tabs[i - 1]).removeClass('active').addClass('done');

                        jQuery(navLinks[i]).addClass('active');
                        jQuery(navLinks[i - 1]).removeClass('active').addClass('done');

                        if(tabs.length === (i+1)){
                            next.prop('disabled', true);
                            next.addClass("disabled");
                        }else{
                            next.prop('disabled', false);
                            next.removeClass("disabled");
                        }

                        return false;
                    }
                })
            });

            back.bind("click", function() {
                next.prop('disabled', false);
                next.removeClass("disabled");
                jQuery.each( navLinks, function( i ) {
                    if (jQuery(navLinks[i]).hasClass('done') && jQuery(navLinks[i + 1]).hasClass('active')) {

                        progressBar.css('width', ((100/navLinks.length) * (i+1))+'%');

                        jQuery(tabs[i + 1]).removeClass('active');
                        jQuery(tabs[i]).removeClass('done').addClass('active');

                        jQuery(navLinks[i + 1]).removeClass('active');
                        jQuery(navLinks[i]).removeClass('done').addClass('active');

                        if(i === 0){
                            back.prop('disabled', true);
                            back.addClass("disabled");
                        }else{
                            back.prop('disabled', false);
                            back.removeClass("disabled");
                        }


                        return false;
                    }
                })
            });

            if(jQuery(".fv_tab_container").find("#PreviewTable").length > 0){
                let bInfo = jQuery('#PreviewTable').data('binfo');
                let bPaginate = jQuery('#PreviewTable').data('bpaginate');


                jQuery(document).ready( function () {
                    jQuery('#PreviewTable').DataTable({
                        "order": [[ 1, 'desc' ]],
                        "bInfo": bInfo,
                        "bPaginate": bPaginate
                    });
                } );
            }

            // js for inner tabs
            jQuery('.nav-link-inner').on('click', function (e) {
                e.preventDefault();
                const target = jQuery(this).attr('href');

                jQuery(".fv_tab_container").find(target).addClass('active');

                jQuery(".fv_tab_container").find(target).siblings().removeClass('active');

                jQuery(this).addClass('active').removeClass('done');

                jQuery(this).parent().nextAll('.nav-item').find('.nav-link-inner').removeClass('done').removeClass('active');

                jQuery(this).parent().prevAll('.nav-item').find('.nav-link-inner').addClass('done').removeClass('active');

            });

            //js for outer main tabs.
            jQuery('.nav-link').on('click', function (e) {
            e.preventDefault();
            const target = jQuery(this).attr('href');

            for (let i=0; i<navLinks.length; i++){
                if(navLinks[i] === jQuery(this)[0]){
                    progressBar.css('width', ((100/navLinks.length) * (i+1))+'%');
                }
            }

            jQuery(".fv_tab_container").find(target).addClass('active');
            jQuery(".fv_tab_container").find(target).siblings().removeClass('active');

            jQuery(this).addClass('active').removeClass('done');

            jQuery(this).parent().nextAll('.nav-item').find('.nav-link').removeClass('done').removeClass('active');

            jQuery(this).parent().prevAll('.nav-item').find('.nav-link').addClass('done').removeClass('active');


            console.log('navlink -> ', navLinks);

                if(jQuery(this).attr('href') == navLinks.first()[0].hash){
                    //jQuery(".fv_tab_container").find(target).nextAll().removeClass('done');
                    console.log('first if');
                    back.prop('disabled', true);
                    back.addClass("disabled");

                    next.prop('disabled', false);
                    next.removeClass("disabled");
                }
                else if(jQuery(this).attr('href') == navLinks.last()[0].hash){
                    console.log('last if');

                    next.prop('disabled', true);
                    next.addClass("disabled");

                    back.prop('disabled', false);
                    back.removeClass("disabled");
                }
                else{
                    console.log('last else');
                    back.prop('disabled', false);
                    back.removeClass("disabled");

                    next.prop('disabled', false);
                    next.removeClass("disabled");
                }
            jQuery(target).fadeIn(600);

            });*/

        }else{
            setTimeout(checkContainer, 50); //wait 50 ms, then try again
        }

        // for style tab content

        jQuery('.form').find('input, textarea').on('keyup blur focus', function (e) {

            var jQuerythis = jQuery(this),
                label = jQuerythis.prev('label');

            if (e.type === 'keyup') {
                if (jQuerythis.val() === '') {
                    label.removeClass('active highlight');
                } else {
                    label.addClass('active highlight');
                }
            } else if (e.type === 'blur') {
                if( jQuerythis.val() === '' ) {
                    label.removeClass('active highlight');
                } else {
                    label.removeClass('highlight');
                }
            } else if (e.type === 'focus') {

                if( jQuerythis.val() === '' ) {
                    label.removeClass('highlight');
                }
                else if( jQuerythis.val() !== '' ) {
                    label.addClass('highlight');
                }
            }

        });

        jQuery('.inner-tab a').on('click', function (e) {

            e.preventDefault();

            jQuery(this).parent().addClass('active');
            jQuery(this).parent().siblings().removeClass('active');

            target = jQuery(this).attr('href');

            jQuery('#'+jQuery(this)[0].name+' .inner-tab-content > div').not(target).hide();

            jQuery(target).fadeIn(600);

        });

}