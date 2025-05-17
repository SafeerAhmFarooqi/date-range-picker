<div>
    <!-- Add a conditional class 'dark-mode' when darkMode is true -->
    <div class="__saf_date_picker-calendar-container" 
         :class="{'dark-mode': darkMode}"
         x-data="calendarApp(
            {{ json_encode($multiSelect) }}, 
            '{{ $picker }}', 
            {{ json_encode($preOccupiedDates) }}, 
            {{ json_encode($disableDates) }}, 
            '{{ $initialMonthYear }}',
            '{{ $defaultHoverText }}',
            {{ json_encode($darkMode) }}
         )" 
         x-init="generateCalendar()">
       <!-- Dynamic Input Field -->
       <input 
            wire:model="selectedDates"
            type="text"
            x-bind:value="picker === 'range' 
                            ? selectedRanges.map(range => `[${range.join(', ')}]`).join(', ') 
                            : selectedDates.join(', ')"
            x-bind:name="{{ isset($inputAttributes['name']) 
                            ? "'" . e($inputAttributes['name']) . "'" 
                            : "(picker === 'range' ? 'selectedRanges' : 'selectedDates')" }}"
            @foreach ($inputAttributes as $keyAttribute => $inputAttribute)
                @if (!is_string($inputAttribute)) @continue @endif
                {{ ' ' }}{{ $keyAttribute }}="{{ e($inputAttribute) }}"
            @endforeach
            @click="open = !open;"
            readonly
            aria-label="Selected Dates"
       />

       <!-- Calendar -->
       <div class="__saf_date_picker-calendar" x-show="open" x-transition x-cloak @click.outside="handleCalendarHide()">
           <div class="__saf_date_picker-navigation">
               <button type="button" @click="prevMonth()"> &lt; Prev </button>
               <div class="__saf_date_picker-month">
                    <select x-model="currentMonth" @change="generateCalendar()" x-init="$nextTick(() => $el.value = currentMonth)">
                        <template x-for="(month, index) in months">
                            <option :value="index" x-text="month"></option>
                        </template>
                    </select>
                </div>
               <div class="__saf_date_picker-year">
                   <input type="number" x-model="currentYear" @input="generateCalendar()" placeholder="Year" min="1900" max="2100">
               </div>
               <button type="button" @click="nextMonth()"> Next &gt; </button>
           </div>

           <div class="__saf_date_picker-days">
               <!-- Weekday Headers -->
               <template x-for="day in weekdays">
                   <div class="__saf_date_picker-day __saf_date_picker-header" x-text="day"></div>
               </template>

               <!-- Dynamic Calendar Days -->
               <template x-for="day in calendarDays">
                <div class="__saf_date_picker-day"
                     :class="{
                         '__saf_date_picker-empty': day.empty, 
                         '__saf_date_picker-selected': isSelected(day.date),
                         '__saf_date_picker-pre-occupied': preOccupiedDates.includes(day.date),
                         '__saf_date_picker-disabled': disableDates.includes(day.date)
                     }"
                     @mouseenter="hoverText = getHoverText(day.date)"
                     @mouseleave="hoverText = ''"
                     @click="handleDateClick(day.date, day.disabled, day.preOccupied)">
                    <span x-text="day.label"></span>
                    <div class="__saf_date_picker-hover-info" x-show="hoverText && !day.empty" x-text="hoverText"></div>
                </div>
            </template>
            
           </div>
           <div class="__saf_date_picker-clear-container">
                <button 
                    type="button" 
                    @click="clearSelection()" 
                    class="__saf_date_picker-clear-button">
                    Clear Selection
                </button>
           </div>
       </div>
    </div>
</div>

