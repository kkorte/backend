function recalculate(el){

    var si = $('.tax-rate :selected').text();
    var tax         = parseFloat(si);

    el.value = el.value.replace(",",".");
    var percentage  = 1 + (tax / 100);

    switch (el.name) {
        case 'price_inc_tax':
            if(el.value) {
                var result = parseFloat(el.value) / percentage;
                result = Math.round(result*1000)/1000;
                $('.price').val(result.toFixed(4));
            } else {
                $('.price').val(0);
            }
            break;
        default:
        case 'price':
            if(el.value) {
                var result = parseFloat(el.value) * percentage;
                result = Math.round(result*100)/100;
                $('.price_inc_tax').val(result.toFixed(2));
            } else {
                $('.price_inc_tax').val(0);
            }
            break;
    }
}

$(document).ready(function() {
    
    $.fn.validator.Constructor.FOCUS_OFFSET = '0px';

    $('.counter').maxlength({
        alwaysShow: true,
        threshold: 10,
        warningClass: "label label-success",
        limitReachedClass: "label label-danger"
    });


    $("body").on("submit", ".delete-button", function(e){
        event.preventDefault();
        var $this = $(this);

        swal({
            title: "Delete item: " + $this.attr('data-title') + "?",
            text: "You will not be able to recover this iem",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            closeOnConfirm: false,
            html: false,
            imageUrl: null
        }, function(){
            $this.submit();
        });
    });


    $("body").on("change", ".print-input-select, .print-input-date", function(e){
       $('body .print-form').submit();
    });

    $("body").on("submit", ".print-form", function(e){
        var $this = $(this);
        e.preventDefault();
        var url = $(this).attr('data-url');
        $.post($this.attr('action'), $this.serialize(), function (data) {


            $.ajax({
                url: url,
                type: 'get',
                dataType: 'html',
                success: function (data) {
                    $('.selected-orders').html(data);
              
                }

            });
        });
    });


    if($( ".load-order-status-template" )) {
        var template = $( ".load-order-status-template" );
        var url = template.attr('data-url');

        if(url) {
            var orderStatusEmailTemplateId = $( ".order_status_email_template_id" );

            $.get(url + orderStatusEmailTemplateId.val(), {
          
            }, function(data){
                template.html(data.content);
            }, 'json');

        }

    }

    $("body").on("change", ".order_status_email_template_id", function(e){
        var template = $( ".load-order-status-template" );
        var url = template.attr('data-url');

        if(url) {
            var orderStatusEmailTemplateId = $( ".order_status_email_template_id" );



        $.get(url + orderStatusEmailTemplateId.val(), {
          
        }, function(data){
            template.html(data.content);
        }, 'json');

        }

    });

    $("body").on("click", ".change-active", function(e){
     
        var $this = $(this);
        var url = $(this).attr('data-url');


        $.get(url, {
          
        }, function(data){
        $('#datatable').DataTable().ajax.reload();
        }, 'html');

    });


    $("body").on("change", ".change-amount", function(e){
     
        var $this = $(this);

        if ($(this).val() == '') {
          return false;
        }

        var amount = $(this).val();

        var url = $(this).attr('data-url') + '/' + amount;
        $.get(url, {
          
        }, function(data){
        $('#datatable').DataTable().ajax.reload();
        }, 'html');

    });


    $("body").on("change", ".change-rank", function(e){
     
        var $this = $(this);

        if ($(this).val() == '') {
          return false;
        }

        var amount = $(this).val();

        var url = $(this).attr('data-url') + '/' + amount;
        $.get(url, {
          
        }, function(data){
        $('#datatable').DataTable().ajax.reload(null, false);
        }, 'html');

    });



    $("body").on("change", ".change-amount-product-attribute", function(e){
     
        var $this = $(this);

        if ($(this).val() == '') {
          return false;
        }

        var amount = $(this).val();

        var url = $(this).attr('data-url') + '/' + amount;
        $.get(url, {
          
        }, function(data){
        $('#datatable').DataTable().ajax.reload();
        }, 'html');

    });


    if(document.getElementById('codeeditor')){
        var editords = CodeMirror.fromTextArea(document.getElementById('codeeditor'), {
        lineNumbers: true
        });
    }


    $('.summernote').summernote({
      height: 300,                 // set editor height

      minHeight: null,             // set minimum height of editor
      maxHeight: null,             // set maximum height of editor

      focus: true,                 // set focus to editable area after initializing summernote
    });


    $('.price').trigger('keyup');

    $(".select2").select2();

    $('.datepicker').datepicker({

        format: 'dd/mm/yyyy'

    });

    $('.colorpicker').colorpicker({

    });

    
});



/*! DataTables Bootstrap 3 integration
 * Â©2011-2014 SpryMedia Ltd - datatables.net/license
 */

/**
 * DataTables integration for Bootstrap 3. This requires Bootstrap 3 and
 * DataTables 1.10 or newer.
 *
 * This file sets the defaults and adds options to DataTables to style its
 * controls using Bootstrap. See http://datatables.net/manual/styling/bootstrap
 * for further information.
 */
