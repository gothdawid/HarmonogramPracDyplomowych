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
                        }
                    })
                },
                droppable: true, // this allows things to be dropped onto the calendar
                editable: true,
            });
            
            // var save = document.getElementById('save_calendar');
            // var events = calendar.getEvents();

            // if(save != null) {
            //     save.addEventListener('click', function() {
            //         saveCalendar();
            //     });
            // }

            // function saveCalendar() {
            //     console.log(events);

                // $.ajaxSetup({
                //     headers: {
                //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //     }
                // });

                // $.ajax({
                //     type: "POST",
                //     url: '/viewcalendar/-1/save',
                //     dataType: 'json',
                //     data: {
                //         events: events,
                //     },
                //     success: function(data) {
                //         // console.log(data)
                //     }, 
                //     error: function() {
                //         console.log("Error");
                //     }
                // })
            // }

            calendar.render();
        });
    </script>
@endpush