@assets
<script>
    // Updated calendarApp to accept darkMode parameter
    function calendarApp(multiSelect = false, picker = 'single', preOccupiedDates = [], disableDates = [], initialMonthYear, defaultHoverText = '', darkMode = false) {
        return {
            open: false,
            darkMode: darkMode === true || darkMode === 'true',
            multiSelect: multiSelect === true || multiSelect === 'true',
            picker,
            defaultHoverText: defaultHoverText,
            preOccupiedDates: generateDateRange(preOccupiedDates),
            disableDates: generateDateRange(disableDates),
            currentMonth: initialMonthYear && initialMonthYear.includes('-') 
                ? parseInt(initialMonthYear.split('-')[1]) - 1 
                : new Date().getMonth(),
            currentYear: initialMonthYear && initialMonthYear.includes('-') 
                ? parseInt(initialMonthYear.split('-')[0]) 
                : new Date().getFullYear(),
            selectedDates: [], // For 'single' mode
            selectedRanges: [], // For 'range' mode
            calendarDays: [],
            livewireHoverData: '',
            hoverText: '',
            hoverData: {},
            months: [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ],
            weekdays: ["S", "M", "T", "W", "T", "F", "S"],
            clearSelection() {
                if (this.picker === 'single') {
                    this.selectedDates = [];
                } else if (this.picker === 'range') {
                    this.selectedRanges = [];
                }
                this.hoverText = '';
                @this.call('clearSelection');
                this.loadHoverData();
            },
            generateCalendar() {
                const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
                const days = [];
                for (let i = 0; i < firstDay; i++) {
                    days.push({ label: "", empty: true });
                }
                for (let i = 1; i <= daysInMonth; i++) {
                    const month = String(parseInt(this.currentMonth) + 1).padStart(2, '0');
                    const date = `${this.currentYear}-${month}-${String(i).padStart(2, '0')}`;
                    days.push({
                        label: i,
                        date,
                        empty: false,
                        preOccupied: this.preOccupiedDates.includes(date),
                        disabled: this.disableDates.includes(date),
                    });
                }
                this.calendarDays = days;
                this.loadHoverData();
            },
            loadHoverData() {
                this.hoverData = {};
                const monthYear = `${this.currentYear}-${parseInt(this.currentMonth) + 1}`;
                this.$wire.call('getLivewireHoverData', monthYear)
                    .then(livewireHoverData => {
                        // livewireHoverData should be something like {1: "...", 2: "..."}
                        this.calendarDays.forEach(day => {
                            if (day.empty) return

                            const date = day.date
                            const txt  = livewireHoverData[day.label]
                                        ?? this.defaultHoverText
                                        ?? date

                            this.hoverData[date] = txt
                        })
                    })
                    .catch(e => console.error('hover data error', e));
            },
            getHoverText(date) {
                return this.hoverData[date] || "";
            },
            handleCalendarHide() {
                this.open = false;
                if (this.picker === 'range') {
                    if (!this.multiSelect) {
                        if (this.selectedRanges.length === 1) {
                            const lastRange = this.selectedRanges[0];
                            if (lastRange.length < 2) {
                                this.selectedRanges = [];
                            }
                        }
                    } else {
                        this.selectedRanges = this.selectedRanges.filter(range => range.length >= 2);
                    }
                } else if (this.picker === 'single') {
                    if (!this.multiSelect && this.selectedDates.length > 1) {
                        this.selectedDates = [this.selectedDates[0]];
                    }
                }
            },
            handleDateClick(date, isDisabled, isPreOccupied) {
                if (isDisabled || isPreOccupied) return;
                const isRangeInvalid = (range) => {
                    if (range.length < 2) return false;
                    return (
                        range.some(date => this.disableDates.includes(date) || this.preOccupiedDates.includes(date)) ||
                        this.isRangeOverlapping(range)
                    );
                };
                if (this.picker === 'single') {
                    if (this.multiSelect) {
                        if (this.selectedDates.includes(date)) {
                            this.selectedDates = this.selectedDates.filter(d => d !== date);
                        } else {
                            this.selectedDates.push(date);
                        }
                    } else {
                        this.selectedDates = [date];
                    }
                } else if (this.picker === 'range') {
                    if (!this.multiSelect) {
                        if (this.selectedRanges.length === 1) {
                            const lastRange = this.selectedRanges[0];
                            if (lastRange.length === 1 && lastRange[0] === date) {
                                this.selectedRanges = [[date, date]];
                            } else {
                                const range = this.getDatesInRange(lastRange[0], date);
                                if (isRangeInvalid(range)) {
                                    this.selectedRanges = [];
                                } else {
                                    this.selectedRanges = [range];
                                }
                            }
                        } else {
                            this.selectedRanges = [[date]];
                        }
                    } else {
                        const lastRange = this.selectedRanges[this.selectedRanges.length - 1];
                        if (lastRange && lastRange.length === 1) {
                            if (lastRange[0] === date) {
                                this.selectedRanges[this.selectedRanges.length - 1] = [date, date];
                            } else {
                                const range = this.getDatesInRange(lastRange[0], date);
                                if (isRangeInvalid(range)) {
                                    this.selectedRanges.pop();
                                } else {
                                    this.selectedRanges[this.selectedRanges.length - 1] = range;
                                }
                            }
                        } else {
                            this.selectedRanges.push([date]);
                        }
                    }
                }
                this.selectedRanges = this.selectedRanges.filter(range => range.length > 0);
                if (this.selectedRanges.length === 0) {
                    this.selectedRanges = [];
                }
            },
            isRangeOverlapping(newRange) {
                const [newStart, newEnd] = [new Date(newRange[0]), new Date(newRange[newRange.length - 1])];
                if (!newStart || !newEnd) return false;
                return this.selectedRanges.some(existingRange => {
                    if (existingRange.length < 2) return false;
                    const [existingStart, existingEnd] = [
                        new Date(existingRange[0]),
                        new Date(existingRange[existingRange.length - 1]),
                    ];
                    return newStart <= existingEnd && newEnd >= existingStart;
                });
            },
            isSelected(date) {
                if (this.picker === 'single') {
                    return this.selectedDates.includes(date);
                }
                return this.selectedRanges.some(range => range.includes(date));
            },
            getDatesInRange(startDate, endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const dates = [];
                while (start <= end) {
                    dates.push(start.toISOString().split('T')[0]);
                    start.setDate(start.getDate() + 1);
                }
                return dates;
            },
            prevMonth() {
                this.currentMonth -= 1;
                if (this.currentMonth < 0) {
                    this.currentMonth = 11;
                    this.currentYear -= 1;
                }
                this.generateCalendar();
            },
            nextMonth() {
                this.currentMonth += 1;
                if (this.currentMonth > 11) {
                    this.currentMonth = 0;
                    this.currentYear += 1;
                }
                this.generateCalendar();
            },
        };
    }

    function generateDateRange(dates) {
        const result = [];
        dates.forEach(range => {
            if (!range.startDate) return;
            let start = new Date(`${range.startDate}T00:00:00`);
            const end = range.endDate
                ? new Date(`${range.endDate}T00:00:00`)
                : new Date(`${range.startDate}T00:00:00`);
            while (start <= end) {
                result.push(
                    `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`
                );
                start.setDate(start.getDate() + 1);
            }
        });
        return result;
    }
