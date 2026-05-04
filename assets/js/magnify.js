// Magnify effect for WooCommerce product images
jQuery(document).ready(function($){
    // Find the first product image in WooCommerce single product gallery
    var $image = $('.woocommerce-product-gallery__image img').first();
    if(!$image.length) return;

    // Wrap image in magnify container
    $image.wrap('<div class="agenix-magnify-container"></div>');
    var $container = $image.parent();

    // Use WooCommerce's full-size image (data-large_image attribute)
    var fullSrc = $image.attr('data-large_image') || $image.attr('src');

    // Create lens with full-size image
    var $lens = $('<div class="agenix-magnify-lens"><img src="'+fullSrc+'" /></div>');
    $container.append($lens);

    var $lensImg = $lens.find('img');

    // Read zoom level from localized settings (default 2×)
    var zoomLevel = (typeof agenixMagnify !== 'undefined' && agenixMagnify.zoom) ? agenixMagnify.zoom : 2;


    // Scale lens image proportionally
    $lensImg.css({
        width: $image.width() * zoomLevel,
        maxWidth: 'none',
        maxHeight: 'none'
    });

    // Mouse move handler
    $container.on('mousemove', function(e){
        var rect = $container[0].getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;

        $lens.css({
            left: (x - $lens.width()/2) + 'px',
            top: (y - $lens.height()/2) + 'px',
            display: 'block'
        });

        var scaleX = $lensImg.width() / $image.width();
        var scaleY = $lensImg.height() / $image.height();

        $lensImg.css({
            left: -(x * scaleX - $lens.width()/2) + 'px',
            top: -(y * scaleY - $lens.height()/2) + 'px'
        });
    });

    // Hide lens when mouse leaves
    $container.on('mouseleave', function(){
        $lens.hide();
    });
});
