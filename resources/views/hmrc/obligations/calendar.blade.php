<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-light">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">Obligations Calendar</h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary legend-toggle" data-bs-toggle="collapse" data-bs-target="#calendarLegend">
                        <i class="fas fa-info-circle me-1"></i> Legend
                    </button>
                </div>
            </div>
        </div>

        <!-- Calendar Legend -->
        <div class="collapse mt-3" id="calendarLegend">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-danger me-2">&nbsp;&nbsp;&nbsp;</span>
                            <span>Overdue</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge" style="background-color: #fd7e14;">&nbsp;&nbsp;&nbsp;</span>
                            <span class="ms-2">Critical (≤3 days)</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning">&nbsp;&nbsp;&nbsp;</span>
                            <span class="ms-2">Warning (≤7 days)</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-info">&nbsp;&nbsp;&nbsp;</span>
                            <span class="ms-2">Upcoming</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success">&nbsp;&nbsp;&nbsp;</span>
                            <span class="ms-2">Fulfilled</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div id="obligationsCalendar"></div>
    </div>
</div>

<style>
    #obligationsCalendar {
        max-width: 100%;
        margin: 0 auto;
    }
    
    .fc {
        /* FullCalendar custom styles */
    }
    
    .fc-event {
        cursor: pointer;
        border-radius: 3px;
        padding: 2px 4px;
        font-size: 0.85rem;
    }
    
    .fc-event:hover {
        opacity: 0.8;
    }
    
    .fc-daygrid-event {
        white-space: normal !important;
        align-items: normal !important;
    }
    
    .fc-event-title {
        font-weight: 500;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('obligationsCalendar');
        
        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                height: 'auto',
                events: {
                    url: '{{ route("hmrc.obligations.calendar") }}',
                    method: 'GET',
                    failure: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load calendar events'
                        });
                    }
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.extendedProps.url) {
                        window.location.href = info.event.extendedProps.url;
                    }
                },
                eventContent: function(arg) {
                    let italicEl = document.createElement('div');
                    italicEl.className = 'fc-event-title';
                    italicEl.innerHTML = arg.event.title;
                    
                    let arrayOfDomNodes = [italicEl];
                    return { domNodes: arrayOfDomNodes };
                },
                eventDidMount: function(info) {
                    // Add tooltip
                    const business = info.event.extendedProps.business_name;
                    const type = info.event.extendedProps.type;
                    const status = info.event.extendedProps.status;
                    
                    info.el.title = `${business}\nType: ${type}\nStatus: ${status}`;
                },
                datesSet: function(dateInfo) {
                    // Optional: Load events when view changes
                }
            });
            
            calendar.render();
        }
    });
</script>

