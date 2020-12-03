(window.webpackJsonp=window.webpackJsonp||[]).push([[16],{105:function(t,e,a){"use strict";var s=a(1),i=a.n(s),r=a(6),n=a.n(r);function o(t,e,a,s,i,r,n){try{var o=t[r](n),l=o.value}catch(t){return void a(t)}o.done?e(l):Promise.resolve(l).then(s,i)}function l(t){return function(){var e=this,a=arguments;return new Promise((function(s,i){var r=t.apply(e,a);function n(t){o(r,s,i,n,l,"next",t)}function l(t){o(r,s,i,n,l,"throw",t)}n(void 0)}))}}var c={name:"ShiftCard",mixins:[a(17).a],props:{shift:{type:Object},schedule:{type:Object},background:{type:String,default:""},width:{type:[String,Number],default:"100%"},height:{type:[String,Number],default:"170px"},onlyinfo:{type:Boolean,default:!1}},data:function(){return{request:!1,trade:!1}},created:function(){this.request=this.isShiftMember,this.trade=4==this.myShiftStatus},computed:{isShiftMember:function(){return this.shift.users.map((function(t){return t.id})).indexOf(this.user.id)>-1},hasRequestedTrade:function(){return 4==this.myShiftStatus},myShiftStatus:function(){var t=this,e=null,a=this.shift.users.filter((function(e){return e.id===t.user.id}));return a.length>0&&(e=a[0].pivot.status),e},shiftFull:function(){var t=!1;1==this.team.setting_shift_request_autoapproval&&(t=this.shift.users.length>=this.shift.max_participants);return t}},methods:{showButton:function(t){var e=!1;switch(t){case"request":e=1==this.schedule.status&&(!this.shiftFull||this.request)||2==this.schedule.status&&(0==this.myShiftStatus||1==this.myShiftStatus);break;case"trade":e=2==this.schedule.status&&this.myShiftStatus>1}return e},dayOfWeek:function(t){var e=[];return(e=this.$dayjs().localeData().weekdaysShort())[7]=e[0],e[t]},updateShiftUser:function(){var t=this;return l(i.a.mark((function e(){var a,s;return i.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return a=1==t.team.setting_shift_request_autoapproval?2:1,s=null,t.request=!t.request,s=t.request?"/api/schedules/joinshift":"/api/schedules/leaveshift",e.next=6,n()({method:"post",url:s,data:{user_id:t.user.id,shift_id:t.shift.id,status:a}}).then((function(e){t.shift.users=e.data.shiftusers}));case 6:case"end":return e.stop()}}),e)})))()},updateTrade:function(){var t=this;return l(i.a.mark((function e(){var a;return i.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return a=null,t.trade=!t.trade,a=t.trade?4:2,e.next=5,n()({method:"post",url:"/api/schedules/shiftuserstatus",data:{user_id:t.user.id,shift_id:t.shift.id,status:a}}).then((function(e){t.shift.users=e.data.shiftusers}));case 5:case"end":return e.stop()}}),e)})))()},returnZero:function(t){return t<0?0:t}}},u=(a(356),a(5)),f=a(13),d=a.n(f),h=a(87),v=a(157),p=a(10),m=a(598),_=a(150),y=a(75),b=a(599),x=Object(u.a)(c,(function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-card",{style:"background-color: "+t.background,attrs:{outlined:"",hover:"",width:t.width}},[a("v-card-title",{staticClass:"justify-center text-h6",style:"background-color: "+(null!==t.shift.location.color_code?t.shift.location.color_code:"")},[t._v("\n    "+t._s(t.shift.location.name)+"\n  ")]),t._v(" "),a("v-card-subtitle",{staticClass:"text-center",style:"background-color: "+(null!==t.shift.location.color_code?t.shift.location.color_code:"")},[t._v("\n    "+t._s(t.dayOfWeek(t.$dayjs(t.shift.time_start).isoWeekday()))+"\n    "+t._s(t._f("formatTime")(t.shift.time_start))+" - "+t._s(t._f("formatTime")(t.shift.time_end))+"\n  ")]),t._v(" "),a("v-card-text",{style:"overflow-y: auto; height: "+t.height},[t._l(t.shift.users,(function(e){return a("div",{key:e.id,staticClass:"ma-2",attrs:{title:t.shiftStatus[e.pivot.status].text,disabled:""}},[a("v-icon",{staticClass:"ml-n4 mr-2",attrs:{color:t.shiftStatus[e.pivot.status].color}},[t._v(t._s(t.shiftStatus[e.pivot.status].icon))]),t._v(" "),a("span",{class:t.shiftStatus[e.pivot.status].color+"--text"},[t._v(t._s(e.name))])],1)})),t._v(" "),t._l(t.returnZero(t.shift.max_participants-t.shift.users.length),(function(e){return a("div",{key:e,staticClass:"ma-2",attrs:{disabled:""}},[a("div",{staticClass:"ml-n5 dashed-border rounded",attrs:{width:"100%"}},[a("v-icon",{staticClass:"ml-1 mr-2",attrs:{color:"grey"}},[t._v("mdi-account-outline")]),t._v(" "),a("span",[t._v(t._s(t.$t("general.available")))])],1)])}))],2),t._v(" "),a("v-divider",{staticClass:"ma-0"}),t._v(" "),t.onlyinfo?t._e():a("v-card-actions",[a("v-row",{attrs:{dense:""}},[a("v-col",{staticClass:"text-center"},[a("v-btn",{staticClass:"me-2",attrs:{disabled:!t.showButton("request"),color:(t.request&&t.myShiftStatus,""),icon:""},on:{click:function(e){return e.stopPropagation(),t.updateShiftUser(e)}}},[a("v-icon",[t._v(t._s(t.request?"mdi-account-minus":"mdi-account-plus"))])],1)],1),t._v(" "),a("v-col",{staticClass:"text-center"},[a("v-btn",{attrs:{disabled:!t.showButton("trade"),color:t.trade?"blue":"",icon:""},on:{click:function(e){return e.stopPropagation(),t.updateTrade(e)}}},[a("v-icon",[t._v("mdi-account-convert")])],1)],1)],1)],1)],1)}),[],!1,null,"0cff391d",null);e.a=x.exports;d()(x,{VBtn:h.a,VCard:v.a,VCardActions:p.a,VCardSubtitle:p.b,VCardText:p.c,VCardTitle:p.d,VCol:m.a,VDivider:_.a,VIcon:y.a,VRow:b.a})},129:function(t,e,a){"use strict";a.r(e);var s=a(6),i=a.n(s),r=a(17),n=a(1),o=a.n(n),l=a(166),c=a.n(l);function u(t,e,a,s,i,r,n){try{var o=t[r](n),l=o.value}catch(t){return void a(t)}o.done?e(l):Promise.resolve(l).then(s,i)}var f={name:"ShiftStatistics",mixins:[r.a],components:{VueApexCharts:c.a},data:function(){var t,e,a,s=this;return{stats:[],seriesPlaces:[],optionsPlaces:(t={chart:{height:350,type:"radialBar"},labels:["Available Spots","Trades"],colors:["#2196F3","#00BCD4"],fill:{type:"gradient",gradient:{shade:"dark",type:"vertical",shadeIntensity:.5,inverseColors:!0,opacityFrom:1,opacityTo:1,stops:[0,100]}},stroke:{lineCap:"round"},plotOptions:{radialBar:{hollow:{margin:0,size:"40%",background:"#222222",image:void 0,position:"front",dropShadow:{enabled:!0,top:0,left:0,blur:4,opacity:.24}},track:{background:["#BBDEFB","#E0F7FA"]},dataLabels:{name:{fontSize:"14px"},value:{fontSize:"14px",color:"#ffffff",formatter:function(t){return Math.round(t/100*s.stats.total_spots)}},total:{show:!0,label:"Total Spots",fontSize:"14px",color:"#ffffff",formatter:function(t){return s.stats.total_spots}}}}}},e="stroke",a={lineCap:"round"},e in t?Object.defineProperty(t,e,{value:a,enumerable:!0,configurable:!0,writable:!0}):t[e]=a,t),seriesShifts:[],optionsShifts:{chart:{height:350,type:"radialBar"},labels:["Shift with Needs","Shifts with Trades"],colors:["#2196F3","#00BCD4"],fill:{type:"gradient",gradient:{shade:"dark",type:"vertical",shadeIntensity:.5,inverseColors:!0,opacityFrom:1,opacityTo:1,stops:[0,100]}},stroke:{lineCap:"round"},plotOptions:{radialBar:{hollow:{margin:0,size:"40%",background:"#222222",image:void 0,position:"front",dropShadow:{enabled:!0,top:0,left:0,blur:4,opacity:.24}},track:{background:["#BBDEFB","#E0F7FA"]},dataLabels:{name:{fontSize:"14px"},value:{fontSize:"14px",color:"#ffffff",formatter:function(t){return Math.round(t/100*s.stats.total_shifts)}},total:{show:!0,label:"Total Shifts",fontSize:"14px",color:"#ffffff",formatter:function(t){return s.stats.total_shifts}}}}}}}},created:function(){this.getStats()},methods:{getStats:function(){var t,e=this;return(t=o.a.mark((function t(){return o.a.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,i.a.get("/api/teams/"+e.team.id+"/stats").then((function(t){e.stats=t.data.stats;var a=e.stats.available_spots/e.stats.total_spots*100,s=e.stats.available_trades/e.stats.total_spots*100;e.seriesPlaces.push(a),e.seriesPlaces.push(s);var i=e.stats.shifts_with_needs/e.stats.total_shifts*100,r=e.stats.shifts_with_trades/e.stats.total_shifts*100;e.seriesShifts.push(i),e.seriesShifts.push(r)}));case 2:case"end":return t.stop()}}),t)})),function(){var e=this,a=arguments;return new Promise((function(s,i){var r=t.apply(e,a);function n(t){u(r,s,i,n,o,"next",t)}function o(t){u(r,s,i,n,o,"throw",t)}n(void 0)}))})()}}},d=a(5),h=a(13),v=a.n(h),p=a(157),m=a(598),_=a(75),y=a(599),b=a(37),x=a(24),w=Object(d.a)(f,(function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-card",[a("v-toolbar",{attrs:{flat:""}},[a("v-toolbar-title",[a("v-icon",{attrs:{left:""}},[t._v("mdi-gauge")]),t._v("\n        "+t._s(t.$t("general.statistics"))+"\n      ")],1)],1),t._v(" "),a("v-row",[a("v-col",{staticClass:"text-center",attrs:{sm:"6",lg:"12"}},[a("VueApexCharts",{attrs:{options:t.optionsPlaces,series:t.seriesPlaces,width:"100%"}})],1),t._v(" "),a("v-col",{staticClass:"text-center",attrs:{sm:"6",lg:"12"}},[a("VueApexCharts",{attrs:{options:t.optionsShifts,series:t.seriesShifts,width:"100%"}})],1)],1)],1)}),[],!1,null,null,null),S=w.exports;v()(w,{VCard:p.a,VCol:m.a,VIcon:_.a,VRow:y.a,VToolbar:b.a,VToolbarTitle:x.a});var g=a(105);function k(t,e,a,s,i,r,n){try{var o=t[r](n),l=o.value}catch(t){return void a(t)}o.done?e(l):Promise.resolve(l).then(s,i)}function C(t){return function(){var e=this,a=arguments;return new Promise((function(s,i){var r=t.apply(e,a);function n(t){k(r,s,i,n,o,"next",t)}function o(t){k(r,s,i,n,o,"throw",t)}n(void 0)}))}}var T={name:"TradeRequests",mixins:[r.a],props:{},components:{ShiftCard:g.a},data:function(){return{shiftOverlay:!1,shift:null,schedule:null,trades:[],headersShift:[{text:"",value:"view",align:"left"},{text:this.$t("shifts.day"),value:"day",align:"left"},{text:this.$t("shifts.location"),value:"location",align:"left"},{text:this.$t("shifts.trade_requests"),value:"tradewith",align:"left"}]}},created:function(){this.getTrades()},methods:{getTrades:function(){var t=this;return C(o.a.mark((function e(){return o.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,i.a.get("/api/teams/"+t.team.id+"/trades").then((function(e){t.trades=e.data.trades}));case 2:case"end":return e.stop()}}),e)})))()},makeTrade:function(t){var e=this;return C(o.a.mark((function a(){return o.a.wrap((function(a){for(;;)switch(a.prev=a.next){case 0:return a.next=2,e.$root.$confirm(e.$t("shifts.confirm_trade"),null,"primary");case 2:if(!a.sent){a.next=5;break}return a.next=5,i()({method:"post",url:"/api/teams/"+e.team.id+"/trades",data:{shift_id:t.pivot.shift_id,user_id:t.pivot.user_id}}).then((function(t){e.trades=t.data.trades.trades,e.showSnackbar(e.$t("shifts.success_trade_made"),"success")}));case 5:case"end":return a.stop()}}),a)})))()},showShiftOverlay:function(t){this.shift=t,this.schedule=t.schedule,this.shiftOverlay=!0}}},V=a(155),$=a(612),O=a(156),q=Object(d.a)(T,(function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-card",[a("v-data-table",{attrs:{headers:t.headersShift,items:t.trades,"disable-sort":"",width:"100%"},scopedSlots:t._u([{key:"top",fn:function(){return[a("v-toolbar",{attrs:{flat:""}},[a("v-toolbar-title",[a("v-icon",{attrs:{left:""}},[t._v("mdi-account-convert")]),t._v("\n          "+t._s(t.$t("shifts.trade_requests"))+"\n        ")],1)],1)]},proxy:!0},{key:"item.view",fn:function(e){var s=e.item;return[a("v-icon",{attrs:{small:""},on:{click:function(e){return t.showShiftOverlay(s)}}},[t._v("mdi-magnify")])]}},{key:"item.day",fn:function(e){var s=e.item;return[t._v("\n      "+t._s(t._f("formatDay")(s.time_start))),a("br"),t._v("\n      "+t._s(t._f("formatTime")(s.time_start))+" - "+t._s(t._f("formatTime")(s.time_end))+"\n    ")]}},{key:"item.shift_time",fn:function(t){t.item}},{key:"item.location",fn:function(e){var s=e.item;return[a("v-chip",{attrs:{label:"",small:"",color:s.location.color_code}},[t._v(t._s(s.location.name))])]}},{key:"header.tradewith",fn:function(e){e.header;return[t._v("\n      "+t._s(t.$t("shifts.trade_requests"))),a("br"),t._v("\n      "+t._s(t.$t("shifts.trade_click_to_accept"))+"\n    ")]}},{key:"item.tradewith",fn:function(e){var s=e.item;return t._l(s.trades,(function(e){return a("v-chip",{key:e.id,staticClass:"me-2",attrs:{color:"blue",label:"",small:"",disabled:e.id===t.user.id,outlined:e.id===t.user.id},on:{click:function(a){return t.makeTrade(e)}}},[a("v-icon",{attrs:{left:"",small:""}},[t._v("mdi-swap-horizontal-bold")]),t._v("\n         "+t._s(e.name)+"\n      ")],1)}))}}])}),t._v(" "),a("v-overlay",{attrs:{value:t.shiftOverlay},nativeOn:{click:function(e){t.shiftOverlay=!1}}},[a("ShiftCard",{attrs:{shift:t.shift,schedule:t.schedule,onlyinfo:"",width:"300px"}})],1)],1)}),[],!1,null,null,null),P=q.exports;v()(q,{VCard:p.a,VChip:V.a,VDataTable:$.a,VIcon:_.a,VOverlay:O.a,VToolbar:b.a,VToolbarTitle:x.a});function B(t,e,a,s,i,r,n){try{var o=t[r](n),l=o.value}catch(t){return void a(t)}o.done?e(l):Promise.resolve(l).then(s,i)}var j={name:"MyShifts",mixins:[r.a],props:{},components:{},data:function(){return{expanded:[],shiftOverlay:!1,shift:null,schedule:null,shifts:[],shiftsAll:[],allTeams:!1,headers:[{text:this.$t("teams.team_name"),value:"schedule.team.display_name",align:"left"},{text:this.$t("shifts.day"),value:"day",align:"left"},{text:this.$t("shifts.location"),value:"location",align:"left"},{text:"",value:"data-table-expand"}]}},created:function(){this.getShifts()},methods:{getShifts:function(){var t,e=this;return(t=o.a.mark((function t(){return o.a.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,i.a.get("/api/user/shifts").then((function(t){e.shiftsAll=t.data,e.shifts=t.data.filter((function(t){return t.schedule.team_id===e.team.id}))}));case 2:case"end":return t.stop()}}),t)})),function(){var e=this,a=arguments;return new Promise((function(s,i){var r=t.apply(e,a);function n(t){B(r,s,i,n,o,"next",t)}function o(t){B(r,s,i,n,o,"throw",t)}n(void 0)}))})()},update:function(){var t=this;this.allTeams?this.shifts=this.shiftsAll:this.shifts=this.shiftsAll.filter((function(e){return e.schedule.team_id===t.team.id}))}}},A=a(627),F=Object(d.a)(j,(function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-card",[a("v-data-table",{attrs:{headers:t.headers,items:t.shifts,"disable-sort":"","show-expand":"","single-expand":"",expanded:t.expanded,width:"100%"},on:{"update:expanded":function(e){t.expanded=e}},scopedSlots:t._u([{key:"top",fn:function(){return[a("v-toolbar",{attrs:{flat:""}},[a("v-toolbar-title",[a("v-icon",{attrs:{left:""}},[t._v("mdi-calendar-account")]),t._v("\n          "+t._s(t.$t("shifts.my_shifts"))+"\n        ")],1)],1),t._v(" "),a("v-switch",{staticClass:"mx-4",attrs:{label:t.$t("shifts.show_all_teams"),"hide-details":""},on:{change:t.update},model:{value:t.allTeams,callback:function(e){t.allTeams=e},expression:"allTeams"}})]},proxy:!0},{key:"item.day",fn:function(e){var s=e.item;return[t._v("\n      "+t._s(t._f("formatDay")(s.time_start))),a("br"),t._v("\n      "+t._s(t._f("formatTime")(s.time_start))+" - "+t._s(t._f("formatTime")(s.time_end))+"\n    ")]}},{key:"item.location",fn:function(e){var s=e.item;return[a("v-chip",{attrs:{label:"",small:"",color:s.location.color_code}},[t._v(t._s(s.location.name))])]}},{key:"expanded-item",fn:function(e){var s=e.headers,i=e.item;return[a("td",{attrs:{colspan:s.length}},[a("v-row",[a("v-col",{staticStyle:{"vertical-align":"top"},attrs:{cols:"12",sm:"6"}},[a("div",{staticClass:"text-overline"},[t._v("Participants")]),t._v(" "),t._l(i.users,(function(e){return a("div",{key:e.id,staticClass:"ma-2",attrs:{title:t.shiftStatus[e.pivot.status].text,disabled:""}},[a("v-icon",{staticClass:"ml-n4 mr-2",attrs:{color:t.shiftStatus[e.pivot.status].color}},[t._v(t._s(t.shiftStatus[e.pivot.status].icon))]),t._v(" "),a("span",{class:t.shiftStatus[e.pivot.status].color+"--text"},[t._v(t._s(e.name))])],1)}))],2),t._v(" "),a("v-col",{staticStyle:{"vertical-align":"top"},attrs:{cols:"12",sm:"6"}},[a("div",{staticClass:"text-overline"},[t._v("Location Details")])])],1)],1)]}}])})],1)}),[],!1,null,null,null),D=F.exports;v()(F,{VCard:p.a,VChip:V.a,VCol:m.a,VDataTable:$.a,VIcon:_.a,VRow:y.a,VSwitch:A.a,VToolbar:b.a,VToolbarTitle:x.a});var z={middleware:["auth","teams"],layout:"vuetify",mixins:[r.a],components:{ShiftStatistics:S,TradeRequests:P,MyShifts:D},data:function(){return{}},computed:{},created:function(){},methods:{}},E=a(630),R=a(626),I=Object(d.a)(z,(function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-container",{attrs:{fluid:""}},[a("v-row",[a("PageTitle",{attrs:{title:t.$t("general.home")}})],1),t._v(" "),a("v-row"),t._v(" "),a("v-row",[a("v-col",{attrs:{cols:"12",lg:"8"}},[a("MyShifts"),t._v(" "),a("v-spacer",{staticClass:"my-12"}),t._v(" "),a("TradeRequests")],1),t._v(" "),a("v-col",{attrs:{cols:"12",lg:"4"}},[t.$vuetify.breakpoint.smAndUp?a("ShiftStatistics"):t._e()],1)],1)],1)}),[],!1,null,null,null);e.default=I.exports;v()(I,{VCol:m.a,VContainer:E.a,VRow:y.a,VSpacer:R.a})},356:function(t,e,a){"use strict";var s=a(95);a.n(s).a},357:function(t,e,a){(t.exports=a(7)(!1)).push([t.i,"\n.dashed-border[data-v-0cff391d] \n{\n  border-style: dashed;\n  border-color: 'grey';\n  border-width: thin;\n}\n",""])},95:function(t,e,a){var s=a(357);"string"==typeof s&&(s=[[t.i,s,""]]);var i={hmr:!0,transform:void 0,insertInto:void 0};a(8)(s,i);s.locals&&(t.exports=s.locals)}}]);