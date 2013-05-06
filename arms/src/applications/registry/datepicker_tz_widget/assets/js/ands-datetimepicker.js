(function($) {
/**
 * @license
 * =========================================================
 * ands-datetimepicker.js
 * http://tarruda.github.io/bootstrap-datetimepicker
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

  /**
   * https://github.com/sproutsocial/walltime-js: walltime-data.min.js;
   * non-essential zone and rule information pruned
   *
   * WallTime License:
   * The MIT License
   *
   * Copyright (c) 2013 Sprout Social, Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a
   * copy of this software and associated documentation files (the "Software"),
   * to deal in the Software without restriction, including without limitation
   * the rights to use, copy, modify, merge, publish, distribute, sublicense,
   * and/or sell copies of the Software, and to permit persons to whom the
   * Software is furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
   * SOFTWARE.
   */
(function(){window.WallTime||(window.WallTime={}),window.WallTime.data={rules:{Namibia:[{name:"Namibia",_from:"1994",_to:"max",type:"-","in":"Sep",on:"Sun>=1",at:"2:00",_save:"1:00",letter:"S"},{name:"Namibia",_from:"1995",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:00",_save:"0",letter:"-"}],SA:[{name:"SA",_from:"1942",_to:"1943",type:"-","in":"Sep",on:"Sun>=15",at:"2:00",_save:"1:00",letter:"-"},{name:"SA",_from:"1943",_to:"1944",type:"-","in":"Mar",on:"Sun>=15",at:"2:00",_save:"0",letter:"-"}],Azer:[{name:"Azer",_from:"1997",_to:"max",type:"-","in":"Mar",on:"lastSun",at:"4:00",_save:"1:00",letter:"S"},{name:"Azer",_from:"1997",_to:"max",type:"-","in":"Oct",on:"lastSun",at:"5:00",_save:"0",letter:"-"}],Dhaka:[{name:"Dhaka",_from:"2009",_to:"only",type:"-","in":"Jun",on:"19",at:"23:00",_save:"1:00",letter:"S"},{name:"Dhaka",_from:"2009",_to:"only",type:"-","in":"Dec",on:"31",at:"23:59",_save:"0",letter:"-"}],Iran:[{name:"Iran",_from:"2013",_to:"2015",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2013",_to:"2015",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2016",_to:"only",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2016",_to:"only",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2017",_to:"2019",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2017",_to:"2019",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2020",_to:"only",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2020",_to:"only",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2021",_to:"2023",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2021",_to:"2023",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2024",_to:"only",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2024",_to:"only",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2025",_to:"2027",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2025",_to:"2027",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2028",_to:"2029",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2028",_to:"2029",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2030",_to:"2031",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2030",_to:"2031",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2032",_to:"2033",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2032",_to:"2033",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2034",_to:"2035",type:"-","in":"Mar",on:"22",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2034",_to:"2035",type:"-","in":"Sep",on:"22",at:"0:00",_save:"0",letter:"S"},{name:"Iran",_from:"2036",_to:"2037",type:"-","in":"Mar",on:"21",at:"0:00",_save:"1:00",letter:"D"},{name:"Iran",_from:"2036",_to:"2037",type:"-","in":"Sep",on:"21",at:"0:00",_save:"0",letter:"S"}],AN:[{name:"AN",_from:"2008",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:00s",_save:"0",letter:"-"},{name:"AN",_from:"2008",_to:"max",type:"-","in":"Oct",on:"Sun>=1",at:"2:00s",_save:"1:00",letter:"-"}],LH:[{name:"LH",_from:"2008",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:00",_save:"0",letter:"-"},{name:"LH",_from:"2008",_to:"max",type:"-","in":"Oct",on:"Sun>=1",at:"2:00",_save:"0:30",letter:"-"}],NZ:[{name:"NZ",_from:"2007",_to:"max",type:"-","in":"Sep",on:"lastSun",at:"2:00s",_save:"1:00",letter:"D"},{name:"NZ",_from:"2008",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:00s",_save:"0",letter:"S"}],Chatham:[{name:"Chatham",_from:"2007",_to:"max",type:"-","in":"Sep",on:"lastSun",at:"2:45s",_save:"1:00",letter:"D"},{name:"Chatham",_from:"2008",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"2:45s",_save:"0",letter:"S"}],WS:[{name:"WS",_from:"2012",_to:"max",type:"-","in":"Sep",on:"lastSun",at:"3:00",_save:"1",letter:"D"},{name:"WS",_from:"2012",_to:"max",type:"-","in":"Apr",on:"Sun>=1",at:"4:00",_save:"0",letter:"-"}],EU:[{name:"EU",_from:"1981",_to:"max",type:"-","in":"Mar",on:"lastSun",at:"1:00u",_save:"1:00",letter:"S"},{name:"EU",_from:"1996",_to:"max",type:"-","in":"Oct",on:"lastSun",at:"1:00u",_save:"0",letter:"-"}],US:[{name:"US",_from:"2007",_to:"max",type:"-","in":"Mar",on:"Sun>=8",at:"2:00",_save:"1:00",letter:"D"},{name:"US",_from:"2007",_to:"max",type:"-","in":"Nov",on:"Sun>=1",at:"2:00",_save:"0",letter:"S"}],Canada:[{name:"Canada",_from:"2007",_to:"max",type:"-","in":"Mar",on:"Sun>=8",at:"2:00",_save:"1:00",letter:"D"},{name:"Canada",_from:"2007",_to:"max",type:"-","in":"Nov",on:"Sun>=1",at:"2:00",_save:"0",letter:"S"}],Chile:[{name:"Chile",_from:"2008",_to:"only",type:"-","in":"Mar",on:"30",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2009",_to:"only",type:"-","in":"Mar",on:"Sun>=9",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2010",_to:"only",type:"-","in":"Apr",on:"Sun>=1",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2011",_to:"only",type:"-","in":"May",on:"Sun>=2",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2011",_to:"only",type:"-","in":"Aug",on:"Sun>=16",at:"4:00u",_save:"1:00",letter:"S"},{name:"Chile",_from:"2012",_to:"only",type:"-","in":"Apr",on:"Sun>=23",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2012",_to:"only",type:"-","in":"Sep",on:"Sun>=2",at:"4:00u",_save:"1:00",letter:"S"},{name:"Chile",_from:"2013",_to:"max",type:"-","in":"Mar",on:"Sun>=9",at:"3:00u",_save:"0",letter:"-"},{name:"Chile",_from:"2013",_to:"max",type:"-","in":"Oct",on:"Sun>=9",at:"4:00u",_save:"1:00",letter:"S"}],Uruguay:[{name:"Uruguay",_from:"2006",_to:"max",type:"-","in":"Oct",on:"Sun>=1",at:"2:00",_save:"1:00",letter:"S"},{name:"Uruguay",_from:"2007",_to:"max",type:"-","in":"Mar",on:"Sun>=8",at:"2:00",_save:"0",letter:"-"}]},zones:{"Pacific/Pago_Pago":[{name:"Pacific/Pago_Pago",_offset:"-11:00",_rule:"-",format:"SST",_until:""}],"America/Adak":[{name:"America/Adak",_offset:"-10:00",_rule:"US",format:"HA%sT",_until:""}],"Pacific/Honolulu":[{name:"Pacific/Honolulu",_offset:"-10:00",_rule:"-",format:"HST",_until:""}],"Pacific/Marquesas":[{name:"Pacific/Marquesas",_offset:"-9:30",_rule:"-",format:"MART",_until:""}],"America/Anchorage":[{name:"America/Anchorage",_offset:"-9:00",_rule:"US",format:"AK%sT",_until:""}],"Pacific/Gambier":[{name:"Pacific/Gambier",_offset:"-9:00",_rule:"-",format:"GAMT",_until:""}],"America/Los_Angeles":[{name:"America/Los_Angeles",_offset:"-8:00",_rule:"US",format:"P%sT",_until:""}],"Pacific/Pitcairn":[{name:"Pacific/Pitcairn",_offset:"-8:00",_rule:"-",format:"PST",_until:""}],"America/Denver":[{name:"America/Denver",_offset:"-7:00",_rule:"US",format:"M%sT",_until:""}],"America/Phoenix":[{name:"America/Phoenix",_offset:"-7:00",_rule:"-",format:"MST",_until:""}],"America/Chicago":[{name:"America/Chicago",_offset:"-6:00",_rule:"US",format:"C%sT",_until:""}],"America/Guatemala":[{name:"America/Guatemala",_offset:"-6:00",_rule:"Guat",format:"C%sT",_until:""}],"Pacific/Easter":[{name:"Pacific/Easter",_offset:"-6:00",_rule:"Chile",format:"EAS%sT",_until:""}],"America/Bogota":[{name:"America/Bogota",_offset:"-5:00",_rule:"CO",format:"CO%sT",_until:""}],"America/New_York":[{name:"America/New_York",_offset:"-5:00",_rule:"US",format:"E%sT",_until:""}],"America/Caracas":[{name:"America/Caracas",_offset:"-4:30",_rule:"-",format:"VET",_until:""}],"America/Halifax":[{name:"America/Halifax",_offset:"-4:00",_rule:"Canada",format:"A%sT",_until:""}],"America/Santiago":[{name:"America/Santiago",_offset:"-4:00",_rule:"Chile",format:"CL%sT",_until:""}],"America/Santo_Domingo":[{name:"America/Santo_Domingo",_offset:"-4:00",_rule:"-",format:"AST",_until:""}],"America/St_Johns":[{name:"America/St_Johns",_offset:"-3:30",_rule:"Canada",format:"N%sT",_until:""}],"America/Godthab":[{name:"America/Godthab",_offset:"-3:00",_rule:"EU",format:"WG%sT",_until:""}],"America/Montevideo":[{name:"America/Montevideo",_offset:"-3:00",_rule:"Uruguay",format:"UY%sT",_until:""}],"America/Argentina/Buenos_Aires":[{name:"America/Argentina/Buenos_Aires",_offset:"-3:00",_rule:"Arg",format:"AR%sT",_until:""}],"America/Noronha":[{name:"America/Noronha",_offset:"-2:00",_rule:"-",format:"FNT",_until:""}],"Atlantic/Azores":[{name:"Atlantic/Azores",_offset:"-1:00",_rule:"EU",format:"AZO%sT",_until:""}],"Atlantic/Cape_Verde":[{name:"Atlantic/Cape_Verde",_offset:"-1:00",_rule:"-",format:"CVT",_until:""}],UTC:[{name:"UTC",_offset:"0:00",_rule:"-",format:"UTC",_until:""}],"Europe/London":[{name:"Europe/London",_offset:"0:00",_rule:"EU",format:"GMT/BST",_until:""}],"Africa/Windhoek":[{name:"Africa/Windhoek",_offset:"1:00",_rule:"Namibia",format:"WA%sT",_until:""}],"Europe/Berlin":[{name:"Europe/Berlin",_offset:"1:00",_rule:"EU",format:"CE%sT",_until:""}],"Africa/Johannesburg":[{name:"Africa/Johannesburg",_offset:"2:00",_rule:"SA",format:"SAST",_until:""}],"Asia/Beirut":[{name:"Asia/Beirut",_offset:"2:00",_rule:"Lebanon",format:"EE%sT",_until:""}],"Asia/Baghdad":[{name:"Asia/Baghdad",_offset:"3:00",_rule:"Iraq",format:"A%sT",_until:""}],"Asia/Tehran":[{name:"Asia/Tehran",_offset:"3:30",_rule:"Iran",format:"IR%sT",_until:""}],"Asia/Baku":[{name:"Asia/Baku",_offset:"4:00",_rule:"Azer",format:"AZ%sT",_until:""}],"Asia/Dubai":[{name:"Asia/Dubai",_offset:"4:00",_rule:"-",format:"GST",_until:""}],"Europe/Moscow":[{name:"Europe/Moscow",_offset:"4:00",_rule:"-",format:"MSK",_until:""}],"Asia/Kabul":[{name:"Asia/Kabul",_offset:"4:30",_rule:"-",format:"AFT",_until:""}],"Asia/Karachi":[{name:"Asia/Karachi",_offset:"5:00",_rule:"Pakistan",format:"PK%sT",_until:""}],"Asia/Kolkata":[{name:"Asia/Kolkata",_offset:"5:30",_rule:"-",format:"IST",_until:""}],"Asia/Kathmandu":[{name:"Asia/Kathmandu",_offset:"5:45",_rule:"-",format:"NPT",_until:""}],"Asia/Dhaka":[{name:"Asia/Dhaka",_offset:"6:00",_rule:"Dhaka",format:"BD%sT",_until:""}],"Asia/Yekaterinburg":[{name:"Asia/Yekaterinburg",_offset:"6:00",_rule:"-",format:"YEKT",_until:""}],"Asia/Rangoon":[{name:"Asia/Rangoon",_offset:"6:30",_rule:"-",format:"MMT",_until:""}],"Asia/Omsk":[{name:"Asia/Omsk",_offset:"7:00",_rule:"-",format:"OMST",_until:""}],"Asia/Shanghai":[{name:"Asia/Shanghai",_offset:"8:00",_rule:"PRC",format:"C%sT",_until:""}],"Asia/Krasnoyarsk":[{name:"Asia/Krasnoyarsk",_offset:"8:00",_rule:"-",format:"KRAT",_until:""}],"Australia/Perth":[{name:"Australia/Perth",_offset:"8:00",_rule:"AW",format:"WST",_until:""}],"Asia/Irkutsk":[{name:"Asia/Irkutsk",_offset:"9:00",_rule:"-",format:"IRKT",_until:""}],"Asia/Tokyo":[{name:"Asia/Tokyo",_offset:"9:00",_rule:"Japan",format:"J%sT",_until:""}],"Australia/Darwin":[{name:"Australia/Darwin",_offset:"9:30",_rule:"Aus",format:"CST",_until:""}],"Asia/Yakutsk":[{name:"Asia/Yakutsk",_offset:"10:00",_rule:"-",format:"YAKT",_until:""}],"Australia/Brisbane":[{name:"Australia/Brisbane",_offset:"10:00",_rule:"AQ",format:"EST",_until:""}],"Australia/Sydney":[{name:"Australia/Sydney",_offset:"10:00",_rule:"AN",format:"EST",_until:""}],"Australia/Lord_Howe":[{name:"Australia/Lord_Howe",_offset:"10:30",_rule:"LH",format:"LHST",_until:""}],"Asia/Vladivostok":[{name:"Asia/Vladivostok",_offset:"11:00",_rule:"-",format:"VLAT",_until:""}],"Pacific/Noumea":[{name:"Pacific/Noumea",_offset:"11:00",_rule:"NC",format:"NC%sT",_until:""}],"Pacific/Norfolk":[{name:"Pacific/Norfolk",_offset:"11:30",_rule:"-",format:"NFT",_until:""}],"Asia/Kamchatka":[{name:"Asia/Kamchatka",_offset:"12:00",_rule:"-",format:"PETT",_until:""}],"Pacific/Auckland":[{name:"Pacific/Auckland",_offset:"12:00",_rule:"NZ",format:"NZ%sT",_until:""}],"Pacific/Majuro":[{name:"Pacific/Majuro",_offset:"12:00",_rule:"-",format:"MHT",_until:""}],"Pacific/Tarawa":[{name:"Pacific/Tarawa",_offset:"12:00",_rule:"-",format:"GILT",_until:""}],"Pacific/Chatham":[{name:"Pacific/Chatham",_offset:"12:45",_rule:"Chatham",format:"CHA%sT",_until:""}],"Pacific/Apia":[{name:"Pacific/Apia",_offset:"13:00",_rule:"WS",format:"WS%sT",_until:""}],"Pacific/Kiritimati":[{name:"Pacific/Kiritimati",_offset:"14:00",_rule:"-",format:"LINT",_until:""}]}},window.WallTime.autoinit=!0}).call(this);
    /**
     * https://github.com/sproutsocial/walltime-js: walltime.min.js
     */
    (function(){var e,t,n,r,i,s;(s=Array.prototype).indexOf||(s.indexOf=function(e){var t,n,r,i;for(t=r=0,i=this.length;r<i;t=++r){n=this[t];if(n===e)return t}return-1}),e={DayShortNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],DayIndex:function(e){return this.DayShortNames.indexOf(e)},DayNameFromIndex:function(e){return this.DayShortNames[e]},AddToDate:function(e,n){return r.MakeDateFromTimeStamp(e.getTime()+n*t.inDay)}},n={MonthsShortNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],CompareRuleMatch:new RegExp("([a-zA-Z]*)([\\<\\>]?=)([0-9]*)"),MonthIndex:function(e){return this.MonthsShortNames.indexOf(e.slice(0,3))},IsDayOfMonthRule:function(e){return e.indexOf(">")>-1||e.indexOf("<")>-1||e.indexOf("=")>-1},IsLastDayOfMonthRule:function(e){return e.slice(0,4)==="last"},DayOfMonthByRule:function(e,t,n){var r,s,o,u,a,f,l,c,h;f=this.CompareRuleMatch.exec(e);if(!f)throw new Error("Unable to parse the 'on' rule for "+e);h=f.slice(1,4),a=h[0],c=h[1],o=h[2],o=parseInt(o,10);if(o===NaN)throw new Error("Unable to parse the dateIndex of the 'on' rule for "+e);u=i.Days.DayIndex(a),s={">=":function(e,t){return e>=t},"<=":function(e,t){return e<=t},">":function(e,t){return e>t},"<":function(e,t){return e<t},"=":function(e,t){return e===t}},r=s[c];if(!r)throw new Error("Unable to parse the conditional for "+c);l=i.Time.MakeDateFromParts(t,n);while(u!==l.getUTCDay()||!r(l.getUTCDate(),o))l=i.Days.AddToDate(l,1);return l.getUTCDate()},LastDayOfMonthRule:function(e,t,n){var r,s,o;s=e.slice(4),r=i.Days.DayIndex(s),n<11?o=i.Time.MakeDateFromParts(t,n+1):o=i.Time.MakeDateFromParts(t+1,0),o=i.Days.AddToDate(o,-1);while(o.getUTCDay()!==r)o=i.Days.AddToDate(o,-1);return o.getUTCDate()}},t={inDay:864e5,inHour:36e5,inMinute:6e4,inSecond:1e3},r={Add:function(e,n,r,i){var s;return n==null&&(n=0),r==null&&(r=0),i==null&&(i=0),s=e.getTime()+n*t.inHour+r*t.inMinute+i*t.inSecond,this.MakeDateFromTimeStamp(s)},ParseGMTOffset:function(e){var t,n,r,i,s;return i=new RegExp("(-)?([0-9]*):([0-9]*):?([0-9]*)?"),r=i.exec(e),s=r?function(){var e,t,i,s;i=r.slice(2),s=[];for(e=0,t=i.length;e<t;e++)n=i[e],s.push(parseInt(n,10));return s}():[0,0,0],t=r&&r[1]==="-",s.splice(0,0,t),s},ParseTime:function(e){var t,n,r,i,s;return i=new RegExp("(\\d*)\\:(\\d*)([wsugz]?)"),n=i.exec(e),n?(s=function(){var e,r,i,s;i=n.slice(1,3),s=[];for(e=0,r=i.length;e<r;e++)t=i[e],s.push(parseInt(t,10));return s}(),r=n[3]?n[3]:"",s.push(r),s):[0,0,""]},ApplyOffset:function(e,n,r){var i;return i=t.inHour*n.hours+t.inMinute*n.mins+t.inSecond*n.secs,n.negative||(i*=-1),r&&(i*=-1),this.MakeDateFromTimeStamp(e.getTime()+i)},ApplySave:function(e,t,n){return n!==!0&&(n=!1),this.ApplyOffset(e,{negative:!0,hours:t.hours,mins:t.mins,secs:0},n)},UTCToWallTime:function(e,t,n){var r;return r=this.UTCToStandardTime(e,t),this.ApplySave(r,n)},UTCToStandardTime:function(e,t){return this.ApplyOffset(e,t,!0)},UTCToQualifiedTime:function(e,t,n,r){var i;i=e;switch(t){case"w":i=this.UTCToWallTime(i,n,r());break;case"s":i=this.UTCToStandardTime(i,n)}return i},QualifiedTimeToUTC:function(e,t,n,r){var i;i=e;switch(t){case"w":i=this.WallTimeToUTC(n,r(),i);break;case"s":i=this.StandardTimeToUTC(n,i)}return i},StandardTimeToUTC:function(e,t,n,r,i,s,o,u){var a;return n==null&&(n=0),r==null&&(r=1),i==null&&(i=0),s==null&&(s=0),o==null&&(o=0),u==null&&(u=0),a=typeof t=="number"?this.MakeDateFromParts(t,n,r,i,s,o,u):t,this.ApplyOffset(a,e)},WallTimeToUTC:function(e,t,n,r,i,s,o,u,a){var f;return r==null&&(r=0),i==null&&(i=1),s==null&&(s=0),o==null&&(o=0),u==null&&(u=0),a==null&&(a=0),f=this.StandardTimeToUTC(e,n,r,i,s,o,u,a),this.ApplySave(f,t,!0)},MakeDateFromParts:function(e,t,n,r,i,s,o){var u;return t==null&&(t=0),n==null&&(n=1),r==null&&(r=0),i==null&&(i=0),s==null&&(s=0),o==null&&(o=0),Date.UTC?new Date(Date.UTC(e,t,n,r,i,s,o)):(u=new Date,u.setUTCFullYear(e),u.setUTCMonth(t),u.setUTCDate(n),u.setUTCHours(r),u.setUTCMinutes(i),u.setUTCSeconds(s),u.setUTCMilliseconds(o),u)},LocalDate:function(e,t,n,r,i,s,o,u,a){return r==null&&(r=0),i==null&&(i=1),s==null&&(s=0),o==null&&(o=0),u==null&&(u=0),a==null&&(a=0),this.WallTimeToUTC(e,t,n,r,i,s,o,u,a)},MakeDateFromTimeStamp:function(e){return new Date(e)},MaxDate:function(){return this.MakeDateFromTimeStamp(864e12)},MinDate:function(){return this.MakeDateFromTimeStamp(-864e12)}},i={Days:e,Months:n,Milliseconds:t,Time:r,noSave:{hours:0,mins:0},noZone:{offset:{negative:!1,hours:0,mins:0,secs:0},name:"UTC"}},typeof window=="undefined"?module.exports=i:typeof define!="undefined"?define("olson/helpers",[],i):(this.WallTime||(this.WallTime={}),this.WallTime.helpers=i)}).call(this),function(){var e,t;e=function(e){var t;return t=function(){function t(t,n,r){this.utc=t,this.zone=n,this.save=r,this.offset=this.zone.offset,this.wallTime=e.Time.UTCToWallTime(this.utc,this.offset,this.save)}return t.prototype.getFullYear=function(){return this.wallTime.getUTCFullYear()},t.prototype.getMonth=function(){return this.wallTime.getUTCMonth()},t.prototype.getDate=function(){return this.wallTime.getUTCDate()},t.prototype.getDay=function(){return this.wallTime.getUTCDay()},t.prototype.getHours=function(){return this.wallTime.getUTCHours()},t.prototype.getMinutes=function(){return this.wallTime.getUTCMinutes()},t.prototype.getSeconds=function(){return this.wallTime.getUTCSeconds()},t.prototype.getMilliseconds=function(){return this.wallTime.getUTCMilliseconds()},t.prototype.getUTCFullYear=function(){return this.utc.getUTCFullYear()},t.prototype.getUTCMonth=function(){return this.utc.getUTCMonth()},t.prototype.getUTCDate=function(){return this.utc.getUTCDate()},t.prototype.getUTCDay=function(){return this.utc.getUTCDay()},t.prototype.getUTCHours=function(){return this.utc.getUTCHours()},t.prototype.getUTCMinutes=function(){return this.utc.getUTCMinutes()},t.prototype.getUTCSeconds=function(){return this.utc.getUTCSeconds()},t.prototype.getUTCMilliseconds=function(){return this.utc.getUTCMilliseconds()},t.prototype.getTime=function(){return this.utc.getTime()},t.prototype.getTimezoneOffset=function(){var e,t;return e=this.offset.hours*60+this.offset.mins,t=this.save.hours*60+this.save.mins,this.offset.negative||(e=-e),e-t},t.prototype.toISOString=function(){return this.wallTime.toISOString()},t.prototype.toUTCString=function(){return this.wallTime.toUTCString()},t.prototype.toDateString=function(){var e,t;return t=this.wallTime.toUTCString(),e=t.match("([a-zA-Z]*), ([0-9]+) ([a-zA-Z]*) ([0-9]+)"),[e[1],e[3],e[2],e[4]].join(" ")},t.prototype.toFormattedTime=function(e){var t,n,r,i;return e==null&&(e=!1),t=i=this.getHours(),t>12&&!e&&(t-=12),t===0&&(t=12),r=this.getMinutes(),r<10&&(r="0"+r),n=i>11?" PM":" AM",e&&(n=""),""+t+":"+r+n},t.prototype.setTime=function(t){return this.wallTime=e.Time.UTCToWallTime(new Date(t),this.zone.offset,this.save),this._updateUTC()},t.prototype.setFullYear=function(e){return this.wallTime.setUTCFullYear(e),this._updateUTC()},t.prototype.setMonth=function(e){return this.wallTime.setUTCMonth(e),this._updateUTC()},t.prototype.setDate=function(e){return this.wallTime.setUTCDate(e),this._updateUTC()},t.prototype.setHours=function(e){return this.wallTime.setUTCHours(e),this._updateUTC()},t.prototype.setMinutes=function(e){return this.wallTime.setUTCMinutes(e),this._updateUTC()},t.prototype.setSeconds=function(e){return this.wallTime.setUTCSeconds(e),this._updateUTC()},t.prototype.setMilliseconds=function(e){return this.wallTime.setUTCMilliseconds(e),this._updateUTC()},t.prototype._updateUTC=function(){return this.utc=e.Time.WallTimeToUTC(this.offset,this.save,this.getFullYear(),this.getMonth(),this.getDate(),this.getHours(),this.getMinutes(),this.getSeconds(),this.getMilliseconds()),this.utc.getTime()},t}(),t},typeof window=="undefined"?(t=require("./helpers"),module.exports=e(t)):typeof define!="undefined"?define("olson/timezonetime",["olson/helpers"],e):(this.WallTime||(this.WallTime={}),this.WallTime.TimeZoneTime=e(this.WallTime.helpers))}.call(this),function(){var e,t,n,r={}.hasOwnProperty;e=function(e,t){var n,i,s,o,u,a;return s=function(){function e(){}return e.prototype.applies=function(e){return!isNaN(parseInt(e,10))},e.prototype.parseDate=function(e){return parseInt(e,10)},e}(),i=function(){function t(){}return t.prototype.applies=e.Months.IsLastDayOfMonthRule,t.prototype.parseDate=function(t,n,r,i,s,o){return e.Months.LastDayOfMonthRule(t,n,r)},t}(),n=function(){function t(){}return t.prototype.applies=e.Months.IsDayOfMonthRule,t.prototype.parseDate=function(t,n,r){return e.Months.DayOfMonthByRule(t,n,r)},t}(),o=function(){function t(t,n,r,i,s,o,u,a,f){var l,c,h,p;this.name=t,this._from=n,this._to=r,this.type=i,this["in"]=s,this.on=o,this.at=u,this._save=a,this.letter=f,this.from=parseInt(this._from,10),this.isMax=!1,h=this.from;switch(this._to){case"max":h=e.Time.MaxDate().getUTCFullYear(),this.isMax=!0;break;case"only":h=this.from;break;default:h=parseInt(this._to,10)}this.to=h,p=this._parseTime(this._save),l=p[0],c=p[1],this.save={hours:l,mins:c}}return t.prototype.forZone=function(t){return this.offset=t,this.fromUTC=e.Time.MakeDateFromParts(this.from,0,1,0,0,0),this.fromUTC=e.Time.ApplyOffset(this.fromUTC,t),this.toUTC=e.Time.MakeDateFromParts(this.to,11,31,23,59,59,999),this.toUTC=e.Time.ApplyOffset(this.toUTC,t)},t.prototype.setOnUTC=function(t,n,r){var i,s,o,u,a,f,l,c=this;return f=e.Months.MonthIndex(this["in"]),s=parseInt(this.on,10),o=isNaN(s)?this._parseOnDay(this.on,t,f):s,l=this._parseTime(this.at),u=l[0],a=l[1],i=l[2],this.onUTC=e.Time.MakeDateFromParts(t,f,o,u,a),this.onUTC.setUTCMilliseconds(this.onUTC.getUTCMilliseconds()-1),this.atQualifier=i!==""?i:"w",this.onUTC=e.Time.QualifiedTimeToUTC(this.onUTC,this.atQualifier,n,function(){return r(c)}),this.onSort=""+f+"-"+o+"-"+this.onUTC.getUTCHours()+"-"+this.onUTC.getUTCMinutes()},t.prototype.appliesToUTC=function(e){return this.fromUTC<=e&&e<=this.toUTC},t.prototype._parseOnDay=function(e,t,r){var o,u,a,f;u=[new s,new i,new n];for(a=0,f=u.length;a<f;a++){o=u[a];if(!o.applies(e))continue;return o.parseDate(e,t,r)}throw new Error("Unable to parse 'on' field for "+this.name+"|"+this._from+"|"+this._to+"|"+e)},t.prototype._parseTime=function(t){return e.Time.ParseTime(t)},t}(),u=function(){function n(t,n){var i,s,o,u,a,f,l,c,h,p=this;this.rules=t,this.timeZone=n,a=null,u=null,o={},i={},h=this.rules;for(l=0,c=h.length;l<c;l++){f=h[l],f.forZone(this.timeZone.offset,function(){return e.noSave});if(a===null||f.from<a)a=f.from;if(u===null||f.to>u)u=f.to;o[f.to]=o[f.to]||[],o[f.to].push(f),i[f.from]=i[f.from]||[],i[f.from].push(f)}this.minYear=a,this.maxYear=u,s=function(n,i){var s,u,a,l;n==null&&(n="toUTC"),i==null&&(i=o),l=[];for(u in i){if(!r.call(i,u))continue;t=i[u],a=p.allThatAppliesTo(t[0][n]);if(a.length<1)continue;t=p._sortRulesByOnTime(t),s=a.slice(-1)[0];if(s.save.hours===0&&s.save.mins===0)continue;l.push(function(){var r,i,o;o=[];for(r=0,i=t.length;r<i;r++)f=t[r],o.push(f[n]=e.Time.ApplySave(f[n],s.save));return o}())}return l},s("toUTC",o),s("fromUTC",i)}return n.prototype.allThatAppliesTo=function(e){var t,n,r,i,s;i=this.rules,s=[];for(n=0,r=i.length;n<r;n++)t=i[n],t.appliesToUTC(e)&&s.push(t);return s},n.prototype.getWallTimeForUTC=function(n){var r,i,s,o,u,a,f;u=this.allThatAppliesTo(n);if(u.length<1)return new t(n,this.timeZone,e.noSave);u=this._sortRulesByOnTime(u),i=function(t){var n;return n=u.indexOf(t),n<1?u.length<1?e.noSave:u.slice(-1)[0].save:u[n-1].save};for(a=0,f=u.length;a<f;a++)o=u[a],o.setOnUTC(n.getUTCFullYear(),this.timeZone.offset,i);return r=function(){var e,t,r;r=[];for(e=0,t=u.length;e<t;e++)o=u[e],o.onUTC.getTime()<n.getTime()&&r.push(o);return r}(),s=u.length<1?e.noSave:u.slice(-1)[0].save,r.length>0&&(s=r.slice(-1)[0].save),new t(n,this.timeZone,s)},n.prototype.getUTCForWallTime=function(t){var n,r,i,s,o,u,a,f;u=e.Time.StandardTimeToUTC(this.timeZone.offset,t),o=function(){var e,t,n,r;n=this.rules,r=[];for(e=0,t=n.length;e<t;e++)s=n[e],s.appliesToUTC(u)&&r.push(s);return r}.call(this);if(o.length<1)return u;o=this._sortRulesByOnTime(o),r=function(t){var n;return n=o.indexOf(t),n<1?o.length<1?e.noSave:o.slice(-1)[0].save:o[n-1].save};for(a=0,f=o.length;a<f;a++)s=o[a],s.setOnUTC(u.getUTCFullYear(),this.timeZone.offset,r);return n=function(){var e,t,n;n=[];for(e=0,t=o.length;e<t;e++)s=o[e],s.onUTC.getTime()<u.getTime()&&n.push(s);return n}(),i=o.length<1?e.noSave:o.slice(-1)[0].save,n.length>0&&(i=n.slice(-1)[0].save),e.Time.WallTimeToUTC(this.timeZone.offset,i,t)},n.prototype.getYearEndDST=function(t){var n,r,i,s,o,u,a,f,l;a=typeof t===number?t:t.getUTCFullYear(),u=e.Time.StandardTimeToUTC(this.timeZone.offset,a,11,31,23,59,59),o=function(){var e,t,n,r;n=this.rules,r=[];for(e=0,t=n.length;e<t;e++)s=n[e],s.appliesToUTC(u)&&r.push(s);return r}.call(this);if(o.length<1)return e.noSave;o=this._sortRulesByOnTime(o),r=function(t){var n;return n=o.indexOf(t),n<1?e.noSave:o[n-1].save};for(f=0,l=o.length;f<l;f++)s=o[f],s.setOnUTC(u.getUTCFullYear(),this.timeZone.offset,r);return n=function(){var e,t,n;n=[];for(e=0,t=o.length;e<t;e++)s=o[e],s.onUTC.getTime()<u.getTime()&&n.push(s);return n}(),i=e.noSave,n.length>0&&(i=n.slice(-1)[0].save),i},n.prototype.isAmbiguous=function(t){var n,r,i,s,o,u,a,f,l,c,h,p,d,v;p=e.Time.StandardTimeToUTC(this.timeZone.offset,t),l=function(){var e,t,n,r;n=this.rules,r=[];for(e=0,t=n.length;e<t;e++)f=n[e],f.appliesToUTC(p)&&r.push(f);return r}.call(this);if(l.length<1)return!1;l=this._sortRulesByOnTime(l),r=function(t){var n;return n=l.indexOf(t),n<1?e.noSave:l[n-1].save};for(d=0,v=l.length;d<v;d++)f=l[d],f.setOnUTC(p.getUTCFullYear(),this.timeZone.offset,r);return n=function(){var e,t,n;n=[];for(e=0,t=l.length;e<t;e++)f=l[e],f.onUTC.getTime()<=p.getTime()-1&&n.push(f);return n}(),n.length<1?!1:(i=n.slice(-1)[0],u=r(i),h={prev:u.hours*60+u.mins,last:i.save.hours*60+i.save.mins},h.prev===h.last?!1:(c=h.prev<h.last,s=function(t,n){var r,i;return r={begin:e.Time.MakeDateFromTimeStamp(t.getTime()+1)},r.end=e.Time.Add(r.begin,0,n),r.begin.getTime()>r.end.getTime()&&(i=r.begin,r.begin=r.end,r.end=i),r},o=c?h.last:-h.prev,a=s(i.onUTC,o),p=e.Time.WallTimeToUTC(this.timeZone.offset,u,t),a.begin<=p&&p<=a.end))},n.prototype._sortRulesByOnTime=function(t){return t.sort(function(t,n){return e.Months.MonthIndex(t["in"])-e.Months.MonthIndex(n["in"])})},n}(),a={Rule:o,RuleSet:u,OnFieldHandlers:{NumberHandler:s,LastHandler:i,CompareHandler:n}},a},typeof window=="undefined"?(n=require("./helpers"),t=require("./timezonetime"),module.exports=e(n,t)):typeof define!="undefined"?define("olson/rule",["olson/helpers","olson/timezonetime"],e):(this.WallTime||(this.WallTime={}),this.WallTime.rule=e(this.WallTime.helpers,this.WallTime.TimeZoneTime))}.call(this),function(){var e,t,n,r;e=function(e,t,n){var r,i,s;return r=function(){function r(t,n,r,i,s,o){var u,a,f,l,c,h;this.name=t,this._offset=n,this._rule=r,this.format=i,this._until=s,h=e.Time.ParseGMTOffset(this._offset),a=h[0],f=h[1],l=h[2],c=h[3],this.offset={negative:a,hours:f,mins:l,secs:isNaN(c)?0:c},u=o?e.Time.MakeDateFromTimeStamp(o.range.end.getTime()+1):e.Time.MinDate(),this.range={begin:u,end:this._parseUntilDate(this._until)}}return r.prototype._parseUntilDate=function(t){var n,r,i,s,o,u,a,f,l,c,h,p,d;return p=t.split(" "),h=p[0],u=p[1],n=p[2],c=p[3],d=c?e.Time.ParseGMTOffset(c):[!1,0,0,0],a=d[0],i=d[1],s=d[2],f=d[3],f=isNaN(f)?0:f,!h||h===""?e.Time.MaxDate():(h=parseInt(h,10),o=u?e.Months.MonthIndex(u):0,n||(n="1"),e.Months.IsDayOfMonthRule(n)?n=e.Months.DayOfMonthByRule(n,h,o):e.Months.IsLastDayOfMonthRule(n)?n=e.Months.LastDayOfMonthRule(n,h,o):n=parseInt(n,10),l=e.Time.StandardTimeToUTC(this.offset,h,o,n,i,s,f),r=e.Time.MakeDateFromTimeStamp(l.getTime()-1),r)},r.prototype.updateEndForRules=function(n){var r,i,s,o,u;if(this._rule==="-"||this._rule==="")return;return this._rule.indexOf(":")>=0&&(u=e.Time.ParseTime(this._rule),i=u[0],s=u[1],this.range.end=e.Time.ApplySave(this.range.end,{hours:i,mins:s})),o=new t.RuleSet(n(this._rule),this),r=o.getYearEndDST(this.range.end),this.range.end=e.Time.ApplySave(this.range.end,r)},r.prototype.UTCToWallTime=function(r,i){var s,o,u,a;return this._rule==="-"||this._rule===""?new n(r,this,e.noSave):this._rule.indexOf(":")>=0?(a=e.Time.ParseTime(this._rule),s=a[0],o=a[1],new n(r,this,{hours:s,mins:o})):(u=new t.RuleSet(i(this._rule),this),u.getWallTimeForUTC(r))},r.prototype.WallTimeToUTC=function(n,r){var i,s,o,u;return this._rule==="-"||this._rule===""?e.Time.StandardTimeToUTC(this.offset,n):this._rule.indexOf(":")>=0?(u=e.Time.ParseTime(this._rule),i=u[0],s=u[1],e.Time.WallTimeToUTC(this.offset,{hours:i,mins:s},n)):(o=new t.RuleSet(r(this._rule),this),o.getUTCForWallTime(n,this.offset))},r.prototype.IsAmbiguous=function(n,r){var i,s,o,u,a,f,l,c,h;if(this._rule==="-"||this._rule==="")return!1;if(this._rule.indexOf(":")>=0){f=e.Time.StandardTimeToUTC(this.offset,n),l=e.Time.ParseTime(this._rule),s=l[0],u=l[1],o=function(t){var n,r;return n={begin:this.range.begin,end:e.Time.ApplySave(this.range.begin,{hours:s,mins:u})},n.end.getTime()<n.begin.getTime()&&(r=n.begin,n.begin=n.end,n.end=r),n},i=o(this.range.begin);if(i.begin.getTime()<=(c=f.getTime())&&c<i.end.getTime())return!0;i=o(this.range.end),i.begin.getTime()<=(h=f.getTime())&&h<i.end.getTime()}return a=new t.RuleSet(r(this._rule),this),a.isAmbiguous(n,this.offset)},r}(),i=function(){function t(e,t){var n,r,i,s;this.zones=e!=null?e:[],this.getRulesNamed=t,this.zones.length>0?this.name=this.zones[0].name:this.name="",s=this.zones;for(r=0,i=s.length;r<i;r++)n=s[r],n.updateEndForRules}return t.prototype.add=function(e){this.zones.length===0&&this.name===""&&(this.name=e.name);if(this.name!==e.name)throw new Error("Cannot add different named zones to a ZoneSet");return this.zones.push(e)},t.prototype.findApplicable=function(t,n){var r,i,s,o,u,a,f,l;n==null&&(n=!1),o=t.getTime(),r=function(t){return{begin:e.Time.UTCToStandardTime(t.range.begin,t.offset),end:e.Time.UTCToStandardTime(t.range.end,t.offset)}},i=null,l=this.zones;for(a=0,f=l.length;a<f;a++){u=l[a],s=n?r(u):u.range;if(s.begin.getTime()<=o&&o<=s.end.getTime()){i=u;break}}return i},t.prototype.getWallTimeForUTC=function(t){var r;return r=this.findApplicable(t),r?r.UTCToWallTime(t,this.getRulesNamed):new n(t,e.noZone,e.noSave)},t.prototype.getUTCForWallTime=function(e){var t;return t=this.findApplicable(e,!0),t?t.WallTimeToUTC(e,this.getRulesNamed):e},t.prototype.isAmbiguous=function(e){var t;return t=this.findApplicable(e,!0),t?t.IsAmbiguous(e,this.getRulesNamed):!1},t}(),s={Zone:r,ZoneSet:i}},typeof window=="undefined"?(n=require("./helpers"),r=require("./rule"),t=require("./timezonetime"),module.exports=e(n,r,t)):typeof define!="undefined"?define("olson/zone",["olson/helpers","olson/rule","olson/timezonetime"],e):(this.WallTime||(this.WallTime={}),this.WallTime.zone=e(this.WallTime.helpers,this.WallTime.rule,this.WallTime.TimeZoneTime))}.call(this),function(){var e,t,n,r,i,s,o,u,a,f,l={}.hasOwnProperty;t=function(e,t,n){var r;return r=function(){function r(){}return r.prototype.init=function(e,t){return e==null&&(e={}),t==null&&(t={}),this.zones={},this.rules={},this.addRulesZones(e,t),this.zoneSet=null,this.timeZoneName=null,this.doneInit=!0},r.prototype.addRulesZones=function(e,r){var i,s,o,u,a,f,c,h,p,d,v,m,g;e==null&&(e={}),r==null&&(r={}),i=null;for(p in r){if(!l.call(r,p))continue;d=r[p],u=[],i=null;for(v=0,m=d.length;v<m;v++)h=d[v],o=new n.Zone(h.name,h._offset,h._rule,h.format,h._until,i),u.push(o),i=o;this.zones[p]=u}g=[];for(f in e){if(!l.call(e,f))continue;c=e[f],s=function(){var e,n,r;r=[];for(e=0,n=c.length;e<n;e++)a=c[e],r.push(new t.Rule(a.name,a._from,a._to,a.type,a["in"],a.on,a.at,a._save,a.letter));return r}(),g.push(this.rules[f]=s)}return g},r.prototype.setTimeZone=function(e){var t,r=this;if(!this.doneInit)throw new Error("Must call init with rules and zones before setting time zone");if(!this.zones[e])throw new Error("Unable to find time zone named "+(e||"<blank>"));return t=this.zones[e],this.zoneSet=new n.ZoneSet(t,function(e){return r.rules[e]}),this.timeZoneName=e},r.prototype.Date=function(t,n,r,i,s,o,u){return n==null&&(n=0),r==null&&(r=1),i==null&&(i=0),s==null&&(s=0),o==null&&(o=0),u==null&&(u=0),t||(t=(new Date).getUTCFullYear()),e.Time.MakeDateFromParts(t,n,r,i,s,o,u)},r.prototype.UTCToWallTime=function(e,t){t==null&&(t=this.timeZoneName),typeof e=="number"&&(e=new Date(e)),t!==this.timeZoneName&&this.setTimeZone(t);if(!this.zoneSet)throw new Error("Must set the time zone before converting times");return this.zoneSet.getWallTimeForUTC(e)},r.prototype.WallTimeToUTC=function(t,n,r,i,s,o,u,a){var f;return t==null&&(t=this.timeZoneName),r==null&&(r=0),i==null&&(i=1),s==null&&(s=0),o==null&&(o=0),u==null&&(u=0),a==null&&(a=0),t!==this.timeZoneName&&this.setTimeZone(t),f=typeof n=="number"?e.Time.MakeDateFromParts(n,r,i,s,o,u,a):n,this.zoneSet.getUTCForWallTime(f)},r.prototype.IsAmbiguous=function(t,n,r,i,s,o){var u;return t==null&&(t=this.timeZoneName),o==null&&(o=0),t!==this.timeZoneName&&this.setTimeZone(t),u=typeof n=="number"?e.Time.MakeDateFromParts(n,r,i,s,o):n,this.zoneSet.isAmbiguous(u)},r}(),new r};if(typeof window=="undefined")s=require("./olson/zone"),i=require("./olson/rule"),r=require("./olson/helpers"),module.exports=t(r,i,s);else if(typeof define!="undefined")define("walltime",["olson/helpers","olson/rule","olson/zone"],t);else{this.WallTime||(this.WallTime={}),e=t(this.WallTime.helpers,this.WallTime.rule,this.WallTime.zone),u=this.WallTime;for(n in u){if(!l.call(u,n))continue;o=u[n],e[n]=o}this.WallTime=e,this.WallTime.autoinit&&((a=this.WallTime.data)!=null?a.rules:void 0)&&((f=this.WallTime.data)!=null?f.zones:void 0)&&this.WallTime.init(this.WallTime.data.rules,this.WallTime.data.zones)}}.call(this);
