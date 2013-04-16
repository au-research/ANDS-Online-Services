
(function($) {
  /**
   * This script gives you the zone info key representing your device's time zone setting.
   *
   * @name jsTimezoneDetect
   * @version 1.0.5
   * @author Jon Nylander
   * @license MIT License - http://www.opensource.org/licenses/mit-license.php
   *
   * For usage and examples, visit:
   * http://pellepim.bitbucket.org/jstz/
   *
   * Copyright (c) Jon Nylander
   */
  var jstz = (function () {
    'use strict';
    var HEMISPHERE_SOUTH = 's',

    /**
     * Gets the offset in minutes from UTC for a certain date.
     * @param {Date} date
     * @returns {Number}
     */
    get_date_offset = function (date) {
      var offset = -date.getTimezoneOffset();
      return (offset !== null ? offset : 0);
    },

    get_date = function (year, month, date) {
      var d = new Date();
      if (year !== undefined) {
        d.setFullYear(year);
      }
      d.setMonth(month);
      d.setDate(date);
      return d;
    },

    get_january_offset = function (year) {
      return get_date_offset(get_date(year, 0 ,2));
    },

    get_june_offset = function (year) {
      return get_date_offset(get_date(year, 5, 2));
    },

    /**
     * Private method.
     * Checks whether a given date is in daylight saving time.
     * If the date supplied is after august, we assume that we're checking
     * for southern hemisphere DST.
     * @param {Date} date
     * @returns {Boolean}
     */
    date_is_dst = function (date) {
      var is_southern = date.getMonth() > 7,
      base_offset = is_southern ? get_june_offset(date.getFullYear()) :
        get_january_offset(date.getFullYear()),
      date_offset = get_date_offset(date),
      is_west = base_offset < 0,
      dst_offset = base_offset - date_offset;

      if (!is_west && !is_southern) {
        return dst_offset < 0;
      }

      return dst_offset !== 0;
    },

    /**
     * This function does some basic calculations to create information about
     * the user's timezone. It uses REFERENCE_YEAR as a solid year for which
     * the script has been tested rather than depend on the year set by the
     * client device.
     *
     * Returns a key that can be used to do lookups in jstz.olson.timezones.
     * eg: "720,1,2".
     *
     * @returns {String}
     */

    lookup_key = function () {
      var january_offset = get_january_offset(),
      june_offset = get_june_offset(),
      diff = january_offset - june_offset;

      if (diff < 0) {
        return january_offset + ",1";
      } else if (diff > 0) {
        return june_offset + ",1," + HEMISPHERE_SOUTH;
      }

      return january_offset + ",0";
    },

    /**
     * Uses get_timezone_info() to formulate a key to use in the olson.timezones dictionary.
     *
     * Returns a primitive object on the format:
     * {'timezone': TimeZone, 'key' : 'the key used to find the TimeZone object'}
     *
     * @returns Object
     */
    determine = function () {
      var key = lookup_key();
      return new jstz.TimeZone(jstz.olson.timezones[key]);
    },

    offset = function() {
      return lookup_key();
    },

    timezones = function() {
      return jstz.olson.timezones;
    },

    /**
     * This object contains information on when daylight savings starts for
     * different timezones.
     *
     * The list is short for a reason. Often we do not have to be very specific
     * to single out the correct timezone. But when we do, this list comes in
     * handy.
     *
     * Each value is a date denoting when daylight savings starts for that timezone.
     */
    dst_start_for = function (tz_name) {

      var ru_pre_dst_change = new Date(2010, 6, 15, 1, 0, 0, 0), // In 2010 Russia had DST, this allows us to detect Russia :)
      dst_starts = {
        'America/Denver': new Date(2011, 2, 13, 3, 0, 0, 0),
        'America/Mazatlan': new Date(2011, 3, 3, 3, 0, 0, 0),
        'America/Chicago': new Date(2011, 2, 13, 3, 0, 0, 0),
        'America/Mexico_City': new Date(2011, 3, 3, 3, 0, 0, 0),
        'America/Asuncion': new Date(2012, 9, 7, 3, 0, 0, 0),
        'America/Santiago': new Date(2012, 9, 3, 3, 0, 0, 0),
        'America/Campo_Grande': new Date(2012, 9, 21, 5, 0, 0, 0),
        'America/Montevideo': new Date(2011, 9, 2, 3, 0, 0, 0),
        'America/Sao_Paulo': new Date(2011, 9, 16, 5, 0, 0, 0),
        'America/Los_Angeles': new Date(2011, 2, 13, 8, 0, 0, 0),
        'America/Santa_Isabel': new Date(2011, 3, 5, 8, 0, 0, 0),
        'America/Havana': new Date(2012, 2, 10, 2, 0, 0, 0),
        'America/New_York': new Date(2012, 2, 10, 7, 0, 0, 0),
        'Europe/Helsinki': new Date(2013, 2, 31, 5, 0, 0, 0),
        'Pacific/Auckland': new Date(2011, 8, 26, 7, 0, 0, 0),
        'America/Halifax': new Date(2011, 2, 13, 6, 0, 0, 0),
        'America/Goose_Bay': new Date(2011, 2, 13, 2, 1, 0, 0),
        'America/Miquelon': new Date(2011, 2, 13, 5, 0, 0, 0),
        'America/Godthab': new Date(2011, 2, 27, 1, 0, 0, 0),
        'Europe/Moscow': ru_pre_dst_change,
        'Asia/Amman': new Date(2013, 2, 29, 1, 0, 0, 0),
        'Asia/Beirut': new Date(2013, 2, 31, 2, 0, 0, 0),
        'Asia/Damascus': new Date(2013, 3, 6, 2, 0, 0, 0),
        'Asia/Jerusalem': new Date(2013, 2, 29, 5, 0, 0, 0),
        'Asia/Yekaterinburg': ru_pre_dst_change,
        'Asia/Omsk': ru_pre_dst_change,
        'Asia/Krasnoyarsk': ru_pre_dst_change,
        'Asia/Irkutsk': ru_pre_dst_change,
        'Asia/Yakutsk': ru_pre_dst_change,
        'Asia/Vladivostok': ru_pre_dst_change,
        'Asia/Baku': new Date(2013, 2, 31, 4, 0, 0),
        'Asia/Yerevan': new Date(2013, 2, 31, 3, 0, 0),
        'Asia/Kamchatka': ru_pre_dst_change,
        'Asia/Gaza': new Date(2010, 2, 27, 4, 0, 0),
        'Africa/Cairo': new Date(2010, 4, 1, 3, 0, 0),
        'Europe/Minsk': ru_pre_dst_change,
        'Pacific/Apia': new Date(2010, 10, 1, 1, 0, 0, 0),
        'Pacific/Fiji': new Date(2010, 11, 1, 0, 0, 0),
        'Australia/Perth': new Date(2008, 10, 1, 1, 0, 0, 0)
      };

      return dst_starts[tz_name];
    };

    return {
      determine: determine,
      offset: offset,
      timezones: timezones,
      date_is_dst: date_is_dst,
      dst_start_for: dst_start_for
    };
  }());

  /**
   * Simple object to perform ambiguity check and to return name of time zone.
   */
  jstz.TimeZone = function (tz_name) {
    'use strict';
    /**
     * The keys in this object are timezones that we know may be ambiguous after
     * a preliminary scan through the olson_tz object.
     *
     * The array of timezones to compare must be in the order that daylight savings
     * starts for the regions.
     */
    var AMBIGUITIES = {
      'America/Denver':       ['America/Denver', 'America/Mazatlan'],
      'America/Chicago':      ['America/Chicago', 'America/Mexico_City'],
      'America/Santiago':     ['America/Santiago', 'America/Asuncion', 'America/Campo_Grande'],
      'America/Montevideo':   ['America/Montevideo', 'America/Sao_Paulo'],
      'Asia/Beirut':          ['Asia/Amman', 'Asia/Jerusalem', 'Asia/Beirut', 'Europe/Helsinki','Asia/Damascus'],
      'Pacific/Auckland':     ['Pacific/Auckland', 'Pacific/Fiji'],
      'America/Los_Angeles':  ['America/Los_Angeles', 'America/Santa_Isabel'],
      'America/New_York':     ['America/Havana', 'America/New_York'],
      'America/Halifax':      ['America/Goose_Bay', 'America/Halifax'],
      'America/Godthab':      ['America/Miquelon', 'America/Godthab'],
      'Asia/Dubai':           ['Europe/Moscow'],
      'Asia/Dhaka':           ['Asia/Yekaterinburg'],
      'Asia/Jakarta':         ['Asia/Omsk'],
      'Asia/Shanghai':        ['Asia/Krasnoyarsk', 'Australia/Perth'],
      'Asia/Tokyo':           ['Asia/Irkutsk'],
      'Australia/Brisbane':   ['Asia/Yakutsk'],
      'Pacific/Noumea':       ['Asia/Vladivostok'],
      'Pacific/Tarawa':       ['Asia/Kamchatka', 'Pacific/Fiji'],
      'Pacific/Tongatapu':    ['Pacific/Apia'],
      'Asia/Baghdad':         ['Europe/Minsk'],
      'Asia/Baku':            ['Asia/Yerevan','Asia/Baku'],
      'Africa/Johannesburg':  ['Asia/Gaza', 'Africa/Cairo']
    },

    timezone_name = tz_name,

    /**
     * Checks if a timezone has possible ambiguities. I.e timezones that are similar.
     *
     * For example, if the preliminary scan determines that we're in America/Denver.
     * We double check here that we're really there and not in America/Mazatlan.
     *
     * This is done by checking known dates for when daylight savings start for different
     * timezones during 2010 and 2011.
     */
    ambiguity_check = function () {
      var ambiguity_list = AMBIGUITIES[timezone_name],
      length = ambiguity_list.length,
      i = 0,
      tz = ambiguity_list[0];

      for (; i < length; i += 1) {
        tz = ambiguity_list[i];

        if (jstz.date_is_dst(jstz.dst_start_for(tz))) {
          timezone_name = tz;
          return;
        }
      }
    },

    /**
     * Checks if it is possible that the timezone is ambiguous.
     */
    is_ambiguous = function () {
      return typeof (AMBIGUITIES[timezone_name]) !== 'undefined';
    };

    if (is_ambiguous()) {
      ambiguity_check();
    }

    return {
      name: function () {
        return timezone_name;
      }
    };
  };

  jstz.olson = {};

  /*
   * The keys in this dictionary are comma separated as such:
   *
   * First the offset compared to UTC time in minutes.
   *
   * Then a flag which is 0 if the timezone does not take daylight savings into account and 1 if it
   * does.
   *
   * Thirdly an optional 's' signifies that the timezone is in the southern hemisphere,
   * only interesting for timezones with DST.
   *
   * The mapped arrays is used for constructing the jstz.TimeZone object from within
   * jstz.determine_timezone();
   */
  jstz.olson.timezones = {
    '-720,0'   : 'Pacific/Majuro',
    '-660,0'   : 'Pacific/Pago_Pago',
    '-600,1'   : 'America/Adak',
    '-600,0'   : 'Pacific/Honolulu',
    '-570,0'   : 'Pacific/Marquesas',
    '-540,0'   : 'Pacific/Gambier',
    '-540,1'   : 'America/Anchorage',
    '-480,1'   : 'America/Los_Angeles',
    '-480,0'   : 'Pacific/Pitcairn',
    '-420,0'   : 'America/Phoenix',
    '-420,1'   : 'America/Denver',
    '-360,0'   : 'America/Guatemala',
    '-360,1'   : 'America/Chicago',
    '-360,1,s' : 'Pacific/Easter',
    '-300,0'   : 'America/Bogota',
    '-300,1'   : 'America/New_York',
    '-270,0'   : 'America/Caracas',
    '-240,1'   : 'America/Halifax',
    '-240,0'   : 'America/Santo_Domingo',
    '-240,1,s' : 'America/Santiago',
    '-210,1'   : 'America/St_Johns',
    '-180,1'   : 'America/Godthab',
    '-180,0'   : 'America/Argentina/Buenos_Aires',
    '-180,1,s' : 'America/Montevideo',
    '-120,0'   : 'America/Noronha',
    '-120,1'   : 'America/Noronha',
    '-60,1'    : 'Atlantic/Azores',
    '-60,0'    : 'Atlantic/Cape_Verde',
    '0,0'      : 'UTC',
    '0,1'      : 'Europe/London',
    '60,1'     : 'Europe/Berlin',
    '60,0'     : 'Africa/Lagos',
    '60,1,s'   : 'Africa/Windhoek',
    '120,1'    : 'Asia/Beirut',
    '120,0'    : 'Africa/Johannesburg',
    '180,0'    : 'Asia/Baghdad',
    '180,1'    : 'Europe/Moscow',
    '210,1'    : 'Asia/Tehran',
    '240,0'    : 'Asia/Dubai',
    '240,1'    : 'Asia/Baku',
    '270,0'    : 'Asia/Kabul',
    '300,1'    : 'Asia/Yekaterinburg',
    '300,0'    : 'Asia/Karachi',
    '330,0'    : 'Asia/Kolkata',
    '345,0'    : 'Asia/Kathmandu',
    '360,0'    : 'Asia/Dhaka',
    '360,1'    : 'Asia/Omsk',
    '390,0'    : 'Asia/Rangoon',
    '420,1'    : 'Asia/Krasnoyarsk',
    '420,0'    : 'Asia/Jakarta',
    '480,0'    : 'Asia/Shanghai',
    '480,1'    : 'Asia/Irkutsk',
    '525,0'    : 'Australia/Eucla',
    '525,1,s'  : 'Australia/Eucla',
    '540,1'    : 'Asia/Yakutsk',
    '540,0'    : 'Asia/Tokyo',
    '570,0'    : 'Australia/Darwin',
    '570,1,s'  : 'Australia/Adelaide',
    '600,0'    : 'Australia/Brisbane',
    '600,1'    : 'Asia/Vladivostok',
    '600,1,s'  : 'Australia/Sydney',
    '630,1,s'  : 'Australia/Lord_Howe',
    '660,1'    : 'Asia/Kamchatka',
    '660,0'    : 'Pacific/Noumea',
    '690,0'    : 'Pacific/Norfolk',
    '720,1,s'  : 'Pacific/Auckland',
    '720,0'    : 'Pacific/Tarawa',
    '765,1,s'  : 'Pacific/Chatham',
    '780,0'    : 'Pacific/Tongatapu',
    '780,1,s'  : 'Pacific/Apia',
    '840,0'    : 'Pacific/Kiritimati'
  };

  // Monkey patching Date to provide iso8601 for non-conforming browsers
  // c.f. http://stackoverflow.com/a/8563517/664095
  if (!Date.prototype.toISOString) {
    Date.prototype.toISOString = function() {
        function pad(n) { return n < 10 ? '0' + n : n }
        return this.getUTCFullYear() + '-'
            + pad(this.getUTCMonth() + 1) + '-'
            + pad(this.getUTCDate()) + 'T'
            + pad(this.getUTCHours()) + ':'
            + pad(this.getUTCMinutes()) + ':'
            + pad(this.getUTCSeconds()) + 'Z';
    };
  }


/**
 * @license
 * =========================================================
 * ands-datetimepicker.js
 * bootstrap-datetimepicker.js
 * http://www.eyecon.ro/bootstrap-datepicker
 * =========================================================
 * Copyright 2013 Australian National Data Service (ANDS)
 *
 * (Copyright 2012 Stefan Petre)
 *
 * (Contributions:
 *  - Andrew Rowls
 *  - Thiago de Arruda)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =========================================================
 */
  // Picker object
  var smartPhone = (window.orientation != undefined);
  var DateTimePicker = function(element, options) {
    this.id = dpgId++;
    this.init(element, options);
  };

  var dateToDate = function(dt) {
    if (typeof dt === 'string') {
      return new Date(dt);
    }
    return dt;
  };

  DateTimePicker.prototype = {
    jstz: jstz,

    constructor: DateTimePicker,

    init: function(element, options) {
      var icon;
      if (!(options.pickTime || options.pickDate))
        throw new Error('Must choose at least one picker');
      this.options = options;
      this.$element = $(element);
      this.language = options.language in dates ? options.language : 'en'
      this.pickDate = options.pickDate;
      this.pickTime = options.pickTime;
      this.pickTZ = options.pickTZ;
      this.isInput = this.$element.is('input');
      this.component = false;
      if (this.$element.is('.input-append') || this.$element.is('.input-prepend'))
          this.component = this.$element.find('.add-on');
      this.format = options.format;
      if (!this.format) {
        if (this.isInput) this.format = this.$element.data('format');
        else this.format = this.$element.find('input').data('format');
        if (!this.format) this.format = 'yyyy/MM/dd';
      }
      this._compileFormat();
      if (this.component) {
        icon = this.component.find('i');
      }
      if (this.pickTime) {
        if (icon && icon.length) this.timeIcon = icon.data('time-icon');
        if (!this.timeIcon) this.timeIcon = 'icon-time';
        icon.addClass(this.timeIcon);
      }
      if (this.pickDate) {
        if (icon && icon.length) this.dateIcon = icon.data('date-icon');
        if (!this.dateIcon) this.dateIcon = 'icon-calendar';
        icon.removeClass(this.timeIcon);
        icon.addClass(this.dateIcon);
      }
      var templateopts = {
	timeIcon: this.timeIcon,
	pickDate: options.pickDate,
	pickTime: options.pickTime,
	pick12Hr: options.pick12HourFormat,
	pickSec: options.pickSeconds,
	pickTZ: options.pickTZ,
	collapse: options.collapse,
	currTZ: {name: this.jstz.determine().name(), offset: this.jstz.offset()}};
      this.widget = $(getTemplate(templateopts)).appendTo('body');
      this.minViewMode = options.minViewMode||this.$element.data('date-minviewmode')||0;
      if (typeof this.minViewMode === 'string') {
        switch (this.minViewMode) {
          case 'months':
            this.minViewMode = 1;
          break;
          case 'years':
            this.minViewMode = 2;
          break;
          default:
            this.minViewMode = 0;
          break;
        }
      }
      this.viewMode = options.viewMode||this.$element.data('date-viewmode')||0;
      if (typeof this.viewMode === 'string') {
        switch (this.viewMode) {
          case 'months':
            this.viewMode = 1;
          break;
          case 'years':
            this.viewMode = 2;
          break;
          default:
            this.viewMode = 0;
          break;
        }
      }
      this.startViewMode = this.viewMode;
      this.weekStart = options.weekStart||this.$element.data('date-weekstart')||0;
      this.weekEnd = this.weekStart === 0 ? 6 : this.weekStart - 1;
      this.setStartDate(options.startDate || this.$element.data('date-startdate'));
      this.setEndDate(options.endDate || this.$element.data('date-enddate'));
      this.fillDow();
      this.fillMonths();
      this.fillHours();
      this.fillMinutes();
      this.fillSeconds();
      this.fillTZ(jstz.timezones());
      this.update();
      this.showMode();
      this._attachDatePickerEvents();
    },

    show: function(e) {
      this.widget.show();
      this.height = this.component ? this.component.outerHeight() : this.$element.outerHeight();
      this.place();
      this.$element.trigger({
        type: 'show',
        date: this._date
      });
      this._attachDatePickerGlobalEvents();
      if (e) {
        e.stopPropagation();
        e.preventDefault();
      }
    },

    disable: function(){
          this.$element.find('input').prop('disabled',true);
          this._detachDatePickerEvents();
    },
    enable: function(){
          this.$element.find('input').prop('disabled',false);
          this._attachDatePickerEvents();
    },

    hide: function() {
      // Ignore event if in the middle of a picker transition
      var collapse = this.widget.find('.collapse')
      for (var i = 0; i < collapse.length; i++) {
        var collapseData = collapse.eq(i).data('collapse');
        if (collapseData && collapseData.transitioning)
          return;
      }
      this.widget.hide();
      this.viewMode = this.startViewMode;
      this.showMode();
      this.set();
      this.$element.trigger({
        type: 'hide',
        date: this._date
      });
      this._detachDatePickerGlobalEvents();
    },

    set: function() {
      var formatted = '';
      if (!this._unset) formatted = this.formatDate(this._date);
      if (!this.isInput) {
        if (this.component){
          var input = this.$element.find('input');
          input.val(formatted);
          this._resetMaskPos(input);
        }
        this.$element.data('date', formatted);
      } else {
        this.$element.val(formatted);
        this._resetMaskPos(this.$element);
      }
    },

    setValue: function(newDate) {
      if (!newDate) {
        this._unset = true;
      } else {
        this._unset = false;
      }
      if (typeof newDate === 'string') {
        this._date = this.parseDate(newDate);
      } else if(newDate) {
        this._date = new Date(newDate);
      }
      this.set();
      this.viewDate = UTCDate(this._date.getUTCFullYear(), this._date.getUTCMonth(), 1, 0, 0, 0, 0);
      this.fillDate();
      this.fillTime();
    },

    getDate: function() {
      if (this._unset) return null;
      return new Date(this._date.valueOf());
    },

    setDate: function(date) {
      if (!date) this.setValue(null);
      else this.setValue(date.valueOf());
    },

    setStartDate: function(date) {
      if (date instanceof Date) {
        this.startDate = date;
      } else if (typeof date === 'string') {
        this.startDate = new UTCDate(date);
        if (! this.startDate.getUTCFullYear()) {
          this.startDate = -Infinity;
        }
      } else {
        this.startDate = -Infinity;
      }
      if (this.viewDate) {
        this.update();
      }
    },

    setEndDate: function(date) {
      if (date instanceof Date) {
        this.endDate = date;
      } else if (typeof date === 'string') {
        this.endDate = new UTCDate(date);
        if (! this.endDate.getUTCFullYear()) {
          this.endDate = Infinity;
        }
      } else {
        this.endDate = Infinity;
      }
      if (this.viewDate) {
        this.update();
      }
    },

    getLocalDate: function() {
      if (this._unset) return null;
      var d = this._date;
      return new Date(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate(),
                      d.getUTCHours(), d.getUTCMinutes(), d.getUTCSeconds(), d.getUTCMilliseconds());
    },

    setLocalDate: function(localDate) {
      if (!localDate) this.setValue(null);
      else
        this.setValue(Date.UTC(
          localDate.getFullYear(),
          localDate.getMonth(),
          localDate.getDate(),
          localDate.getHours(),
          localDate.getMinutes(),
          localDate.getSeconds(),
          localDate.getMilliseconds()));
    },

    place: function(){
      var position = 'absolute';
      var offset = this.component ? this.component.offset() : this.$element.offset();
      offset.top = offset.top + this.height;

      if (this._isInFixed()) {
        var $window = $(window);
        position = 'fixed';
        offset.top -= $window.scrollTop();
        offset.left -= $window.scrollLeft();
      }

      this.widget.css({
        position: position,
        top: offset.top,
        left: offset.left
      });
    },

    notifyChange: function(){
      this.$element.trigger({
        type: 'changeDate',
        date: this.getDate(),
        localDate: this.getLocalDate()
      });
    },

    update: function(newDate){
      var dateStr = newDate;
      if (!dateStr) {
        if (this.isInput) {
          dateStr = this.$element.val();
        } else {
          dateStr = this.$element.find('input').val();
        }
        if (!dateStr) {
          var tmp = new Date()
          this._date = UTCDate(tmp.getFullYear(),
                              tmp.getMonth(),
                              tmp.getDate(),
                              tmp.getHours(),
                              tmp.getMinutes(),
                              tmp.getSeconds(),
                              tmp.getMilliseconds())
        } else {
          this._date = this.parseDate(dateStr);
        }
      }
      this.viewDate = UTCDate(this._date.getUTCFullYear(), this._date.getUTCMonth(), 1, 0, 0, 0, 0);
      this.fillDate();
      this.fillTime();
    },

    fillDow: function() {
      var dowCnt = this.weekStart;
      var html = '<tr>';
      while (dowCnt < this.weekStart + 7) {
        html += '<th class="dow">' + dates[this.language].daysMin[(dowCnt++) % 7] + '</th>';
      }
      html += '</tr>';
      this.widget.find('.datepicker-days thead').append(html);
    },

    fillMonths: function() {
      var html = '';
      var i = 0
      while (i < 12) {
        html += '<span class="month">' + dates[this.language].monthsShort[i++] + '</span>';
      }
      this.widget.find('.datepicker-months td').append(html);
    },

    fillDate: function() {
      var year = this.viewDate.getUTCFullYear();
      var month = this.viewDate.getUTCMonth();
      var currentDate = UTCDate(
        this._date.getUTCFullYear(),
        this._date.getUTCMonth(),
        this._date.getUTCDate(),
        0, 0, 0, 0
      );
      var startYear  = typeof this.startDate === 'object' ? this.startDate.getUTCFullYear() : -Infinity;
      var startMonth = typeof this.startDate === 'object' ? this.startDate.getUTCMonth() : -1;
      var endYear  = typeof this.endDate === 'object' ? this.endDate.getUTCFullYear() : Infinity;
      var endMonth = typeof this.endDate === 'object' ? this.endDate.getUTCMonth() : 12;

      this.widget.find('.datepicker-days').find('.disabled').removeClass('disabled');
      this.widget.find('.datepicker-months').find('.disabled').removeClass('disabled');
      this.widget.find('.datepicker-years').find('.disabled').removeClass('disabled');

      this.widget.find('.datepicker-days th:eq(1)').text(
        dates[this.language].months[month] + ' ' + year);

      var prevMonth = UTCDate(year, month-1, 28, 0, 0, 0, 0);
      var day = DPGlobal.getDaysInMonth(
        prevMonth.getUTCFullYear(), prevMonth.getUTCMonth());
      prevMonth.setUTCDate(day);
      prevMonth.setUTCDate(day - (prevMonth.getUTCDay() - this.weekStart + 7) % 7);
      if ((year == startYear && month <= startMonth) || year < startYear) {
        this.widget.find('.datepicker-days th:eq(0)').addClass('disabled');
      }
      if ((year == endYear && month >= endMonth) || year > endYear) {
        this.widget.find('.datepicker-days th:eq(2)').addClass('disabled');
      }

      var nextMonth = new Date(prevMonth.valueOf());
      nextMonth.setUTCDate(nextMonth.getUTCDate() + 42);
      nextMonth = nextMonth.valueOf();
      var html = [];
      var clsName;
      while (prevMonth.valueOf() < nextMonth) {
        if (prevMonth.getUTCDay() === this.weekStart) {
          html.push('<tr>');
        }
        clsName = '';
        if (prevMonth.getUTCFullYear() < year ||
            (prevMonth.getUTCFullYear() == year &&
             prevMonth.getUTCMonth() < month)) {
          clsName += ' old';
        } else if (prevMonth.getUTCFullYear() > year ||
                   (prevMonth.getUTCFullYear() == year &&
                    prevMonth.getUTCMonth() > month)) {
          clsName += ' new';
        }
        if (prevMonth.valueOf() === currentDate.valueOf()) {
          clsName += ' active';
        }
        if ((prevMonth.valueOf() + 86400000) <= this.startDate) {
          clsName += ' disabled';
        }
        if (prevMonth.valueOf() > this.endDate) {
          clsName += ' disabled';
        }
        html.push('<td class="day' + clsName + '">' + prevMonth.getUTCDate() + '</td>');
        if (prevMonth.getUTCDay() === this.weekEnd) {
          html.push('</tr>');
        }
        prevMonth.setUTCDate(prevMonth.getUTCDate() + 1);
      }
      this.widget.find('.datepicker-days tbody').empty().append(html.join(''));
      var currentYear = this._date.getUTCFullYear();

      var months = this.widget.find('.datepicker-months').find(
        'th:eq(1)').text(year).end().find('span').removeClass('active');
      if (currentYear === year) {
        months.eq(this._date.getUTCMonth()).addClass('active');
      }
      if (currentYear - 1 < startYear) {
        this.widget.find('.datepicker-months th:eq(0)').addClass('disabled');
      }
      if (currentYear + 1 > endYear) {
        this.widget.find('.datepicker-months th:eq(2)').addClass('disabled');
      }
      for (var i = 0; i < 12; i++) {
        if ((year == startYear && startMonth > i) || (year < startYear)) {
          $(months[i]).addClass('disabled');
        } else if ((year == endYear && endMonth < i) || (year > endYear)) {
          $(months[i]).addClass('disabled');
        }
      }

      html = '';
      year = parseInt(year/10, 10) * 10;
      var yearCont = this.widget.find('.datepicker-years').find(
        'th:eq(1)').text(year + '-' + (year + 9)).end().find('td');
      this.widget.find('.datepicker-years').find('th').removeClass('disabled');
      if (startYear > year) {
        this.widget.find('.datepicker-years').find('th:eq(0)').addClass('disabled');
      }
      if (endYear < year+9) {
        this.widget.find('.datepicker-years').find('th:eq(2)').addClass('disabled');
      }
      year -= 1;
      for (var i = -1; i < 11; i++) {
        html += '<span class="year' + (i === -1 || i === 10 ? ' old' : '') + (currentYear === year ? ' active' : '') + ((year < startYear || year > endYear) ? ' disabled' : '') + '">' + year + '</span>';
        year += 1;
      }
      yearCont.html(html);
    },

    fillHours: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-hours table');
      table.parent().hide();
      var html = '';
      if (this.options.pick12HourFormat) {
        var current = 1;
        for (var i = 0; i < 3; i += 1) {
          html += '<tr>';
          for (var j = 0; j < 4; j += 1) {
             var c = current.toString();
             html += '<td class="hour">' + padLeft(c, 2, '0') + '</td>';
             current++;
          }
          html += '</tr>'
        }
      } else {
        var current = 0;
        for (var i = 0; i < 6; i += 1) {
          html += '<tr>';
          for (var j = 0; j < 4; j += 1) {
             var c = current.toString();
             html += '<td class="hour">' + padLeft(c, 2, '0') + '</td>';
             current++;
          }
          html += '</tr>'
        }
      }
      table.html(html);
    },

    fillMinutes: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-minutes table');
      table.parent().hide();
      var html = '';
      var current = 0;
      for (var i = 0; i < 5; i++) {
        html += '<tr>';
        for (var j = 0; j < 4; j += 1) {
          var c = current.toString();
          html += '<td class="minute">' + padLeft(c, 2, '0') + '</td>';
          current += 3;
        }
        html += '</tr>';
      }
      table.html(html);
    },

    fillSeconds: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-seconds table');
      table.parent().hide();
      var html = '';
      var current = 0;
      for (var i = 0; i < 5; i++) {
        html += '<tr>';
        for (var j = 0; j < 4; j += 1) {
          var c = current.toString();
          html += '<td class="second">' + padLeft(c, 2, '0') + '</td>';
          current += 3;
        }
        html += '</tr>';
      }
      table.html(html);
    },

    fillTZ: function(timezones) {
      var list = this.widget.find(
	'.timepicker .timepicker-tz ul');
      list.parent().hide();
      list.append('<li class="alert alert-small"><small>' +
		  'UTC offset shown in decimal hours' +
		  '</small></li>');
      list.append('<li class="alert alert-small"><small>' +
		  '<strong>*</strong> = DST timezone (applied as required)' +
		  '</small></li>');
      $.each(timezones, function(idx, tz) {
	var li = $('<li/>');
	var button = $('<button class="btn btn-block" />');
	button.data('offset', idx);
	button.data('tz', tz);
	var offset = idx.split(',')[0]/60;
	if (idx.substr(0,1) !== '-') {
	  offset = "+" + parseFloat(offset).toFixed(1);
	}
	if (tz !== "UTC") {
	  button.html(tz + (idx.split(',')[1] == 1 ? '<strong>*</strong>' : '') + ' <small>(' + offset + ')</small>');
	}
	else {
	  button.html(tz + (idx.split(',')[1] == 1 ? '<strong>*</strong>' : ''));
	}
	li.append(button);
	list.append(li);
      });
    },

    fillTime: function() {
      if (!this._date)
        return;
      var timeComponents = this.widget.find('.timepicker span[data-time-component]');
      var table = timeComponents.closest('table');
      var is12HourFormat = this.options.pick12HourFormat;
      var hour = this._date.getUTCHours();
      var period = 'AM';
      if (is12HourFormat) {
        if (hour >= 12) period = 'PM';
        if (hour === 0) hour = 12;
        else if (hour != 12) hour = hour % 12;
        this.widget.find(
          '.timepicker [data-action=togglePeriod]').text(period);
      }
      hour = padLeft(hour.toString(), 2, '0');
      var minute = padLeft(this._date.getUTCMinutes().toString(), 2, '0');
      var second = padLeft(this._date.getUTCSeconds().toString(), 2, '0');
      timeComponents.filter('[data-time-component=hours]').text(hour);
      timeComponents.filter('[data-time-component=minutes]').text(minute);
      timeComponents.filter('[data-time-component=seconds]').text(second);
    },

    click: function(e) {
      e.stopPropagation();
      e.preventDefault();
      this._unset = false;
      var target = $(e.target).closest('span, td, th');
      if (target.length === 1) {
        if (! target.is('.disabled')) {
          switch(target[0].nodeName.toLowerCase()) {
            case 'th':
              switch(target[0].className) {
                case 'switch':
                  this.showMode(1);
                  break;
                case 'prev':
                case 'next':
                  var vd = this.viewDate;
                  var navFnc = DPGlobal.modes[this.viewMode].navFnc;
                  var step = DPGlobal.modes[this.viewMode].navStep;
                  if (target[0].className === 'prev') step = step * -1;
                  vd['set' + navFnc](vd['get' + navFnc]() + step);
                  this.fillDate();
                  this.set();
                  break;
              }
              break;
            case 'span':
              if (target.is('.month')) {
                var month = target.parent().find('span').index(target);
                this.viewDate.setUTCMonth(month);
              } else {
                var year = parseInt(target.text(), 10) || 0;
                this.viewDate.setUTCFullYear(year);
              }
              if (this.viewMode !== 0) {
                this._date = UTCDate(
                  this.viewDate.getUTCFullYear(),
                  this.viewDate.getUTCMonth(),
                  this.viewDate.getUTCDate(),
                  this._date.getUTCHours(),
                  this._date.getUTCMinutes(),
                  this._date.getUTCSeconds(),
                  this._date.getUTCMilliseconds()
                );
                this.notifyChange();
              }
              this.showMode(-1);
              this.fillDate();
              this.set();
              break;
            case 'td':
              if (target.is('.day')) {
                var day = parseInt(target.text(), 10) || 1;
                var month = this.viewDate.getUTCMonth();
                var year = this.viewDate.getUTCFullYear();
                if (target.is('.old')) {
                  if (month === 0) {
                    month = 11;
                    year -= 1;
                  } else {
                    month -= 1;
                  }
                } else if (target.is('.new')) {
                  if (month == 11) {
                    month = 0;
                    year += 1;
                  } else {
                    month += 1;
                  }
                }
                this._date = UTCDate(
                  year, month, day,
                  this._date.getUTCHours(),
                  this._date.getUTCMinutes(),
                  this._date.getUTCSeconds(),
                  this._date.getUTCMilliseconds()
                );
                this.viewDate = UTCDate(
                  year, month, Math.min(28, day) , 0, 0, 0, 0);
                this.fillDate();
                this.set();
                this.notifyChange();
              }
              break;
          }
        }
      }
    },

    actions: {
      incrementHours: function(e) {
        this._date.setUTCHours(this._date.getUTCHours() + 1);
      },

      incrementMinutes: function(e) {
        this._date.setUTCMinutes(this._date.getUTCMinutes() + 1);
      },

      incrementSeconds: function(e) {
        this._date.setUTCSeconds(this._date.getUTCSeconds() + 1);
      },

      decrementHours: function(e) {
        this._date.setUTCHours(this._date.getUTCHours() - 1);
      },

      decrementMinutes: function(e) {
        this._date.setUTCMinutes(this._date.getUTCMinutes() - 1);
      },

      decrementSeconds: function(e) {
        this._date.setUTCSeconds(this._date.getUTCSeconds() - 1);
      },

      togglePeriod: function(e) {
        var hour = this._date.getUTCHours();
        if (hour >= 12) hour -= 12;
        else hour += 12;
        this._date.setUTCHours(hour);
      },

      showPicker: function() {
        this.widget.find('.timepicker > div:not(.timepicker-picker)').hide();
        this.widget.find('.timepicker .timepicker-picker').show();
      },

      showHours: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-hours').show();
      },

      showMinutes: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-minutes').show();
      },

      showSeconds: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-seconds').show();
      },

      showTZ: function() {
        this.widget.find('.timepicker .timepicker-picker').hide();
        this.widget.find('.timepicker .timepicker-tz').show();
      },

      selectTZ: function(e) {
	var tgt = $(e.target);
	if (tgt.is('button')) {
	  var tz = tgt.data('tz');
	  var offset = tgt.data('offset');
	  var label = $('span.timepicker-tz');
	  label.html('<i class="icon-globe"> </i> ' + tz);
	  $('.timepicker .timepicker-tz').hide();
	  $('.timepicker-picker').show();
	  $('.timepicker-picker .timepicker-tz').show();
	}
      },

      selectHour: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
        if (this.options.pick12HourFormat) {
          var current = this._date.getUTCHours();
          if (current >= 12) {
            if (value != 12) value = (value + 12) % 24;
          } else {
            if (value === 12) value = 0;
            else value = value % 12;
          }
        }
        this._date.setUTCHours(value);
        this.actions.showPicker.call(this);
      },

      selectMinute: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
        this._date.setUTCMinutes(value);
        this.actions.showPicker.call(this);
      },

      selectSecond: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
        this._date.setUTCSeconds(value);
        this.actions.showPicker.call(this);
      }
    },

    doAction: function(e) {
      if ($(e.target).parent().is('button')) {
	$(e.target).parent().trigger('click');
      }
      e.stopPropagation();
      e.preventDefault();
      if (!this._date) this._date = UTCDate(1970, 0, 0, 0, 0, 0, 0);
      var action = $(e.currentTarget).data('action');
      var rv = this.actions[action].apply(this, arguments);
      this.set();
      this.fillTime();
      this.notifyChange();
      return rv;
    },

    stopEvent: function(e) {
      e.stopPropagation();
      e.preventDefault();
    },

    // part of the following code was taken from
    // http://cloud.github.com/downloads/digitalBush/jquery.maskedinput/jquery.maskedinput-1.3.js
    keydown: function(e) {
      var self = this, k = e.which, input = $(e.target);
      if (k == 8 || k == 46) {
        // backspace and delete cause the maskPosition
        // to be recalculated
        setTimeout(function() {
          self._resetMaskPos(input);
        });
      }
    },

    keypress: function(e) {
      var k = e.which;
      if (k == 8 || k == 46) {
        // For those browsers which will trigger
        // keypress on backspace/delete
        return;
      }
      var input = $(e.target);
      var c = String.fromCharCode(k);
      var val = input.val() || '';
      val += c;
      var mask = this._mask[this._maskPos];
      if (!mask) {
        return false;
      }
      if (mask.end != val.length) {
        return;
      }
      if (!mask.pattern.test(val.slice(mask.start))) {
        val = val.slice(0, val.length - 1);
        while ((mask = this._mask[this._maskPos]) && mask.character) {
          val += mask.character;
          // advance mask position past static
          // part
          this._maskPos++;
        }
        val += c;
        if (mask.end != val.length) {
          input.val(val);
          return false;
        } else {
          if (!mask.pattern.test(val.slice(mask.start))) {
            input.val(val.slice(0, mask.start));
            return false;
          } else {
            input.val(val);
            this._maskPos++;
            return false;
          }
        }
      } else {
        this._maskPos++;
      }
    },

    change: function(e) {
      var input = $(e.target);
      var val = input.val();
      if (this._formatPattern.test(val)) {
        this.update();
        this.setValue(this._date.getTime());
        this.notifyChange();
        this.set();
      } else if (val && val.trim()) {
        this.setValue(this._date.getTime());
        if (this._date) this.set();
        else input.val('');
      } else {
        if (this._date) {
          this.setValue(null);
          // unset the date when the input is
          // erased
          this.notifyChange();
          this._unset = true;
        }
      }
      this._resetMaskPos(input);
    },

    showMode: function(dir) {
      if (dir) {
        this.viewMode = Math.max(this.minViewMode, Math.min(
          2, this.viewMode + dir));
      }
      this.widget.find('.datepicker > div').hide().filter(
        '.datepicker-'+DPGlobal.modes[this.viewMode].clsName).show();
    },

    destroy: function() {
      this._detachDatePickerEvents();
      this._detachDatePickerGlobalEvents();
      this.widget.remove();
      this.$element.removeData('datetimepicker');
      this.component.removeData('datetimepicker');
    },

    formatDate: function(d) {
      return this.format.replace(formatReplacer, function(match) {
        var methodName, property, rv, len = match.length;
        if (match === 'ms')
          len = 1;
        property = dateFormatComponents[match].property
        if (property === 'Hours12') {
          rv = d.getUTCHours();
          if (rv === 0) rv = 12;
          else if (rv !== 12) rv = rv % 12;
        } else if (property === 'Period12') {
          if (d.getUTCHours() >= 12) return 'PM';
          else return 'AM';
        } else {
          methodName = 'get' + property;
          rv = d[methodName]();
        }
        if (methodName === 'getUTCMonth') rv = rv + 1;
        if (methodName === 'getUTCYear') rv = rv + 1900 - 2000;
        return padLeft(rv.toString(), len, '0');
      });
    },

    parseDate: function(str) {
      var match, i, property, methodName, value, parsed = {};
      if (!(match = this._formatPattern.exec(str)))
        return null;
      for (i = 1; i < match.length; i++) {
        property = this._propertiesByIndex[i];
        if (!property)
          continue;
        value = match[i];
        if (/^\d+$/.test(value))
          value = parseInt(value, 10);
        parsed[property] = value;
      }
      return this._finishParsingDate(parsed);
    },

    _resetMaskPos: function(input) {
      var val = input.val();
      for (var i = 0; i < this._mask.length; i++) {
        if (this._mask[i].end > val.length) {
          // If the mask has ended then jump to
          // the next
          this._maskPos = i;
          break;
        } else if (this._mask[i].end === val.length) {
          this._maskPos = i + 1;
          break;
        }
      }
    },

    _finishParsingDate: function(parsed) {
      var year, month, date, hours, minutes, seconds, milliseconds;
      year = parsed.UTCFullYear;
      if (parsed.UTCYear) year = 2000 + parsed.UTCYear;
      if (!year) year = 1970;
      if (parsed.UTCMonth) month = parsed.UTCMonth - 1;
      else month = 0;
      date = parsed.UTCDate || 1;
      hours = parsed.UTCHours || 0;
      minutes = parsed.UTCMinutes || 0;
      seconds = parsed.UTCSeconds || 0;
      milliseconds = parsed.UTCMilliseconds || 0;
      if (parsed.Hours12) {
        hours = parsed.Hours12;
      }
      if (parsed.Period12) {
        if (/pm/i.test(parsed.Period12)) {
          if (hours != 12) hours = (hours + 12) % 24;
        } else {
          hours = hours % 12;
        }
      }
      return UTCDate(year, month, date, hours, minutes, seconds, milliseconds);
    },

    _compileFormat: function () {
      var match, component, components = [], mask = [],
      str = this.format, propertiesByIndex = {}, i = 0, pos = 0;
      while (match = formatComponent.exec(str)) {
        component = match[0];
        if (component in dateFormatComponents) {
          i++;
          propertiesByIndex[i] = dateFormatComponents[component].property;
          components.push('\\s*' + dateFormatComponents[component].getPattern(
            this) + '\\s*');
          mask.push({
            pattern: new RegExp(dateFormatComponents[component].getPattern(
              this)),
            property: dateFormatComponents[component].property,
            start: pos,
            end: pos += component.length
          });
        }
        else {
          components.push(escapeRegExp(component));
          mask.push({
            pattern: new RegExp(escapeRegExp(component)),
            character: component,
            start: pos,
            end: ++pos
          });
        }
        str = str.slice(component.length);
      }
      this._mask = mask;
      this._maskPos = 0;
      this._formatPattern = new RegExp(
        '^\\s*' + components.join('') + '\\s*$');
      this._propertiesByIndex = propertiesByIndex;
    },

    _attachDatePickerEvents: function() {
      var self = this;
      // this handles date picker clicks
      this.widget.on('click', '.datepicker *', $.proxy(this.click, this));
      // this handles time picker clicks
      this.widget.on('click', '[data-action]', $.proxy(this.doAction, this));
      this.widget.on('mousedown', $.proxy(this.stopEvent, this));
      if (this.pickDate && this.pickTime) {
        this.widget.on('click.togglePicker', '.accordion-toggle', function(e) {
          e.stopPropagation();
          var $this = $(this);
          var $parent = $this.closest('ul');
          var expanded = $parent.find('.collapse.in');
          var closed = $parent.find('.collapse:not(.in)');

          if (expanded && expanded.length) {
            var collapseData = expanded.data('collapse');
            if (collapseData && collapseData.transitioning) return;
            expanded.collapse('hide');
            closed.collapse('show')
            $this.find('i').toggleClass(self.timeIcon + ' ' + self.dateIcon);
            self.$element.find('.add-on i').toggleClass(self.timeIcon + ' ' + self.dateIcon);
          }
        });
      }
      if (this.isInput) {
        this.$element.on({
          'focus': $.proxy(this.show, this),
          'change': $.proxy(this.change, this)
        });
        if (this.options.maskInput) {
          this.$element.on({
            'keydown': $.proxy(this.keydown, this),
            'keypress': $.proxy(this.keypress, this)
          });
        }
      } else {
        this.$element.on({
          'change': $.proxy(this.change, this)
        }, 'input');
        if (this.options.maskInput) {
          this.$element.on({
            'keydown': $.proxy(this.keydown, this),
            'keypress': $.proxy(this.keypress, this)
          }, 'input');
        }
        if (this.component){
          this.component.on('click', $.proxy(this.show, this));
        } else {
          this.$element.on('click', $.proxy(this.show, this));
        }
      }
    },

    _attachDatePickerGlobalEvents: function() {
      $(window).on(
        'resize.datetimepicker' + this.id, $.proxy(this.place, this));
      if (!this.isInput) {
        $(document).on(
          'mousedown.datetimepicker' + this.id, $.proxy(this.hide, this));
      }
    },

    _detachDatePickerEvents: function() {
      this.widget.off('click', '.datepicker *', this.click);
      this.widget.off('click', '[data-action]');
      this.widget.off('mousedown', this.stopEvent);
      if (this.pickDate && this.pickTime) {
        this.widget.off('click.togglePicker');
      }
      if (this.isInput) {
        this.$element.off({
          'focus': this.show,
          'change': this.change
        });
        if (this.options.maskInput) {
          this.$element.off({
            'keydown': this.keydown,
            'keypress': this.keypress
          });
        }
      } else {
        this.$element.off({
          'change': this.change
        }, 'input');
        if (this.options.maskInput) {
          this.$element.off({
            'keydown': this.keydown,
            'keypress': this.keypress
          }, 'input');
        }
        if (this.component){
          this.component.off('click', this.show);
        } else {
          this.$element.off('click', this.show);
        }
      }
    },

    _detachDatePickerGlobalEvents: function () {
      $(window).off('resize.datetimepicker' + this.id);
      if (!this.isInput) {
        $(document).off('mousedown.datetimepicker' + this.id);
      }
    },

    _isInFixed: function() {
      if (this.$element) {
        var parents = this.$element.parents();
        var inFixed = false;
        for (var i=0; i<parents.length; i++) {
            if ($(parents[i]).css('position') == 'fixed') {
                inFixed = true;
                break;
            }
        };
        return inFixed;
      } else {
        return false;
      }
    }
  };

  $.fn.datetimepicker = function ( option, val ) {
    return this.each(function () {
      var $this = $(this),
      data = $this.data('datetimepicker'),
      options = typeof option === 'object' && option;
      if (!data) {
        $this.data('datetimepicker', (data = new DateTimePicker(
          this, $.extend({}, $.fn.datetimepicker.defaults,options))));
      }
      if (typeof option === 'string') data[option](val);
    });
  };

  $.fn.datetimepicker.defaults = {
    maskInput: false,
    pickDate: true,
    pickTime: true,
    pick12HourFormat: false,
    pickSeconds: true,
    pickTZ: true,
    startDate: -Infinity,
    endDate: Infinity,
    collapse: true
  };
  $.fn.datetimepicker.Constructor = DateTimePicker;
  var dpgId = 0;
  var dates = $.fn.datetimepicker.dates = {
    en: {
      days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday",
        "Friday", "Saturday", "Sunday"],
      daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
      daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
      months: ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"],
      monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul",
        "Aug", "Sep", "Oct", "Nov", "Dec"]
    }
  };

  var dateFormatComponents = {
    dd: {property: 'UTCDate', getPattern: function() { return '(0?[1-9]|[1-2][0-9]|3[0-1])\\b';}},
    MM: {property: 'UTCMonth', getPattern: function() {return '(0?[1-9]|1[0-2])\\b';}},
    yy: {property: 'UTCYear', getPattern: function() {return '(\\d{2})\\b'}},
    yyyy: {property: 'UTCFullYear', getPattern: function() {return '(\\d{4})\\b';}},
    hh: {property: 'UTCHours', getPattern: function() {return '(0?[0-9]|1[0-9]|2[0-3])\\b';}},
    mm: {property: 'UTCMinutes', getPattern: function() {return '(0?[0-9]|[1-5][0-9])\\b';}},
    ss: {property: 'UTCSeconds', getPattern: function() {return '(0?[0-9]|[1-5][0-9])\\b';}},
    ms: {property: 'UTCMilliseconds', getPattern: function() {return '([0-9]{1,3})\\b';}},
    HH: {property: 'Hours12', getPattern: function() {return '(0?[1-9]|1[0-2])\\b';}},
    PP: {property: 'Period12', getPattern: function() {return '(AM|PM|am|pm|Am|aM|Pm|pM)\\b';}}
  };

  var keys = [];
  for (var k in dateFormatComponents) keys.push(k);
  keys[keys.length - 1] += '\\b';
  keys.push('.');

  var formatComponent = new RegExp(keys.join('\\b|'));
  keys.pop();
  var formatReplacer = new RegExp(keys.join('\\b|'), 'g');

  function escapeRegExp(str) {
    // http://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
  }

  function padLeft(s, l, c) {
    if (l < s.length) return s;
    else return Array(l - s.length + 1).join(c || ' ') + s;
  }

  function getTemplate(opts) {
    if (opts.pickDate && opts.pickTime) {
      return (
        '<div class="bootstrap-datetimepicker-widget dropdown-menu">' +
          '<ul>' +
            '<li' + (opts.collapse ? ' class="collapse in"' : '') + '>' +
              '<div class="datepicker">' +
                DPGlobal.template +
              '</div>' +
            '</li>' +
            '<li class="picker-switch accordion-toggle"><a><i class="' + opts.timeIcon + '"></i></a></li>' +
            '<li' + (opts.collapse ? ' class="collapse"' : '') + '>' +
              '<div class="timepicker">' +
                TPGlobal.getTemplate({do12: opts.pick12Hr,
				      doSec: opts.pickSec,
				      doTz: opts.pickTZ,
				      currTz: opts.currTZ}) +
              '</div>' +
            '</li>' +
          '</ul>' +
        '</div>'
      );
    } else if (opts.pickTime) {
      return (
        '<div class="bootstrap-datetimepicker-widget dropdown-menu">' +
          '<div class="timepicker">' +
            TPGlobal.getTemplate({do12: opts.pick12Hr,
				  doSec: opts.pickSec,
				  doTz: opts.pickTZ,
				  currTz: opts.currTZ}) +
          '</div>' +
        '</div>'
      );
    } else {
      return (
        '<div class="bootstrap-datetimepicker-widget dropdown-menu">' +
          '<div class="datepicker">' +
            DPGlobal.template +
          '</div>' +
        '</div>'
      );
    }
  }

  function UTCDate() {
    return new Date(Date.UTC.apply(Date, arguments));
  }

  var DPGlobal = {
    modes: [
      {
      clsName: 'days',
      navFnc: 'UTCMonth',
      navStep: 1
    },
    {
      clsName: 'months',
      navFnc: 'UTCFullYear',
      navStep: 1
    },
    {
      clsName: 'years',
      navFnc: 'UTCFullYear',
      navStep: 10
    }],
    isLeapYear: function (year) {
      return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0))
    },
    getDaysInMonth: function (year, month) {
      return [31, (DPGlobal.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month]
    },
    headTemplate:
      '<thead>' +
        '<tr>' +
          '<th class="prev">&lsaquo;</th>' +
          '<th colspan="5" class="switch"></th>' +
          '<th class="next">&rsaquo;</th>' +
        '</tr>' +
      '</thead>',
    contTemplate: '<tbody><tr><td colspan="7"></td></tr></tbody>'
  };
  DPGlobal.template =
    '<div class="datepicker-days">' +
      '<table class="table-condensed">' +
        DPGlobal.headTemplate +
        '<tbody></tbody>' +
      '</table>' +
    '</div>' +
    '<div class="datepicker-months">' +
      '<table class="table-condensed">' +
        DPGlobal.headTemplate +
        DPGlobal.contTemplate+
      '</table>'+
    '</div>'+
    '<div class="datepicker-years">'+
      '<table class="table-condensed">'+
        DPGlobal.headTemplate+
        DPGlobal.contTemplate+
      '</table>'+
    '</div>';
  var TPGlobal = {
    hourTemplate: '<span data-action="showHours" data-time-component="hours" class="timepicker-hour"></span>',
    minuteTemplate: '<span data-action="showMinutes" data-time-component="minutes" class="timepicker-minute"></span>',
    secondTemplate: '<span data-action="showSeconds" data-time-component="seconds" class="timepicker-second"></span>',
    tzTemplate: function(opts) {
      var wrapper = $('<div />');
      var container = $('<span data-action="showTZ" class="timepicker-tz"/>');
      container.html('<i class="icon-globe"> </i> ' + opts.current.name);
      wrapper.append(container);
      return wrapper.html();
      /*
      return '<span class="timepicker-tz"><i class="icon-globe"> </i> ' +
	opts.current.name + ' (UTC ' + (opts.current.offset.substr(0,1) === '-' ? '' : '+') +
	parseFloat(opts.current.offset.split(',')[0]/60).toFixed(1) +')' +
	'</span>';
      */
    }
  };
  TPGlobal.getTemplate = function(opts) {
    return (
    '<div class="timepicker-picker">' +
      '<table class="table-condensed"' +
        (opts.do12 ? ' data-hour-format="12"' : '') +
        '>' +
        '<tr>' +
          '<td><a href="#" class="btn" data-action="incrementHours"><i class="icon-chevron-up"></i></a></td>' +
          '<td class="separator"></td>' +
          '<td><a href="#" class="btn" data-action="incrementMinutes"><i class="icon-chevron-up"></i></a></td>' +
          (opts.doSec ?
          '<td class="separator"></td>' +
          '<td><a href="#" class="btn" data-action="incrementSeconds"><i class="icon-chevron-up"></i></a></td>': '')+
          (opts.do12 ? '<td class="separator"></td>' : '') +
        '</tr>' +
        '<tr>' +
          '<td>' + TPGlobal.hourTemplate + '</td> ' +
          '<td class="separator">:</td>' +
          '<td>' + TPGlobal.minuteTemplate + '</td> ' +
          (opts.doSec ?
          '<td class="separator">:</td>' +
          '<td>' + TPGlobal.secondTemplate + '</td>' : '') +
          (opts.do12 ?
          '<td class="separator"></td>' +
          '<td>' +
          '<button type="button" class="btn btn-primary" data-action="togglePeriod"></button>' +
          '</td>' : '') +
        '</tr>' +
        '<tr>' +
          '<td><a href="#" class="btn" data-action="decrementHours"><i class="icon-chevron-down"></i></a></td>' +
          '<td class="separator"></td>' +
          '<td><a href="#" class="btn" data-action="decrementMinutes"><i class="icon-chevron-down"></i></a></td>' +
          (opts.doSec ?
          '<td class="separator"></td>' +
          '<td><a href="#" class="btn" data-action="decrementSeconds"><i class="icon-chevron-down"></i></a></td>': '') +
          (opts.do12 ? '<td class="separator"></td>' : '') +
        '</tr>' +
	(opts.doTz ?
	 '<tr>' +
	   '<td colspan="' + ( opts.doSec ? '5' : '4' ) + '">' +
	     TPGlobal.tzTemplate({timezones: opts.tzs, current:opts.currTz}) +
	   '</td>' +
	 '</tr>' : '') +
      '</table>' +
    '</div>' +
    '<div class="timepicker-hours" data-action="selectHour">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>'+
    '<div class="timepicker-minutes" data-action="selectMinute">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>'+
    (opts.doSec ?
    '<div class="timepicker-seconds" data-action="selectSecond">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>': '') +
    (opts.doTz ?
    '<div class="timepicker-tz" data-action="selectTZ">' +
      '<ul class="unstyled">' +
      '</ul>'+
    '</div>': '')
    );
  }


})( jQuery )
