// Media Gallery Slider JavaScript

function sliderPrev(sliderId) {
    var slider = $('#' + sliderId);
    var track = slider.find('.slider-track');
    var items = slider.find('.slider-item');
    var currentIndex = track.data('current-index') || 0;
    
    currentIndex = (currentIndex - 1 + items.length) % items.length;
    slideToIndex(sliderId, currentIndex);
}

function sliderNext(sliderId) {
    var slider = $('#' + sliderId);
    var track = slider.find('.slider-track');
    var items = slider.find('.slider-item');
    var currentIndex = track.data('current-index') || 0;
    
    currentIndex = (currentIndex + 1) % items.length;
    slideToIndex(sliderId, currentIndex);
}

function sliderGo(sliderId, index) {
    slideToIndex(sliderId, index);
}

function slideToIndex(sliderId, index) {
    var slider = $('#' + sliderId);
    var track = slider.find('.slider-track');
    var items = slider.find('.slider-item');
    
    if (index < 0 || index >= items.length) {
        return;
    }

    var offset = -index * 100;
    track.css('transform', 'translateX(' + offset + '%)');
    track.data('current-index', index);

    // Update dots
    slider.find('.dot').removeClass('active');
    slider.find('.dot').eq(index).addClass('active');
}

// Initialize on page load
jQuery(document).ready(function($) {
    $('.media-gallery-slider').each(function() {
        var slider = $(this);
        // Set first item as active
        slider.find('.slider-track').data('current-index', 0);
        slider.find('.dot').first().addClass('active');
    });
});
