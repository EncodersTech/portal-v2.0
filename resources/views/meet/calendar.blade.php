@extends('layouts.main')
<style>
    .dd{
        background-color: #084c66;
        color: white;
        padding: 10px;
        margin: 5% 0%;
        border-radius: 2%;
    }
</style>
<!-- <link rel="stylesheet" href="https://uicdn.toast.com/calendar/latest/toastui-calendar.css" /> -->
@section('content-main')

@include('include.errors')
<div id="calendar"></div>
<div id="app">
        <!-- Your Vue component will be rendered here -->
        <calendar></calendar>
    </div>
@endsection

@section('scripts-main')
<!-- <script src="{{ mix('js/meet/meets.js') }}"></script> -->
<script src="{{ mix('js/meet/meet-calendar.js') }}"></script>



    <!-- <script src="https://cdn.jsdelivr.net/npm/preact@10.17.1/dist/preact.min.js"></script> -->
    <!-- <script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/preact-render-to-string@6.2.1/dist/index.min.js"></script> -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/immer/10.0.2/cjs/immer.cjs.production.min.js" integrity="sha512-dQlH4XRoVkOYYxv0N8RbsEOS4CGw49zsDp3X4xtTxAqxjo43k3l/KERojKTzUtE5Rpp60AbSjKaXzZ6bhsXkfg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/isomorphic-dompurify@1.8.0/browser.min.js"></script> -->
   <!-- <script>
    // document.addEventListener('DOMContentLoaded', function () {
      const container = document.getElementById('calendar');
      const options = {
        defaultView: 'month',
        useDetailPopup: true,
        template: {
          time(event) {
            const { start, end, title } = event;

            return `<span style="color: red;">${formatTime(start)}~${formatTime(end)} ${title}</span>`;
          },
          allday(event) {
            return `<span style="color: gray;">${event.title}</span>`;
          },
        },
        timezone: {
          zones: [
            {
              timezoneName: 'Asia/Seoul',
              displayLabel: 'Seoul',
            },
            {
              timezoneName: 'Europe/London',
              displayLabel: 'London',
            },
          ],
        },
        calendars: [
          {
            id: 'cal1',
            name: 'Personal',
            backgroundColor: '#03bd9e',
          },
          {
            id: 'cal2',
            name: 'Work',
            backgroundColor: '#00a9ff',
          },
        ],
      };

      const calendar = new tui.Calendar(container, options);
      calendar.createEvents([
        {
          id: 'event1',
          calendarId: 'cal2',
          title: 'Weekly meeting',
          start: '2023-08-07T09:00:00',
          end: '2023-08-07T10:00:00',
        },
        {
          id: 'event2',
          calendarId: 'cal1',
          title: 'Lunch appointment',
          start: '2023-08-08T12:00:00',
          end: '2023-08-08T13:00:00',
        },
        {
          id: 'event3',
          calendarId: 'cal2',
          title: 'Vacation',
          start: '2023-08-08',
          end: '2023-08-10',
          isAllday: true,
          category: 'allday',
        },
      ]);
      calendar.on('click', ({ event }) => {
        console.log("event",event);
        const el = document.getElementById('clicked-event');
        el.innerText = event.title;
      });
    // });
    function formatTime(time) {
      const hours = `${time.getHours()}`.padStart(2, '0');
      const minutes = `${time.getMinutes()}`.padStart(2, '0');

      return `${hours}:${minutes}`;
    }
   </script> -->
       
@endsection