/**
 * Date.parse with progressive enhancement for ISO 8601 <https://github.com/csnover/js-iso8601>
 * © 2011 Colin Snover <http://zetafleet.com>
 * Released under MIT license.
 */
(function (Date, undefined) {
    var origParse = Date.parse, numericKeys = [ 1, 4, 5, 6, 7, 10, 11 ];
    Date.parse = function (date) {
        var timestamp, struct, minutesOffset = 0;

        // ES5 §15.9.4.2 states that the string should attempt to be parsed as a Date Time String Format string
        // before falling back to any implementation-specific date parsing, so that’s what we do, even if native
        // implementations could be faster
        //              1 YYYY                2 MM       3 DD           4 HH    5 mm       6 ss        7 msec        8 Z 9 ±    10 tzHH    11 tzmm
        if ((struct = /^(\d{4}|[+\-]\d{6})(?:-(\d{2})(?:-(\d{2}))?)?(?:T(\d{2}):(\d{2})(?::(\d{2})(?:\.(\d{3}))?)?(?:(Z)|([+\-])(\d{2})(?::(\d{2}))?)?)?$/.exec(date))) {
            // avoid NaN timestamps caused by “undefined” values being passed to Date.UTC
            for (var i = 0, k; (k = numericKeys[i]); ++i) {
                struct[k] = +struct[k] || 0;
            }

            // allow undefined days and months
            struct[2] = (+struct[2] || 1) - 1;
            struct[3] = +struct[3] || 1;

            if (struct[8] !== 'Z' && struct[9] !== undefined) {
                minutesOffset = struct[10] * 60 + struct[11];

                if (struct[9] === '+') {
                    minutesOffset = 0 - minutesOffset;
                }
            }

            timestamp = Date.UTC(struct[1], struct[2], struct[3], struct[4], struct[5] + minutesOffset, struct[6], struct[7]);
        }
        else {
            timestamp = origParse ? origParse(date) : NaN;
        }

        return timestamp;
    };
}(Date));
/**
 * @name jsTimezoneDetect
 * @version 1.0.5
 * @author Jon Nylander
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 * For usage and examples, visit: http://pellepim.bitbucket.org/jstz/
 * Copyright (c) Jon Nylander
 */