</script>

<!-- Dark Mode Styles -->
<style>
    body, html {
        overflow: visible;
        font-family: 'Arial', sans-serif;
    }

    /* Default calendar styles */
    .__saf_date_picker-calendar {
        max-width: 400px;
        margin: 0 auto;
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        width: 400px;
        padding: 20px;
        background-color: #ffffff;
        border: 1px solid #e3e3e3;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        transition: opacity 0.3s ease-in-out;
    }

    /* Dark mode overrides */
    .dark-mode .__saf_date_picker-calendar {
        background-color: #333;
        border: 1px solid #444;
        color: #fff;
    }
    .dark-mode .__saf_date_picker-day {
        border-color: #555;
    }
    .dark-mode .__saf_date_picker-navigation button {
        background-color: #555;
    }
    .dark-mode .__saf_date_picker-navigation button:hover {
        background-color: #777;
    }
    .dark-mode .__saf_date_picker-clear-button {
        background-color: #bb0000;
    }
    .dark-mode .__saf_date_picker-clear-button:hover {
        background-color: #aa0000;
    }
    .dark-mode input {
        background-color: #444;
        color: #fff;
        border-color: #555;
    }

    /* The rest of your styles remain unchanged */
    .__saf_date_picker-calendar[x-show="true"] {
        display: block;
    }
    .__saf_date_picker-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .__saf_date_picker-navigation button {
        background-color: #007bff;
        color: #ffffff;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .__saf_date_picker-navigation button:hover {
        background-color: #0056b3;
    }
    .__saf_date_picker-navigation select, 
    .__saf_date_picker-navigation input[type="number"] {
        border: 1px solid #ccc;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        color: #333;
        outline: none;
        width: auto;
    }
    .__saf_date_picker-navigation select {
        cursor: pointer;
    }
    .__saf_date_picker-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }
    .__saf_date_picker-day {
        padding: 10px;
        text-align: center;
        border: 1px solid transparent;
        border-radius: 50%;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
        font-size: 14px;
    }
    .__saf_date_picker-day.__saf_date_picker-empty {
        background-color: transparent;
        cursor: default;
    }
    .__saf_date_picker-day.__saf_date_picker-selected {
        background-color: #007bff;
        color: #ffffff;
        font-weight: bold;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        transform: scale(1.1);
    }
    .__saf_date_picker-day.__saf_date_picker-pre-occupied {
        background-color: #ffcc00;
        color: #333;
        font-style: italic;
        cursor: not-allowed;
    }
    .__saf_date_picker-day.__saf_date_picker-disabled {
        text-decoration: line-through;
        color: #999;
        cursor: not-allowed;
    }
    .__saf_date_picker-day:hover:not(.__saf_date_picker-empty):not(.__saf_date_picker-disabled):not(.__saf_date_picker-pre-occupied) {
        background-color: #f0f8ff;
        color: #007bff;
        transform: scale(1.05);
    }
    .__saf_date_picker-header {
        font-weight: bold;
        text-align: center;
        color: #555;
        padding: 10px 0;
        border-bottom: 1px solid #e3e3e3;
    }
    .__saf_date_picker-hover-info {
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #333;
        color: #fff;
        padding: 5px;
        border-radius: 5px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 10;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease-in-out;
    }
    .__saf_date_picker-day:hover .__saf_date_picker-hover-info {
        opacity: 1;
    }
    .__saf_date_picker-clear-container {
        margin-top: 20px;
        text-align: center;
    }
    .__saf_date_picker-clear-button {
        background-color: #ff4d4d;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s, transform 0.2s;
    }
    .__saf_date_picker-clear-button:hover {
        background-color: #cc0000;
        transform: scale(1.05);
    }
    .__saf_date_picker-calendar-container {
        position: relative;
        display: inline-block;
        overflow: visible;
    }
</style>

<script>
    function check303() {
        alert('check 303');    
    }
</script>
@endassets
