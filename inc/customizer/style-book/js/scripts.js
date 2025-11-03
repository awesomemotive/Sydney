/**
 * Initialize the Sydney Style Book functionality.
*/
( function( $ ) {
    $( document ).ready( function() {
        // Open the Style Book
        $( '.sydney-style-book-toggle' ).on( 'click', function(e) {
            e.preventDefault();
            var $styleBook = $( '.sydney-style-book' );

            if ( $styleBook.length && $styleBook.is(':visible') ) {
                // Hide instead of removing to preserve state
                $styleBook.hide();
            } else if ( $styleBook.length ) {
                // Show existing Style Book to preserve live preview state
                $styleBook.show();
            } else {
                // Create Style Book only if it doesn't exist
                var template = wp.template( 'sydney-style-book' );
                $( 'body' ).append( template );
                $styleBook = $( '.sydney-style-book' );
                
                //Close button - use hide instead of remove
                $( '.sydney-style-book-close' ).on( 'click', function() {
                    $styleBook.hide();
                } );
            }
        } );

    } );
} )( jQuery );

/**
 * Handle live previews for the Style Book.
 */
( function( $ ) {
    //Global colors
    for (let i = 1; i <= 9; i++) {
        wp.customize(`global_color_${i}`, function( value ) {
            value.bind( function( newval ) {
                $( `.sydney-style-book-color-value[data-color-setting="global_color_${i}"]` ).css( 'background-color', newval );
            } );
        });
    }

    //Heading colors
    for (let i = 1; i <= 6; i++) {
        wp.customize(`color_heading_${i}`, function( value ) {
            value.bind( function( newval ) {
                $( `.sydney-style-book h${i}.style-book-heading` ).css( 'color', newval );
            } );
        });
    }

    //Text colors
    wp.customize('body_text_color', function( value ) {
        value.bind( function( newval ) {
            $( '.sydney-style-book .style-book-body' ).css( 'color', newval );
        } );
    });

    //Button colors
    var buttonColors = { 'button_background_color': 'background-color', 'button_color': 'color', 'button_border_color': 'border-color' };
    $.each( buttonColors, function( option, prop ) {
        wp.customize(option, function( value ) {
            value.bind( function( newval ) {
                $( `.sydney-style-book .roll-button` ).css( prop, newval );
            } );
        });
    } );

    //Heading typography
	wp.customize( 'sydney_headings_font', function( value ) {
		value.bind( function( to ) {

			$( 'head' ).find( '#sydney-preview-style-book-google-fonts-headings-css' ).remove();
			$( 'head' ).find( '#sydney-preview-style-book-headings-weight-css' ).remove();

			$( 'head' ).append( '<link id="sydney-preview-style-book-google-fonts-headings-css" href="" rel="stylesheet">' );

			$( '#sydney-preview-style-book-google-fonts-headings-css' ).attr( 'href', 'https://fonts.googleapis.com/css?family=' + jQuery.parseJSON( to )['font'].replace(/ /g, '+') + ':' + jQuery.parseJSON( to )['regularweight'] + '&display=swap' );

			$( '.style-book-heading' ).css( 'font-family', jQuery.parseJSON( to )['font'] );

            $( '.style-book-headings-typography-data' ).find( '.style-book-typography-family' ).html( jQuery.parseJSON( to )['font'] );
            $( '.style-book-headings-typography-data' ).find( '.style-book-typography-weight' ).html( jQuery.parseJSON( to )['regularweight'] );

			$( 'head' ).append('<style id="sydney-preview-style-book-headings-weight-css" type="text/css">.style-book-heading {font-weight:' + jQuery.parseJSON( to )['regularweight'] + ';}</style>');

		} );
	} );

    var headingsTypography = { 
        'headings_line_height': 'line-height',
        'headings_letter_spacing': 'letter-spacing',
        'headings_text_transform': 'text-transform',
        'menu_items_text_transform': 'text-transform',
        'headings_text_decoration': 'text-decoration'
    };

    $.each( headingsTypography, function( option, prop ) {
        wp.customize(option, function( value ) {
            value.bind( function( newval ) {
                $( '.style-book-heading' ).css( prop, newval );
            } );
        });
    });

    // Heading font sizes
    var headingFontSizes = {
        'h1_font_size_desktop': 'h1.style-book-heading',
        'h2_font_size_desktop': 'h2.style-book-heading',
        'h3_font_size_desktop': 'h3.style-book-heading',
        'h4_font_size_desktop': 'h4.style-book-heading',
        'h5_font_size_desktop': 'h5.style-book-heading',
        'h6_font_size_desktop': 'h6.style-book-heading'
    };

    $.each(headingFontSizes, function(option, selector) {
        wp.customize(option, function(value) {
            value.bind(function(newval) {
                $(selector).css('font-size', newval + 'px');
                $( '.style-book-headings-typography-data[data-heading="' + selector.replace('.style-book-heading', '') + '"]' ).find( '.style-book-typography-size' ).html( newval + 'px' );
            });
        });
    });

    //Body typography
    wp.customize('sydney_body_font', function( value ) {
        value.bind( function( to ) {

            $( 'head' ).find( '#sydney-preview-style-book-google-fonts-body-css' ).remove();
            $( 'head' ).find( '#sydney-preview-style-book-body-weight-css' ).remove();

            $( 'head' ).append( '<link id="sydney-preview-style-book-google-fonts-body-css" href="" rel="stylesheet">' );

            $( '#sydney-preview-style-book-google-fonts-body-css' ).attr( 'href', 'https://fonts.googleapis.com/css?family=' + jQuery.parseJSON( to )['font'].replace(/ /g, '+') + ':' + jQuery.parseJSON( to )['regularweight'] + '&display=swap' );

            $( '.sydney-style-book .style-book-body' ).css( 'font-family', jQuery.parseJSON( to )['font'] );
            $( '.style-book-body-typography-data' ).find( '.style-book-typography-family' ).html( jQuery.parseJSON( to )['font'] );
            $( '.style-book-body-typography-data' ).find( '.style-book-typography-weight' ).html( jQuery.parseJSON( to )['regularweight'] );

            $( 'head' ).append('<style id="sydney-preview-style-book-body-weight-css" type="text/css">.sydney-style-book .style-book-body {font-weight:' + jQuery.parseJSON( to )['regularweight'] + ';}</style>');

        } );
    });

    var bodyTypography = { 
        'body_font_size_desktop': 'font-size',
        'body_font_style': 'font-style',
        'body_line_height': 'line-height',
        'body_letter_spacing': 'letter-spacing',
        'body_text_transform': 'text-transform',
        'body_text_decoration': 'text-decoration'
    };

    $.each( bodyTypography, function( option, prop ) {
        wp.customize(option, function( value ) {
            value.bind( function( newval ) {
                suffix = (prop === 'font-size' || prop === 'letter-spacing') ? 'px' : '';
                
                $( '.sydney-style-book .style-book-body' ).css( prop, newval + suffix );

                if ( option === 'body_font_size_desktop' ) { 
                    $( '.style-book-body-typography-data' ).find( '.style-book-typography-size' ).html( newval + suffix );
                }
            } );
        });
    });

    //Images
    wp.customize('image_border_radius', function( value ) {
        value.bind( function( newval ) {
            $( '.sydney-style-book img' ).css( 'border-radius', newval + 'px' );
        } );
    });

    wp.customize('image_caption_font_size', function( value ) {
        value.bind( function( newval ) {
            $( '.sydney-style-book figcaption' ).css( 'font-size', newval + 'px' );
        } );
    });

    wp.customize('image_caption_color', function( value ) {
        value.bind( function( newval ) {
            $( '.sydney-style-book figcaption' ).css( 'color', newval );
        } );
    });

} )( jQuery );