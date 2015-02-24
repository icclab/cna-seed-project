var highestZIndex,
    leftAnimation,
    animationTime,
    easingType,
    carouselWidth,
    viewfinderWidth,
    maxLeft,
    maxRight,
    myLeft,
    fixedStep,
    firstVisible,
    numPanels,
    numToScroll;

function getCurrentVisibleCollections(direction, firstRun){
    if( firstVisible >= 1 && firstVisible <= (numPanels + 1) - numToScroll || firstRun === true ){
        $('.gd-collection-panel').removeClass('visible-panel');
        $('.gd-collection-panel').removeClass('visible-panel-last');
        var step = fixedStep;
        myLeft = parseInt($('#gd-carousel').css('margin-left'));
        if(direction === 'forward'){
            if((myLeft - step) <= maxLeft){
                step = maxLeft;
                currentLeftMargin = maxLeft;
            } else {
                step = '-=' + fixedStep.toString();
            }
            firstVisible++;
        }
        if(direction === 'back'){
            if( (myLeft + step) > maxRight){
                step = 0;
                currentLeftMargin = 0;
            } else {
                step = '+=' + fixedStep.toString();
            }
            firstVisible--;
        }
        if(firstVisible === 0){
            firstVisible = 1;
        }
        if(firstVisible > (numPanels + 1) - numToScroll){
            firstVisible = (numPanels + 1) - 4;
        }
        if(firstRun !== true){
            $('#gd-carousel').stop( true, true ).animate({ marginLeft : step.toString() }, animationTime, easingType);
        }
        var i;
        for (i = firstVisible; i < firstVisible + numToScroll; i++){
            $( '.gd-collection-panel:nth-child(' + i + ')').addClass('visible-panel');
        }
        $( '.gd-collection-panel:nth-child(' + (i-1) + ')').addClass('visible-panel-last');
    }
}

$(window).ready(function(){

    highestZIndex = 100;
    leftAnimation = 0;
    animationTime = 425;
    easingType = 'easeOutQuint';
    carouselWidth = $('#gd-carousel').outerWidth();
    viewfinderWidth = 1140;
    maxLeft = viewfinderWidth - carouselWidth;
    maxRight = 0;
    myLeft;
    fixedStep = 285;
    firstVisible = -1;
    numPanels = $('.gd-collection-panel').length;
    numToScroll = 4;

    $('#gd-carousel').on('mouseenter', '.gd-collection-panel', function() {
        if ($(this).hasClass('visible-panel-last') === true){
            leftAnimation = -285;
        } else {
            leftAnimation = 0;
        }
        $('> div', this).css('z-index', highestZIndex++).stop( true, true ).animate({
            width:'570px', top:'-95px', left: leftAnimation
        }, animationTime, easingType);
    });

    $('#gd-carousel').on('mouseleave', '.gd-collection-panel', function() {
        $('> div', this).stop( true, true ).animate({width:'100%', top:0, left: 0}, animationTime, easingType,
            function(){
                $(this).css('z-index', 0);
            });
        }
    );

    $('#nav-right').click(function(){
        getCurrentVisibleCollections('forward');
        return false;
    });

    $('#nav-left').click(function(){
        getCurrentVisibleCollections('back');
        return false;
    });

    $(document).on('mouseover', '.gd-collection-item img', function(event) {
        $(this).qtip({
            overwrite: false,
            content: {'attr':'data-tooltip'},
            position: {my: 'bottom center', at: 'top center'},
            show: {
                event: event.type,
                ready: true
            }
        }, event);
    })

    getCurrentVisibleCollections('forward', true);
});