(function(window, document, undefined){

var factory = function( $, DataTable ) {
"use strict";


/* Set the defaults for DataTables initialisation */
$.extend( true, DataTable.defaults, {
    dom:
        "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    renderer: 'bootstrap'
} );


/* Default class modification */
$.extend( DataTable.ext.classes, {
    sWrapper:      "dataTables_wrapper form-inline dt-bootstrap",
    sFilterInput:  "form-control input-sm",
    sLengthSelect: "form-control input-sm"
} );


/* Bootstrap paging button renderer */
DataTable.ext.renderer.pageButton.bootstrap = function ( settings, host, idx, buttons, page, pages ) {
    var api     = new DataTable.Api( settings );
    var classes = settings.oClasses;
    var lang    = settings.oLanguage.oPaginate;
    var btnDisplay, btnClass, counter=0;

    var attach = function( container, buttons ) {
        var i, ien, node, button;
        var clickHandler = function ( e ) {
            e.preventDefault();
            if ( !$(e.currentTarget).hasClass('disabled') ) {
                api.page( e.data.action ).draw( false );
            }
        };

        for ( i=0, ien=buttons.length ; i<ien ; i++ ) {
            button = buttons[i];

            if ( $.isArray( button ) ) {
                attach( container, button );
            }
            else {
                btnDisplay = '';
                btnClass = '';

                switch ( button ) {
                    case 'ellipsis':
                        btnDisplay = '&hellip;';
                        btnClass = 'disabled';
                        break;

                    case 'first':
                        btnDisplay = lang.sFirst;
                        btnClass = button + (page > 0 ?
                            '' : ' disabled');
                        break;

                    case 'previous':
                        btnDisplay = lang.sPrevious;
                        btnClass = button + (page > 0 ?
                            '' : ' disabled');
                        break;

                    case 'next':
                        btnDisplay = lang.sNext;
                        btnClass = button + (page < pages-1 ?
                            '' : ' disabled');
                        break;

                    case 'last':
                        btnDisplay = lang.sLast;
                        btnClass = button + (page < pages-1 ?
                            '' : ' disabled');
                        break;

                    default:
                        btnDisplay = button + 1;
                        btnClass = page === button ?
                            'active' : '';
                        break;
                }

                if ( btnDisplay ) {
                    node = $('<li>', {
                            'class': classes.sPageButton+' '+btnClass,
                            'id': idx === 0 && typeof button === 'string' ?
                                settings.sTableId +'_'+ button :
                                null
                        } )
                        .append( $('<a>', {
                                'href': '#',
                                'aria-controls': settings.sTableId,
                                'data-dt-idx': counter,
                                'tabindex': settings.iTabIndex
                            } )
                            .html( btnDisplay )
                        )
                        .appendTo( container );

                    settings.oApi._fnBindAction(
                        node, {action: button}, clickHandler
                    );

                    counter++;
                }
            }
        }
    };

    // IE9 throws an 'unknown error' if document.activeElement is used
    // inside an iframe or frame. 
    var activeEl;

    try {
        // Because this approach is destroying and recreating the paging
        // elements, focus is lost on the select button which is bad for
        // accessibility. So we want to restore focus once the draw has
        // completed
        activeEl = $(document.activeElement).data('dt-idx');
    }
    catch (e) {}

    attach(
        $(host).empty().html('<ul class="pagination"/>').children('ul'),
        buttons
    );

    if ( activeEl ) {
        $(host).find( '[data-dt-idx='+activeEl+']' ).focus();
    }
};


/*
 * TableTools Bootstrap compatibility
 * Required TableTools 2.1+
 */
if ( DataTable.TableTools ) {
    // Set the classes that TableTools uses to something suitable for Bootstrap
    $.extend( true, DataTable.TableTools.classes, {
        "container": "DTTT btn-group",
        "buttons": {
            "normal": "btn btn-default",
            "disabled": "disabled"
        },
        "collection": {
            "container": "DTTT_dropdown dropdown-menu",
            "buttons": {
                "normal": "",
                "disabled": "disabled"
            }
        },
        "print": {
            "info": "DTTT_print_info"
        },
        "select": {
            "row": "active"
        }
    } );

    // Have the collection use a bootstrap compatible drop down
    $.extend( true, DataTable.TableTools.DEFAULTS.oTags, {
        "collection": {
            "container": "ul",
            "button": "li",
            "liner": "a"
        }
    } );
}

}; // /factory


// Define as an AMD module if possible
if ( typeof define === 'function' && define.amd ) {
    define( ['jquery', 'datatables'], factory );
}
else if ( typeof exports === 'object' ) {
    // Node/CommonJS
    factory( require('jquery'), require('datatables') );
}
else if ( jQuery ) {
    // Otherwise simply initialise as normal, stopping multiple evaluation
    factory( jQuery, jQuery.fn.dataTable );
}


})(window, document);