var jstz=function(){"use strict"
var a="s",i=function(a){var i=-a.getTimezoneOffset()
return null!==i?i:0},e=function(a,i,e){var r=new Date
return void 0!==a&&r.setFullYear(a),r.setMonth(i),r.setDate(e),r},r=function(a){return i(e(a,0,2))},n=function(a){return i(e(a,5,2))},t=function(a){var e=a.getMonth()>7,t=e?n(a.getFullYear()):r(a.getFullYear()),s=i(a),c=0>t,A=t-s
return c||e?0!==A:0>A},s=function(){var i=r(),e=n(),t=i-e
return 0>t?i+",1":t>0?e+",1,"+a:i+",0"},c=function(){var a=s()
return new jstz.TimeZone(jstz.olson.timezones[a])},A=function(){return s()},o=function(){return jstz.olson.timezones},u=function(a){var i=new Date(2010,6,15,1,0,0,0),e={"America/Denver":new Date(2011,2,13,3,0,0,0),"America/Mazatlan":new Date(2011,3,3,3,0,0,0),"America/Chicago":new Date(2011,2,13,3,0,0,0),"America/Mexico_City":new Date(2011,3,3,3,0,0,0),"America/Asuncion":new Date(2012,9,7,3,0,0,0),"America/Santiago":new Date(2012,9,3,3,0,0,0),"America/Campo_Grande":new Date(2012,9,21,5,0,0,0),"America/Montevideo":new Date(2011,9,2,3,0,0,0),"America/Sao_Paulo":new Date(2011,9,16,5,0,0,0),"America/Los_Angeles":new Date(2011,2,13,8,0,0,0),"America/Santa_Isabel":new Date(2011,3,5,8,0,0,0),"America/Havana":new Date(2012,2,10,2,0,0,0),"America/New_York":new Date(2012,2,10,7,0,0,0),"Europe/Helsinki":new Date(2013,2,31,5,0,0,0),"Pacific/Auckland":new Date(2011,8,26,7,0,0,0),"America/Halifax":new Date(2011,2,13,6,0,0,0),"America/Goose_Bay":new Date(2011,2,13,2,1,0,0),"America/Miquelon":new Date(2011,2,13,5,0,0,0),"America/Godthab":new Date(2011,2,27,1,0,0,0),"Europe/Moscow":i,"Asia/Amman":new Date(2013,2,29,1,0,0,0),"Asia/Beirut":new Date(2013,2,31,2,0,0,0),"Asia/Damascus":new Date(2013,3,6,2,0,0,0),"Asia/Jerusalem":new Date(2013,2,29,5,0,0,0),"Asia/Yekaterinburg":i,"Asia/Omsk":i,"Asia/Krasnoyarsk":i,"Asia/Irkutsk":i,"Asia/Yakutsk":i,"Asia/Vladivostok":i,"Asia/Baku":new Date(2013,2,31,4,0,0),"Asia/Yerevan":new Date(2013,2,31,3,0,0),"Asia/Kamchatka":i,"Asia/Gaza":new Date(2010,2,27,4,0,0),"Africa/Cairo":new Date(2010,4,1,3,0,0),"Europe/Minsk":i,"Pacific/Apia":new Date(2010,10,1,1,0,0,0),"Pacific/Fiji":new Date(2010,11,1,0,0,0),"Australia/Perth":new Date(2008,10,1,1,0,0,0)}
return e[a]}
return{determine:c,offset:A,timezones:o,date_is_dst:t,dst_start_for:u}}()
jstz.TimeZone=function(a){"use strict"
var i={"America/Denver":["America/Denver","America/Mazatlan"],"America/Chicago":["America/Chicago","America/Mexico_City"],"America/Santiago":["America/Santiago","America/Asuncion","America/Campo_Grande"],"America/Montevideo":["America/Montevideo","America/Sao_Paulo"],"Asia/Beirut":["Asia/Amman","Asia/Jerusalem","Asia/Beirut","Europe/Helsinki","Asia/Damascus"],"Pacific/Auckland":["Pacific/Auckland","Pacific/Fiji"],"America/Los_Angeles":["America/Los_Angeles","America/Santa_Isabel"],"America/New_York":["America/Havana","America/New_York"],"America/Halifax":["America/Goose_Bay","America/Halifax"],"America/Godthab":["America/Miquelon","America/Godthab"],"Asia/Dubai":["Europe/Moscow"],"Asia/Dhaka":["Asia/Yekaterinburg"],"Asia/Jakarta":["Asia/Omsk"],"Asia/Shanghai":["Asia/Krasnoyarsk","Australia/Perth"],"Asia/Tokyo":["Asia/Irkutsk"],"Australia/Brisbane":["Asia/Yakutsk"],"Pacific/Noumea":["Asia/Vladivostok"],"Pacific/Tarawa":["Asia/Kamchatka","Pacific/Fiji"],"Pacific/Tongatapu":["Pacific/Apia"],"Asia/Baghdad":["Europe/Minsk"],"Asia/Baku":["Asia/Yerevan","Asia/Baku"],"Africa/Johannesburg":["Asia/Gaza","Africa/Cairo"]},e=a,r=function(){for(var a=i[e],r=a.length,n=0,t=a[0];r>n;n+=1)if(t=a[n],jstz.date_is_dst(jstz.dst_start_for(t)))return e=t,void 0},n=function(){return typeof i[e]!="undefined"}
return n()&&r(),{name:function(){return e}}},jstz.olson={},jstz.olson.timezones={"-720,0":"Pacific/Majuro","-660,0":"Pacific/Pago_Pago","-600,1":"America/Adak","-600,0":"Pacific/Honolulu","-570,0":"Pacific/Marquesas","-540,0":"Pacific/Gambier","-540,1":"America/Anchorage","-480,1":"America/Los_Angeles","-480,0":"Pacific/Pitcairn","-420,0":"America/Phoenix","-420,1":"America/Denver","-360,0":"America/Guatemala","-360,1":"America/Chicago","-360,1,s":"Pacific/Easter","-300,0":"America/Bogota","-300,1":"America/New_York","-270,0":"America/Caracas","-240,1":"America/Halifax","-240,0":"America/Santo_Domingo","-240,1,s":"America/Santiago","-210,1":"America/St_Johns","-180,1":"America/Godthab","-180,0":"America/Argentina/Buenos_Aires","-180,1,s":"America/Montevideo","-120,0":"America/Noronha","-60,1":"Atlantic/Azores","-60,0":"Atlantic/Cape_Verde","0,0":"UTC","0,1":"Europe/London","60,1":"Europe/Berlin","60,0":"Africa/Lagos","60,1,s":"Africa/Windhoek","120,1":"Asia/Beirut","120,0":"Africa/Johannesburg","180,0":"Asia/Baghdad","180,1":"Europe/Moscow","210,1":"Asia/Tehran","240,0":"Asia/Dubai","240,1":"Asia/Baku","270,0":"Asia/Kabul","300,1":"Asia/Yekaterinburg","300,0":"Asia/Karachi","330,0":"Asia/Kolkata","345,0":"Asia/Kathmandu","360,0":"Asia/Dhaka","360,1":"Asia/Omsk","390,0":"Asia/Rangoon","420,1":"Asia/Krasnoyarsk","420,0":"Asia/Jakarta","480,0":"Asia/Shanghai","480,1":"Asia/Irkutsk","525,0":"Australia/Perth","540,1":"Asia/Yakutsk","540,0":"Asia/Tokyo","570,0":"Australia/Darwin","570,1,s":"Australia/Adelaide","600,0":"Australia/Brisbane","600,1":"Asia/Vladivostok","600,1,s":"Australia/Sydney","630,1,s":"Australia/Lord_Howe","660,1":"Asia/Kamchatka","660,0":"Pacific/Noumea","690,0":"Pacific/Norfolk","720,1,s":"Pacific/Auckland","720,0":"Pacific/Tarawa","765,1,s":"Pacific/Chatham","780,0":"Pacific/Tongatapu","780,1,s":"Pacific/Apia","840,0":"Pacific/Kiritimati"};
  /*
   * Provide Date.toISOString() for older (i.e. MSIE8) browsers
   */
  if (!Date.prototype.toISOString) {
    Date.prototype.toISOString = function (key) {
      function pad(n) {return parseInt(n) < 10 ? '0' + n : n;}
      return this.getUTCFullYear()   + '-' +
        pad(this.getUTCMonth() + 1) + '-' +
        pad(this.getUTCDate())      + 'T' +
        pad(this.getUTCHours())     + ':' +
        pad(this.getUTCMinutes())   + ':' +
        pad(this.getUTCSeconds())   + 'Z';
    };
  }


  /**
   * With all that frameworky stuff out of the way, let's move on to the real code
   */
  // Picker object
  var DateTimePicker = function(element, options) {
    this.id = dpgId++;
    this.init(element, options);
  };

  DateTimePicker.prototype = {
    jstz: jstz,
    _date: new Date(),

    constructor: DateTimePicker,

    init: function(element, options) {
      var icon;
      this.options = options;
      this.$element = $(element);
      this.language = 'en';
      //jQuery 1.9 ditched $.browser, so I'm checking for leadingWhitespace support
      //(something IE6-8 don't do)
      if ($.support.leadingWhitespace === false) {
	$('<div class="alert alert-danger">This plugin requires a moderately modern browser.<br/> Did you know Internet Explorer 8 was released way back in 2009? George W Bush was still president of America in 2009, which is also the year the film <em>Avatar</em> was released.<br/>Might be time to upgrade... </div>').insertAfter(this.$element);
	this.disable();
	return false;
      }
      else {

	this.isInput = this.$element.is('input');
	this.component = false;
	if (this.$element.is('.input-append') || this.$element.is('.input-prepend')) {
          this.component = this.$element.find('.add-on');
          $('<span class="help-block">' +
	    'Datetime format is ' +
	    '<a href="http://en.wikipedia.org/wiki/ISO_8601">' +
	    'ISO-8601' +
	    '</a>, UTC' +
	    '</span>').insertAfter(this.$element);
	}

	this.format = 'iso8601';

	if (this.component) {
          icon = this.component.find('i');
	}
	if (icon && icon.length) this.timeIcon = icon.data('time-icon');
	if (!this.timeIcon) this.timeIcon = 'icon-time';
	icon.addClass(this.timeIcon);

	if (icon && icon.length) this.dateIcon = icon.data('date-icon');
	if (!this.dateIcon) this.dateIcon = 'icon-calendar';
	icon.removeClass(this.timeIcon);
	icon.addClass(this.dateIcon);

	this._timezone = this.jstz.determine().name();
	this._oldtz = this._timezone;

	var templateopts = {
	  timeIcon: this.timeIcon,
	  collapse: options.collapse,
	  currTZ: this._timezone};

	this.widget = $(getTemplate(templateopts)).appendTo('body');
	this.startViewMode = this.viewMode = this.minViewMode = 0;

	this.weekStart = options.weekStart||this.$element.data('date-weekstart')||0;
	this.weekEnd = this.weekStart === 0 ? 6 : this.weekStart - 1;
	this.fillDow();
	this.fillMonths();
	this.fillHours();
	this.fillMinutes();
	this.fillSeconds();

	var zones = [];

	$.each(WallTime.data.zones,
	       function(name, zone) {
		 zones.push({name: name, offset:zone[0]['_offset']});
	       });

	zones = zones.sort(function(a,b) {
	  var ao = parseFloat(a.offset.replace(':', '.'), 10);
	  var bo = parseFloat(b.offset.replace(':', '.'), 10);
	  return ao - bo;
	});
	this.fillTZ(zones);

	this.setup();
	this.set();

	this.showMode();
	this._attachDatePickerEvents();
      }
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

    makeoffset: function(offset) {
      //offset is in minutes; we want [+-]HHMM
      offset = parseFloat(offset/60).toFixed(2);

      var hours = offset.split('.')[0].toString();
      var minutes = (parseInt((offset.split('.')[1] / 100) * 60)).toString();

      if (hours.length === 1 || hours.substr(0,1) === '-' && hours.length === 2) {
	hours = "0" + hours;
      }

      if (hours.substr(0,1) !== '-') {
	hours = "+" + hours;
      }

      if (minutes.length === 1) {
	minutes = "0" + minutes;
      }

      return hours + minutes;
    },

    // this sets the text box value with the value of this._date
    set: function() {
      var formatted = !this._unset ? this._date.toISOString().replace(/\.?\d*Z$/, 'Z') : '';
      if (!this.isInput) {
        if (this.component){
          var input = this.$element.find('input');
          input.val(formatted);
        }
      } else {
        this.$element.val(formatted);
      }
    },

    // this sets the value of this._date to the supplied newDate,
    // and resets the view (this.fillDate(), this.fillTime()) after
    // calling this.set()
    propagate: function(newDate) {
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
      this.fillDate();
      this.fillTime();
    },

    getDate: function() {
      if (this._unset) return null;
      return new Date(this._date.valueOf());
    },

    setDate: function(date) {
      if (!date) this.propagate(null);
      else this.propagate(date.valueOf());
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

    // this exposes the current datetime via a trigger
    notifyChange: function(){
      this.$element.trigger({
        type: 'changeDate',
        utcTime: this._walltime.utc,
	tzTime: this._walltime.wallTime
      });
    },

    // this retrieves the value of the text box
    // and resets this._date accordingly.
    // the view is the reset (via this.fillDate(),
    // this.fillTime())
    setup: function(){
      var dateStr;
      if (this.isInput) {
        dateStr = this.$element.val();
      } else {
        dateStr = this.$element.find('input').val();
      }
      if (dateStr) {
        this._date = this.parseDate(dateStr);
	this.set();
      }
      else {
	var nd = new Date();
	this._date = this.parseDate(nd.toISOString());
      }
      this.fillDate();
      this.fillTime();
    },

    parseDate: function(str) {
      this._walltime = WallTime.UTCToWallTime(Date.UTC(1970, 0, 1, 0, 0, 0, Date.parse(str)),
					      this._timezone);
      return this._walltime.utc;
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

    fillDate: function(theDate) {
      if (typeof(theDate) === 'undefined') {
	theDate = this._walltime.wallTime;
      }

      var year = theDate.getUTCFullYear();
      var month = theDate.getUTCMonth();
      var currentDate = UTCDate(
        theDate.getUTCFullYear(),
        theDate.getUTCMonth(),
        theDate.getUTCDate(),
        0, 0, 0, 0
      );

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

      var nextMonth = new Date(prevMonth.valueOf());
      nextMonth.setUTCDate(nextMonth.getDate() + 42);
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
        if (prevMonth.getDay() === this.weekEnd) {
          html.push('</tr>');
        }
        prevMonth.setUTCDate(prevMonth.getUTCDate() + 1);
      }
      this.widget.find('.datepicker-days tbody').empty().append(html.join(''));
      var currentYear = theDate.getUTCFullYear();

      var months = this.widget.find('.datepicker-months').find(
        'th:eq(1)').text(year).end().find('span').removeClass('active');
      if (currentYear === year) {
        months.eq(theDate.getUTCMonth()).addClass('active');
      }

      html = '';
      year = parseInt(year/10, 10) * 10;
      var yearCont = this.widget.find('.datepicker-years').find(
        'th:eq(1)').text(year + '-' + (year + 9)).end().find('td');
      this.widget.find('.datepicker-years').find('th').removeClass('disabled');
      year -= 1;
      for (var i = -1; i < 11; i++) {
        html += '<span class="year' + (i === -1 || i === 10 ? ' old' : '') + (currentYear === year ? ' active' : '') +  '">' + year + '</span>';
        year += 1;
      }
      yearCont.html(html);
    },

    fillHours: function() {
      var table = this.widget.find(
        '.timepicker .timepicker-hours table');
      table.parent().hide();
      var html = '';
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
      list.append('<li class="alert-warning alert">' +
		    '<small>Offsets are for standard time; ' +
		    'daylight saving time offsets are automatically ' +
		    'calculated as required.</small>' +
		  '</li>');
      list.append('<li><button data-tz-toggle="true" class="btn-small btn-block btn-success">Show all timezones</button></li>');
      $.each(timezones, function(idx, tz) {
	var offset = tz.offset;
	if (offset.substr(0,1) !== '-') {
	  offset = "+" + offset;
	}
	var name = tz.name;
	var intl = name.indexOf('Australia/') !== 0 ? 'yes' : 'no';

	var li = $('<li data-intl="' + intl + '" />');
	var button = $('<button class="btn btn-block" ' +
		       'data-tz="' + name + '" />');

	if (name !== "UTC") {
	  button.html(name + ' <small>(' + offset + ')</small>');
	}
	else {
	  button.html(name);
	}

	li.append(button);
	list.append(li);
      });
    },

    fillTime: function(theTime) {
      if (typeof(theTime) === 'undefined') {
	theTime = this._walltime.wallTime;
      }

      if (!theTime)
        return;

      var timeComponents = this.widget.find('.timepicker span[data-time-component]');
      var table = timeComponents.closest('table');
      var hour = theTime.getUTCHours();

      hour = padLeft(hour.toString(), 2, '0');
      var minute = padLeft(theTime.getUTCMinutes().toString(), 2, '0');
      var second = padLeft(theTime.getUTCSeconds().toString(), 2, '0');
      timeComponents.filter('[data-time-component=hours]').text(hour);
      timeComponents.filter('[data-time-component=minutes]').text(minute);
      timeComponents.filter('[data-time-component=seconds]').text(second);
    },

    click: function(e) {
      e.stopPropagation();
      e.preventDefault();
      this._unset = false;
      var target = $(e.target).closest('span, td, th');
      var newdate = this._walltime.wallTime;
      var doNotify = false;


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
              var navFnc = DPGlobal.modes[this.viewMode].navFnc;
              var step = DPGlobal.modes[this.viewMode].navStep;
              if (target[0].className === 'prev') step = step * -1;
              newdate['set' + navFnc](newdate['get' + navFnc]() + step);
              break;
            }
	    doNotify = true;
            break;
          case 'span':
            if (target.is('.month')) {
              var month = target.parent().find('span').index(target);
              newdate.setUTCMonth(month);
            } else {
              var year = parseInt(target.text(), 10) || 0;
              newdate.setUTCFullYear(year);
            }
	    doNotify = true;
            this.showMode(-1);
            break;
          case 'td':
            if (target.is('.day')) {
	      doNotify = true;
              var day = parseInt(target.text(), 10) || 1;
              var month = newdate.getUTCMonth();
              var year = newdate.getUTCFullYear();
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
	      newdate.setUTCDate(day);
	      newdate.setUTCMonth(month);
	      newdate.setUTCFullYear(year);
            }
            break;
          }

	  this._walltime = WallTime.UTCToWallTime(WallTime.WallTimeToUTC(this._timezone,
									 newdate.getUTCFullYear(),
									 newdate.getUTCMonth(),
									 newdate.getUTCDate(),
									 newdate.getUTCHours(),
									 newdate.getUTCMinutes(),
									 newdate.getUTCSeconds()),
						  this._timezone);

          this._date = this._walltime.utc;
          this.fillDate();
          this.fillTime();
          this.set();
	  if (doNotify) {
            this.notifyChange();
	  }

        }
      }
    },

    actions: {
      incrementHours: function(e) {
	this._date.setUTCHours(this._date.getUTCHours() + 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      incrementMinutes: function(e) {
	this._date.setUTCMinutes(this._date.getUTCMinutes() + 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      incrementSeconds: function(e) {
	this._date.setUTCSeconds(this._date.getUTCSeconds() + 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      decrementHours: function(e) {
	this._date.setUTCHours(this._date.getUTCHours() - 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      decrementMinutes: function(e) {
	this._date.setUTCMinutes(this._date.getUTCMinutes() - 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
      },

      decrementSeconds: function(e) {
	this._date.setUTCSeconds(this._date.getUTCSeconds() - 1);
	this._walltime = WallTime.UTCToWallTime(this._date, this._timezone);
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
	this.widget.find('.timepicker .timepicker-tz button').removeClass('btn-primary');
	this.widget.find('.timepicker .timepicker-tz button[data-tz="' + this._timezone +'"]').addClass('btn-primary');
        this.widget.find('.timepicker .timepicker-tz').show();
	this.widget.find('.timepicker .timepicker-tz ul.unstyled li[data-intl=yes]').hide();
	this.widget.find('.timepicker .timepicker-tz ul.unstyled button.btn-primary').parent().show();
	this.widget.find('.timepicker .timepicker-tz ul.unstyled button.btn-success').parent().show();
      },

      selectTZ: function(e) {
	var tgt = $(e.target);
	if (tgt.is('button') && typeof(tgt.data('tz')) !== 'undefined') {
	  var tz = tgt.data('tz');
	  var label = $('span.timepicker-tz');
	  label.html('<i class="icon-globe"> </i> ' + tz);
	  this._walltime = WallTime.UTCToWallTime(this._date, tz);
	  this._oldtz = $.extend({}, true, this._timezone);
	  this._timezone = tz;
          this.actions.showPicker.call(this);
	}
	else if (tgt.is('button') && typeof(tgt.data('tz-toggle')) !== 'undefined') {
	  this.widget.find('.timepicker .timepicker-tz ul.unstyled li[data-intl=yes]').show();
	  tgt.parent().hide();
	}
      },

      selectHour: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
	var newdate = this._walltime.wallTime;
	newdate.setUTCHours(value);
	this._walltime = WallTime.UTCToWallTime(WallTime.WallTimeToUTC(this._timezone,
								       newdate.getUTCFullYear(),
								       newdate.getUTCMonth(),
								       newdate.getUTCDate(),
								       newdate.getUTCHours(),
								       newdate.getUTCMinutes(),
								       newdate.getUTCSeconds()),
						this._timezone);
        this.actions.showPicker.call(this);
      },

      selectMinute: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
	var newdate = this._walltime.wallTime;
	newdate.setUTCMinutes(value);
	this._walltime = WallTime.UTCToWallTime(WallTime.WallTimeToUTC(this._timezone,
								       newdate.getUTCFullYear(),
								       newdate.getUTCMonth(),
								       newdate.getUTCDate(),
								       newdate.getUTCHours(),
								       newdate.getUTCMinutes(),
								       newdate.getUTCSeconds()),
						this._timezone);
        this.actions.showPicker.call(this);
      },

      selectSecond: function(e) {
        var tgt = $(e.target);
        var value = parseInt(tgt.text(), 10);
	var newdate = this._walltime.wallTime;
	newdate.setUTCSeconds(value);
	this._walltime = WallTime.UTCToWallTime(WallTime.WallTimeToUTC(this._timezone,
								       newdate.getUTCFullYear(),
								       newdate.getUTCMonth(),
								       newdate.getUTCDate(),
								       newdate.getUTCHours(),
								       newdate.getUTCMinutes(),
								       newdate.getUTCSeconds()),
						this._timezone);
        this.actions.showPicker.call(this);
      }

    },

    doAction: function(e) {
      e.stopPropagation();
      e.preventDefault();
      var target = $(e.target);
      if (target.parent().is('button')) {
	target.parent().trigger('click');
      }
      else if (target.parent().parent().is('button')) {
	target.parent().parent().trigger('click');
      }
      else if (target.is('table, tr, tbody, thead, th')) {
	return;
      }

      var action = $(e.currentTarget).data('action');
      var rv = this.actions[action].apply(this, arguments);
      this._date = this._walltime.utc;
      this.set();
      this.fillDate();
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
        this.setup();
        this.propagate(this._date.getTime());
        this.notifyChange();
        this.set();
      } else if (val && val.trim()) {
        this.propagate(this._date.getTime());
        if (this._date) this.set();
        else input.val('');
      } else {
        if (this._date) {
          this.propagate(null);
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

    _attachDatePickerEvents: function() {
      var self = this;
      // this handles date picker clicks
      this.widget.on('click', '.datepicker *', $.proxy(this.click, this));
      // this handles time picker clicks
      this.widget.on('click', '[data-action]', $.proxy(this.doAction, this));
      this.widget.on('mousedown', $.proxy(this.stopEvent, this));
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
      this.widget.off('click.togglePicker');

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
        $this.data('datetimepicker',
		   (data = new DateTimePicker(
		     this,
		     $.extend({},
			      $.fn.datetimepicker.defaults,
			      options))));
      }
      if (typeof option === 'string') data[option](val);
    });
  };

  $.fn.datetimepicker.defaults = {
    maskInput: true,
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


  function escapeRegExp(str) {
    // http://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
  }

  function padLeft(s, l, c) {
    if (l < s.length) return s;
    else return Array(l - s.length + 1).join(c || ' ') + s;
  }

  function getTemplate(opts) {
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
                TPGlobal.getTemplate({currTz: opts.currTZ}) +
              '</div>' +
            '</li>' +
          '</ul>' +
        '</div>'
      );
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
      container.html('<i class="icon-globe"> </i> ' + opts.current.replace('_', ' '));
      wrapper.append(container);
      return wrapper.html();
    }
  };
  TPGlobal.getTemplate = function(opts) {
    return (
    '<div class="timepicker-picker">' +
      '<table class="table-condensed">' +
        '<tr>' +
          '<td><a href="#" class="btn" data-action="incrementHours"><i class="icon-chevron-up"></i></a></td>' +
          '<td class="separator"></td>' +
          '<td><a href="#" class="btn" data-action="incrementMinutes"><i class="icon-chevron-up"></i></a></td>' +
	  '<td class="separator"></td>' +
          '<td><a href="#" class="btn" data-action="incrementSeconds"><i class="icon-chevron-up"></i></a></td>' +
        '</tr>' +
        '<tr>' +
          '<td>' + TPGlobal.hourTemplate + '</td> ' +
          '<td class="separator">:</td>' +
          '<td>' + TPGlobal.minuteTemplate + '</td> ' +
          '<td class="separator">:</td>' +
          '<td>' + TPGlobal.secondTemplate + '</td>' +
        '</tr>' +
        '<tr>' +
          '<td><a href="#" class="btn" data-action="decrementHours"><i class="icon-chevron-down"></i></a></td>' +
          '<td class="separator"></td>' +
          '<td><a href="#" class="btn" data-action="decrementMinutes"><i class="icon-chevron-down"></i></a></td>' +
	  '<td class="separator"></td>' +
          '<td><a href="#" class="btn" data-action="decrementSeconds"><i class="icon-chevron-down"></i></a></td>' +
        '</tr>' +
	 '<tr>' +
	   '<td colspan="5">' +
	     TPGlobal.tzTemplate({current:opts.currTz}) +
	   '</td>' +
	 '</tr>' +
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
    '<div class="timepicker-seconds" data-action="selectSecond">' +
      '<table class="table-condensed">' +
      '</table>'+
    '</div>'+
    '<div class="timepicker-tz" data-action="selectTZ">' +
      '<ul class="unstyled">' +
      '</ul>' +
    '</div>'
    );
  }
})( jQuery )
