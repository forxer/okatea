(function($) {       
    function formatTime(hour, minute, options)
    {
        var printMinute = minute;
        if (minute < 10) printMinute = '0' + minute;

        if (options.isoTime) {
            var printHour = hour
            if (printHour < 10) printHour = '0' + hour;
            return printHour + ':' + printMinute;
        } else {
            var printHour = hour % 12;
            if (printHour == 0) printHour = 12;

            if (options.meridiemUpperCase) {
            	 var half = (hour < 12) ? 'AM' : 'PM';
            } else {
            	 var half = (hour < 12) ? 'am' : 'pm';
            }
           
            return printHour + ':' + printMinute + half;
        }
    }
    
    function parseTime(text)
    {
        var match = match = /(\d+)\s*[:\-\.,]\s*(\d+)\s*(am|pm)?/i.exec(text);
        if (match && match.length >= 3) {
            var hour = Number(match[1]);
            var minute = Number(match[2])
            if (hour == 12 && match[3]) hour -= 12;
            if (match[3] && match[3].toLowerCase() == 'pm') hour += 12;
            return {
                hour:   hour,
                minute: minute
            };
        } else {
            return null;
        }
    }
    
    function timeToMinutes(time)
    {
        return time && (time.hour * 60 + time.minute);
    }
    
        
    function renderTimeSelect(element, options)
    {
        var minTime = timeToMinutes(options.minTime);
        var maxTime = timeToMinutes(options.maxTime);
        var defaultTime = timeToMinutes(options.defaultTime);
        var selection = options.selection && timeToMinutes(parseTime(options.selection));
        
        //Round selection to nearest time interval so that it matches a list item
        selection = selection && (
            (
                Math.floor((selection - minTime) / options.timeInterval) *
                options.timeInterval
            ) + minTime
        );
        
        var scrollTo;   //Element to scroll the dropdown box to when shown
        var ul = $('<ul />');
        
        for (var time = minTime; time <= maxTime; time += options.timeInterval)  {
            (function(time) {
            	var hour = Math.floor(time / 60);
            	var minute = time % 60;
                var timeText = formatTime(hour, minute, options);
                var fullText = timeText;
                if (options.showDuration) {
                    var duration = time - minTime;
                    if (duration < 60) {
                        fullText += ' (' + duration + ' mins)';
                    } else if (duration == 60) {
                        fullText += ' (1 hr)';
                    } else {
                        //Round partial hours to 1 decimal place
                        fullText += ' (' + (Math.round(duration / 60.0 * 10.0) / 10.0) + ' hrs)';
                    }
                }
                var li = $('<li />').append(
                    $('<a href="javascript:;">' + fullText + '</a>')
                    .click(function() {
                        if (options && options.selectTime) {
                            options.selectTime(timeText);
                        }
                    }).mousemove(function() {
                        $('li.selected', ul).removeClass('selected');
                    })
                ).appendTo(ul);
                
                //Set to scroll to the default hour, unless already set
                if (!scrollTo && time == defaultTime) scrollTo = li;
                
                if (selection == time) {
                    //Highlight selected item
                    li.addClass('selected');
                    
                    //Set to scroll to the selected hour
                    //
                    //This is set even if scrollTo is already set, since
                    //scrolling to selected hour is more important than
                    //scrolling to default hour
                    scrollTo = li;
                }
            })(time);
        }
        if (scrollTo) {
            //Set timeout of zero so code runs immediately after any calling
            //functions are finished (this is needed, since box hasn't been
            //added to the DOM yet)
            setTimeout(function() {
                //Scroll the dropdown box so that scrollTo item is in
                //the middle
                element[0].scrollTop =
                    scrollTo[0].offsetTop - scrollTo.height() * 2;
            }, 0);
        }
        element.empty().append(ul);
    }
    
    $.fn.timePicker = function(options)
    {
        options = options || {};
        options.timeInterval = options.timeInterval || 30;
        options.padding = options.padding || 4;
        
        return this.each(function() {            
            var element = $(this);
            var div;
            var within = false;
            
            element.bind('focus click', function() {
                if (div) return;

                var offset = element.position();
                div = $('<div />')
                    .addClass('timePickerPopup')
                    .mouseenter(function() { within = true; })
                    .mouseleave(function() { within = false; })
                    .mousedown(function(e) {
                        e.preventDefault();
                    })
                    .css({
                        position: 'absolute',
                        left: offset.left,
                        top: offset.top + element.height() +
                            options.padding * 2
                    });

                element.after(div); 
                
                var renderOptions = {
                    selection: element.val(),
                    selectTime: function(time) {
                        within = false;
                        element.val(time);
                        div.remove();
                        div = null;
                    },
                    isoTime:        options.isoTime || false,
                    meridiemUpperCase: options.meridiemUpperCase || false,
                    defaultTime:    options.defaultTime || {hour: 8, minute: 0},
                    minTime:        options.minTime || {hour: 0, minute: 0},
                    maxTime:        options.maxTime || {hour: 23, minute: 59},
                    timeInterval:   options.timeInterval || 30
                };
                
                if (options.startTime) {
                    var startTime = parseTime(options.startTime.val());
                    //Don't display duration if part of a datetime range,
                    //and start and end times are on different days
                    if (options.startDate && options.endDate &&
                        !areDatesEqual(parseDate(options.startDate.val()),
                            parseDate(options.endDate.val()))) {
                        startTime = null;
                    }
                    if (startTime) {
                        renderOptions.minTime = startTime;
                        renderOptions.showDuration = true;
                        div.addClass('endTimePicker');
                    }
                }
                
                renderTimeSelect(div, renderOptions);
            }).blur(function() {
                if (within){
                    if (div) element.focus();
                    return;
                }
                if (!div) return;
                div.remove();
                div = null;
            });
        });
    }
})(jQuery);
