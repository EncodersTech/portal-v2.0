<style>
.popup {
  position: absolute;
  background-color: white;
  border: 1px solid #ccc;
  max-height: 200px;
  overflow-y: auto;
  width: 100%;
  z-index: 1;
}

.popup-item {
  padding: 8px;
  cursor: pointer;
  border-bottom: 1px solid #ccc;
  background: #c3eaff;
  color: black;
}

.popup-item:last-child {
  border-bottom: none;
}
</style>
<template>
  <div>
    <div style="display: inline-block;">
      <input v-model="searchQuery" @input="filterEvents" placeholder="Search events" class="view-select form-control" style="width: 20em; display: inline-block;"/>
      <div v-if="showFilteredEvents" class="popup">
      <div v-for="event in filteredEvents" :key="event.id" class="popup-item form-control" @click="selectEvent(event)">
        {{ cleanUrlFromTitle(event.title) }}
      </div>
    </div>
    </div>
    <select
      v-model="selectedView"
      class="view-select form-control" style="width: 10em; display: inline-block;"
    >
      <option
        v-for="view in viewOptions"
        :key="view.value"
        :value="view.value"
      >
        {{ view.title }}
      </option>
    </select>
    <div class="buttons">
      <button
        type="button" class="btn btn-success"
        @click="onClickTodayButton"
      >
        Today
      </button>
      <button
        type="button" class="btn btn-primary"
        @click="onClickMoveButton(-1)"
      >
        Prev
      </button>
      <button
        type="button" class="btn btn-primary"
        @click="onClickMoveButton(1)"
      >
        Next
      </button>
    </div>
    <span class="date-range" style="font-size: larger; font-weight: 600;">{{ dateRangeText }}</span>
    <Calendar
      ref="calendar"
      style="height: 800px"
      :view="'month'"
      :use-form-popup="false"
      :use-detail-popup="true"
      :week="{
        showTimezoneCollapseButton: true,
        timezonesCollapsed: false,
        eventView: true,
        taskView: false,
      }"
      :month="{ startDayOfWeek: 1 }"
      :timezone="{ zones }"
      :theme="theme"
      :template="{
        // milestone: getTemplateForMilestone,
        allday: getTemplateForAllday,
      }"
      :grid-selection="false"
      :calendars="calendars"
      :events="events"
      :isReadOnly="true"
      :is-read-only="true"
      @clickDayName="onClickDayName"
      @clickEvent="onClickEvent"
      @clickTimezonesCollapseBtn="onClickTimezonesCollapseBtn"
    />
  </div>
</template>
<script>
//   @selectDateTime="onSelectDateTime"
//   @beforeCreateEvent="onBeforeCreateEvent"
//   @beforeUpdateEvent="onBeforeUpdateEvent"
//   @beforeDeleteEvent="onBeforeDeleteEvent"
//   @afterRenderEvent="onAfterRenderEvent"
// import Calendar from '@toast-ui/vue-calendar';
import Calendar from '../../../../public/assets/admin/js/toast-ui/vue-calendar';
import '../../../../public/assets/admin/js/toast-ui/calendar/dist/toastui-calendar.css';

// import { events } from '../../../../public/assets/admin/js/toast-ui/mock-data';
import { theme } from '../../../../public/assets/admin/js/toast-ui/theme.js';
import '../../../../public/assets/admin/js/toast-ui/app.css';


// import '../../../../public/assets/admin/js/toast-ui/tui-time-picker/dist/tui-time-picker.min.css';

