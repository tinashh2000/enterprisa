"use strict";
$(document).ready(function () {
    // $('#external-events .fc-event').each(function () {
    //     $(this).data('event', {title: $.trim($(this).text()), stick: true});
    //     $(this).draggable({zIndex: 999, revert: true, revertDuration: 0});
    // });
    loadEvents();
});

function loadEvents() {
    var startDate = moment().format("YYYY") + "-01-01";
    var endDate = moment().format("YYYY") + "-12-31";
    $.post(eGotoLink("Api/Event"), {"r": "fetch", "start" : 0, "limit" : 1000,  "startDate": startDate, "endDate" : endDate}, function (response) {

        try {
            var m = JSON.parse(response);
            if (m["status"] == "OK") {
                var eventList = [];
                var c=0;

                for(var c=0;c < m.eventx.length; c++) {
                    var ev = m.eventx[c];
                    eventList[c] = {
                        id : ev.id,
                        title : ev.name,
                        start : ev.startDate,
                        end : ev.endDate,
                        // "overlap" : false,
                        // "editable" : true,
                        description: ev.name + " @ " + ev.venue,
//                        url: 'http://google.com/',
                        borderColor : '#FFB64D',
                        backgroundColor : '#FFB64D',
                        textColor : '#d8d6d6'};
                }



                var Calendar = FullCalendar.Calendar;
                var Draggable = FullCalendarInteraction.Draggable;

                var calendar = new Calendar(document.getElementById("calendar"), {
                    plugins: [ 'bootstrap', 'interaction', 'dayGrid', 'timeGrid' ],
                    initialView: 'listMonth',
                    header: {left: 'prev,next today', center: 'title', right: 'year,month,agendaWeek,agendaDay'},
                    // header    : {
                    //     left  : 'prev,next today',
                    //     center: 'title',
                    //     right : 'dayGridMonth,timeGridWeek,timeGridDay'
                    // },
                    'themeSystem': 'bootstrap',
                    defaultDate: moment().format("YYYY-MM-DD"),
                    navLinks: true,
                    businessHours: true,
                    editable: true,
                    droppable: true,
                    events   : eventList,
                    drop      : function(info) {
                        // is the "remove after drop" checkbox checked?
                        if (checkbox.checked) {
                            // if so, remove the element from the "Draggable Events" list
                            info.draggedEl.parentNode.removeChild(info.draggedEl);
                        }
                    },
                    eventRender: function (eventObj, el) {
                        // el.popover({
                        //     title: eventObj.title,
                        //     content: eventObj.description,
                        //     trigger: 'hover',
                        //     placement: 'top',
                        //     container: 'body'
                        // });
                    },
                        eventClick: function(calEvent, jsEvent, view) {
                            editEvent(calEvent.id);
                        },
                });

                calendar.render();
            }
        } catch(e) {
            alert(e + " :::: " + response);
        }
    });
}