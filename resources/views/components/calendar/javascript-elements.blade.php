@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
    <script src='fullcalendar/lang-all.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script> 
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            const editToast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })
            
            var calendar;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: false,
                initialView: 'timeGridWeek',
                initialDate: @json($calendar_start_date),
                slotMinTime: '9:00:00',
                slotMaxTime: '16:30:00',
                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false,
                    hour12: false
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridTwoDay,timeGridDay,listYear'
                },
                slotDuration: '00:05:00',
                eventTimeFormat: { // like '14:30:00'
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false,
                    hour12: false
                },
                views: {
                    timeGridTwoDay: {
                        type: 'timeGrid',
                        duration: { days: 2 },
                        buttonText: 'two days'
                    }
                },
                events: @json($calendar_data),
                eventClick: function(info) {
                    info.jsEvent.preventDefault(); // don't let the browser navigate
                    if(info.event.extendedProps.student != undefined) { //check if event is not commission availibility event
                        Swal.fire({
                            title: '<strong>Additional defense info</strong>',
                            icon: 'info',
                            html:
                                '<h3><b>' + info.event.extendedProps.timeStart + ' - ' + info.event.extendedProps.timeEnd + '</b></h3> <br>' +
                                '<b>Student: </b> ' + info.event.extendedProps.student + '<br>' +
                                '<b>Leader: </b>' + info.event.extendedProps.leader + '<br>' +
                                '<b>Promoter: </b>' + info.event.extendedProps.promoter + '<br>' +
                                '<b>Reviewer: </b>' + info.event.extendedProps.reviewer + '<br>',
                            showCloseButton: true,
                            showCancelButton: false,
                            focusConfirm: true,
                            confirmButtonText:
                                '<i class="fa fa-thumbs-up"></i> Okay!',
                            confirmButtonAriaLabel: 'Okay'
                        })
                    }
                },
                eventOverlap: function(stillEvent, movingEvent) {
                    return (!(movingEvent.extendedProps.leader === stillEvent.extendedProps.leader || 
                            movingEvent.extendedProps.promoter === stillEvent.extendedProps.promoter || 
                            movingEvent.extendedProps.reviewer === stillEvent.extendedProps.reviewer)); //prevent overlapping when there the same person in both defense commission
                },
                eventDragStart: function(info) {
                    /* TODO: optimize this to load into static array all events with commission unavaialbe */

                    //get availible hours for commsion
                    data = info.event.extendedProps.hours_with_lessons;

                    //for every date
                    for (let date in data) {
                        //for every time in specific date
                        for (let time in data[date]) {
                                //check if there is a lesson
                                if (data[date][time][info.event.extendedProps.promoter_id] === 1 || 
                                    data[date][time][info.event.extendedProps.reviewer_id] === 1 ||
                                    data[date][time][info.event.extendedProps.leader_id] === 1) {

                                    obj_date = new Date(date + ' ' + time); //date and time
                                    string_date_start = obj_date.toISOString(); //date and time to string
                                    string_date_end = new Date(obj_date.setMinutes(obj_date.getMinutes() + 30)).toISOString(); //date and time + 30 minutes to string

                                    //add event
                                    calendar.addEvent({
                                        id: info.event.extendedProps.leader_id,
                                        title: 'Commsion lessons',
                                        start: string_date_start,
                                        end: string_date_end,
                                        editable: false,
                                        display: 'background',
                                        backgroundColor: '#ff9999',
                                    });
                                }
                        }
                    }

                    //refresh events
                    calendar.refetchEvents();
                },
                eventDragStop: function(info) {
                    //remove events after dropping
                    var events = calendar.getEvents();
                    for (var i = 0; i < events.length; i++) {
                        //check if event is commsion availibility event
                        if (events[i].id == info.event.extendedProps.leader_id) {
                            //if so remove
                            events[i].remove();
                        }
                    }

                    //reload events
                    calendar.refetchEvents();
                },
                eventChange: function(info) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "{{ route('save.custom.edited.event') }}",
                        data: {
                            defense_id: info.event.id,
                            defense_start: info.event.start.toString(),
                            defense_end: info.event.end.toString(),
                        },
                        success: function(data) {
                            editToast.fire({
                                icon: 'success',
                                title: 'Successfully edited event'
                            })
                        }, 
                        error: function(error) {
                            editToast.fire({
                                icon: 'error',
                                title: 'Error editing event'
                            })

                            info.revert();
                        }
                    })
                },
                droppable: true, // this allows things to be dropped onto the calendar
                editable: true,
            });

            calendar.render();
        });
    </script>
@endpush