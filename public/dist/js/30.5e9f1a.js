(window.webpackJsonp=window.webpackJsonp||[]).push([[30],{176:function(e,t,r){"use strict";r.r(t);var a=r(1),s=r.n(a),n=r(6),o=r.n(n),i=r(130),u=r(131),d=r(24);function c(e,t,r,a,s,n,o){try{var i=e[n](o),u=i.value}catch(e){return void r(e)}i.done?t(u):Promise.resolve(u).then(a,s)}function l(e){return function(){var t=this,r=arguments;return new Promise((function(a,s){var n=e.apply(t,r);function o(e){c(n,a,s,o,i,"next",e)}function i(e){c(n,a,s,o,i,"throw",e)}o(void 0)}))}}var m={layout:"vuetify",middleware:"guest",mixins:[r(11).a],components:{LoginWithGoogle:i.a,LoginWithFacebook:u.a},validations:{name:{required:d.required},email:{required:d.required,email:d.email},password:{required:d.required,minLength:Object(d.minLength)(6)},password2:{required:d.required,sameAsPassword:Object(d.sameAs)("password")}},data:function(){return{name:"",email:"",password:"",password2:"",showPwd:!1,showPwd2:!1,mustVerifyEmail:!1}},computed:{nameErrors:function(){var e=[];return this.$v.name.$dirty?(!this.$v.name.required&&e.push(this.$t("auth.name_required")),e):e},emailErrors:function(){var e=[];return this.$v.email.$dirty?(!this.$v.email.email&&e.push(this.$t("auth.email_invalid")),!this.$v.email.required&&e.push(this.$t("auth.email_required")),e):e},passwordErrors:function(){var e=[];return this.$v.password.$dirty?(!this.$v.password.minLength&&e.push(this.$t("auth.password_length",{length:"6"})),!this.$v.password.required&&e.push(this.$t("auth.password_required")),e):e},passwordErrors2:function(){var e=[];return this.$v.password2.$dirty?(!this.$v.password2.sameAsPassword&&e.push(this.$t("auth.password_mismatch")),e):e}},methods:{register:function(){var e=this;return l(s.a.mark((function t(){return s.a.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:if(e.$v.$touch(),e.$v.$invalid){t.next=4;break}return t.next=4,o()({method:"post",url:"/api/register",data:{name:e.name,email:e.email,password:e.password,password_confirmation:e.password2}}).then((function(t){t.data.status?e.mustVerifyEmail=!0:e.login(t.data)})).catch((function(t){e.showSnackbar(e.$t("auth.error_registration"),"error")}));case 4:case"end":return t.stop()}}),t)})))()},login:function(e){var t=this;return l(s.a.mark((function r(){return s.a.wrap((function(r){for(;;)switch(r.prev=r.next){case 0:return r.next=2,o()({method:"post",url:"/api/login",data:{email:t.email,password:t.password}}).then((function(r){r.data.token&&(t.$store.dispatch("auth/saveToken",{token:r.data.token,remember:t.remember}),t.$store.dispatch("auth/updateUser",{user:e}),t.$store.dispatch("auth/fetchUser"),t.$store.dispatch("teams/fetchTeams"),t.$router.push({name:"teams.join"}))})).catch((function(e){t.showSnackbar(t.$t("auth.error_login"),"error")}));case 2:case"end":return r.stop()}}),r)})))()}}},h=r(8),p=r(9),w=r.n(p),v=r(103),f=r(221),$=r(5),g=r(658),b=r(660),_=r(619),x=r(659),k=r(51),y=Object(h.a)(m,(function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("v-container",{attrs:{fluid:""}},[r("v-card",{staticClass:"mx-auto",attrs:{"max-width":"500",outlined:""}},[r("v-card-title",[e._v(e._s(e.$t("auth.register")))]),e._v(" "),r("v-card-text",[r("v-form",[r("v-text-field",{attrs:{name:"name",label:e.$t("general.name"),"error-messages":e.nameErrors},on:{blur:function(t){return e.$v.name.$touch()}},model:{value:e.name,callback:function(t){e.name=t},expression:"name"}}),e._v(" "),r("v-text-field",{attrs:{name:"email",label:e.$t("general.email"),"error-messages":e.emailErrors},on:{blur:function(t){return e.$v.email.$touch()}},model:{value:e.email,callback:function(t){e.email=t},expression:"email"}}),e._v(" "),r("v-text-field",{attrs:{name:"password",label:e.$t("auth.password"),"error-messages":e.passwordErrors,"append-icon":e.showPwd?"mdi-eye":"mdi-eye-off",type:e.showPwd?"text":"password"},on:{blur:function(t){return e.$v.password.$touch()},"click:append":function(t){e.showPwd=!e.showPwd}},model:{value:e.password,callback:function(t){e.password=t},expression:"password"}}),e._v(" "),r("v-text-field",{attrs:{name:"password2",label:e.$t("auth.confirm_password"),"error-messages":e.passwordErrors2,"append-icon":e.showPwd2?"mdi-eye":"mdi-eye-off",type:e.showPwd2?"text":"password"},on:{blur:function(t){return e.$v.password2.$touch()},input:function(t){return e.$v.password2.$touch()},"click:append":function(t){e.showPwd2=!e.showPwd2}},model:{value:e.password2,callback:function(t){e.password2=t},expression:"password2"}}),e._v(" "),r("v-row",[r("v-col",{staticClass:"text-center",attrs:{cols:"12"}},[r("v-btn",{attrs:{type:"submit",color:"primary",block:""},on:{click:function(t){return t.preventDefault(),e.register(t)}}},[e._v("\n              "+e._s(e.$t("auth.register"))+"\n            ")])],1)],1),e._v(" "),r("v-row",[r("v-col",{staticClass:"text-center",attrs:{cols:"12"}},[r("p",{staticClass:"my-8"},[r("span",{staticClass:"h6"},[e._v(e._s(e.$t("auth.login_with")))])]),e._v(" "),r("login-with-google"),e._v(" "),r("login-with-facebook")],1)],1)],1)],1)],1)],1)}),[],!1,null,null,null);t.default=y.exports;w()(y,{VBtn:v.a,VCard:f.a,VCardText:$.c,VCardTitle:$.d,VCol:g.a,VContainer:b.a,VForm:_.a,VRow:x.a,VTextField:k.a})}}]);