export default {
  components: {
    Calendar,
  },
  data() {
    return {
        scheduleView: ['allday'],
        isReadOnly: true,
        isReadOnlyCalendar: true,
        useDetailPopup: true,
        useFormPopup: false,
        calendars: [
            {
            id: 0,
            name: 'Registration Closed',
            backgroundColor: '#d61e11',
            borderColor: '#d61e11',
            dragBackgroundColor: '#d61e11',
            },
            {
            id: 1,
            name: 'Registration Open',
            backgroundColor: '#299406',
            borderColor: '#299406',
            dragBackgroundColor: '#299406',
            },
            {
            id: 2,
            name: 'Registration Late',
            backgroundColor: '#42188f',
            borderColor: '#42188f',
            dragBackgroundColor: '#42188f',
            },
            {
            id: 3,
            name: 'Registration Opening Soon',
            backgroundColor: '#21a9b8',
            borderColor: '#21a9b8',
            dragBackgroundColor: '#21a9b8',
            }
        ],
        events : [],
        zones: [
            {
                timezoneName: 'America/New_York',
                displayLabel: 'NY',
                tooltip: 'UTC-04:00',
            }
        ],
        theme,
        selectedView: 'month',
        viewOptions: [
            {
                title: 'Monthly',
                value: 'month',
            },
            {
                title: 'Weekly',
                value: 'week',
            },
            {
                title: 'Daily',
                value: 'day',
            },
        ],
        dateRangeText: '',
        template: {
            milestone: function(model) {
                return '<span class="calendar-font-icon ic-milestone-b"></span> <span style="background-color: ' + model.bgColor + '">' + model.title + '</span>';
            },
            allday: function(events) {
                return getTimeTemplate(events, true);
            },
            time: function(events) {
                return getTimeTemplate(events, false);
            }
        },
        searchQuery: '',
        showFilteredEvents: false,
        filteredEvents: []
    };
    
  },

  computed: {
    calendarInstance() {
      return this.$refs.calendar.getInstance();
    }
  },
  watch: {
    selectedView(newView) {
      this.calendarInstance.changeView(newView);
      this.setDateRangeText();
    },
  },
  mounted() {
    this.setDateRangeText();

    axios.get('/api/calendar', {
    }).then(result => {
        console.log(result.data.events);
        this.events = result.data.events;
    }).catch(error => {
        console.log("error");
        // this.errorMessage = msg + '<br/>Please reload this page.';
        // this.isError = true;
    }).finally(() => {
        console.log("Done");
        // this.isLoading = false;
    });
  },
  methods: {
    cleanUrlFromTitle(title) {
      const titleWithoutUrl = title.replace(/<a [^>]*>.*<\/a>/, ''); // Remove URLs
      return titleWithoutUrl.trim();
    },
    filterEvents() {
      this.filteredEvents = this.events.filter(e =>
        e.title.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
      this.showFilteredEvents = this.searchQuery.length > 0;
    },
    selectEvent(event) {
      // Update the calendar view and date based on the clicked event
      const eventDate = new Date(event.start); // Adjust this based on your event data structure
      this.calendarInstance.setDate(eventDate);
      // this.calendarInstance.changeView('month');
      this.showFilteredEvents = false;
      this.calendarInstance.changeView('day');
      this.showFilteredEvents = false;
      this.setDateRangeText();
    },
    getTemplateForMilestone(event) {
      return `<span style="color: #fff; background-color: ${event.backgroundColor};">${this.cleanUrlFromTitle(event.title)}</span>`;
    },
    getTemplateForAllday(event) {
      return `[All day] ${this.cleanUrlFromTitle(event.title)}`;
    },
    getTimeTemplate(schedule, isAllDay) {
        var html = [];
        var start = moment(schedule.start.toUTCString());
        if (!isAllDay) {
            html.push('<strong>' + start.format('HH:mm') + '</strong> ');
        }
        if (schedule.isPrivate) {
            html.push('<span class="calendar-font-icon ic-lock-b"></span>');
            html.push(' Private');
        } else {
            if (schedule.isReadOnly) {
                html.push('<span class="calendar-font-icon ic-readonly-b"></span>');
            } else if (schedule.recurrenceRule) {
                html.push('<span class="calendar-font-icon ic-repeat-b"></span>');
            } else if (schedule.attendees.length) {
                html.push('<span class="calendar-font-icon ic-user-b"></span>');
            } else if (schedule.location) {
                html.push('<span class="calendar-font-icon ic-location-b"></span>');
            }
            html.push(' ' + schedule.title);
        }

        return html.join('');
    },
    // onSelectDateTime({ start, end }) {
    //   console.group('onSelectDateTime');
    //   console.log(`Date : ${start} ~ ${end}`);
    //   console.groupEnd();
    // },
    // onBeforeCreateEvent(eventData) {
    //   const event = {
    //     calendarId: eventData.calendarId || '',
    //     id: String(Math.random()),
    //     title: eventData.title,
    //     isAllday: eventData.isAllday,
    //     start: eventData.start,
    //     end: eventData.end,
    //     category: eventData.isAllday ? 'allday' : 'time',
    //     dueDateClass: '',
    //     location: eventData.location,
    //     state: eventData.state,
    //     isPrivate: eventData.isPrivate,
    //   };

    //   this.calendarInstance.createEvents([event]);
    // },
    // onBeforeUpdateEvent(updateData) {
    //   console.group('onBeforeUpdateEvent');
    //   console.log(updateData);
    //   console.groupEnd();

    //   const targetEvent = updateData.event;
    //   const changes = { ...updateData.changes };

    //   this.calendarInstance.updateEvent(targetEvent.id, targetEvent.calendarId, changes);
    // },

    // onBeforeDeleteEvent({ title, id, calendarId }) {
    //   console.group('onBeforeDeleteEvent');
    //   console.log('Event Info : ', title);
    //   console.groupEnd();

    //   this.calendarInstance.deleteEvent(id, calendarId);
    // },
    // onAfterRenderEvent({ title }) {
    //   console.group('onAfterRenderEvent');
    //   console.log('Event Info : ', title);
    //   console.groupEnd();
    // },
    onClickDayName({ date }) {
      // console.group('onClickDayName');
      // console.log('Date : ', date);
      // console.groupEnd();
    },
    onClickEvent({ nativeEvent, event }) {
      // event.title = 'CLICKED';
      // console.group('onClickEvent');
      // console.log('MouseEvent : ', nativeEvent);
      // console.log('Event Info : ', event);
      // console.groupEnd();
    },
    onClickTimezonesCollapseBtn(timezoneCollapsed) {
      // console.group('onClickTimezonesCollapseBtn');
      // console.log('Is Timezone Collapsed?: ', timezoneCollapsed);
      // console.groupEnd();

      const newTheme = {
        'week.daygridLeft.width': '100px',
        'week.timegridLeft.width': '100px',
      };

      this.calendarInstance.setTheme(newTheme);
    },
    onClickTodayButton() {
      this.calendarInstance.today();
      this.setDateRangeText();
    },
    onClickMoveButton(offset) {
      this.calendarInstance.move(offset);
      this.setDateRangeText();
    },
    setDateRangeText() {
      const date = this.calendarInstance.getDate();
      const start = this.calendarInstance.getDateRangeStart();
      const end = this.calendarInstance.getDateRangeEnd();

      const startYear = start.getFullYear();
      const endYear = end.getFullYear();
      const monthNames = [
                  'January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December', 'January'
                ];
      switch (this.selectedView) {
        case 'month':
          this.dateRangeText = `${monthNames[date.getMonth() + 1]} - ${date.getFullYear()}`;

          return;
        case 'day':
          this.dateRangeText = `${date.getDate()}-${monthNames[date.getMonth() + 1]}-${date.getFullYear()}`;

          return;
        default:
          this.dateRangeText = `${start.getDate()}/${monthNames[start.getMonth() + 1]} - ${end.getDate()}/${monthNames[end.getMonth() + 1]} 
          ${startYear} ${ startYear !== endYear ? ` - ${endYear}.` : ''} `;
      }
    },
  },
};
</script>