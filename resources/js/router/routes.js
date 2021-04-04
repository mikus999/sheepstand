
function page (path) {
  return () => import(/* webpackChunkName: '' */ `~/pages/${path}`).then(m => m.default || m)
}

export default [
  { path: '/', name: 'default', component: page('index.vue'), meta: { roles: [], auth: false } },
  { path: '/dashboard', name: 'dashboard', component: page('dashboard.vue'), meta: { roles: [], auth: true } },

  // PUBLIC routes
  { path: '/privacy', name: 'privacy', component: page('public/privacy.vue'), meta: { roles: [], auth: false } },

  // AUTH routes
  { path: '/login', name: 'login', component: page('auth/login.vue'), meta: { title: 'Login', roles: [], auth: false } },
  { path: '/register', name: 'register', component: page('auth/register.vue'), meta: { title: 'Register', roles: [], auth: false } },
  { path: '/password/reset', name: 'password.request', component: page('auth/password/email.vue'), meta: { roles: [], auth: false } },
  { path: '/password/reset/:token', name: 'password.reset', component: page('auth/password/reset.vue'), meta: { roles: [], auth: false }, props: true },
  { path: '/email/verify/:id', name: 'verification.verify', component: page('auth/verification/verify.vue'), meta: { roles: [], auth: false }, props: true },
  { path: '/email/resend', name: 'verification.resend', component: page('auth/verification/resend.vue'), meta: { roles: [], auth: false } },

  // ACCOUNT routes
  { path: '/account/inbox', name: 'account.inbox', component: page('account/inbox.vue'), meta: { roles: [], auth: true } },
  { path: '/account/settings', name: 'account.index', component: page('account/index.vue'), meta: { roles: [], auth: true } },
  { path: '/account/notifications', name: 'notifications.join', component: page('account/notifications.vue'), meta: { roles: [], auth: true } },

  // TEAM routes
  { path: '/team/join', name: 'teams.join', component: page('teams/join.vue'), meta: { roles: [], auth: true } },
  { path: '/team/settings/:tab', name: 'teams.index', component: page('teams/index.vue'), meta: { roles: ['elder','team_admin'], auth: true, props: true, params: { tab: 'general' } } },
  { path: '/team/notifications', name: 'notifications.setup', component: page('teams/notifications.vue'), meta: { roles: ['team_admin'], auth: true } },
  { path: '/team/messages', name: 'teams.messages', component: page('teams/messages.vue'), meta: { roles: ['team_admin'], auth: true } },


  // SCHEDULE routes
  { path: '/schedule/edit/:id', name: 'schedules.edit', component: page('schedules/edit.vue'), meta: { roles: ['elder','team_admin'], auth: true }, props: true },
  { path: '/schedule/assignments/:id', name: 'schedules.assignments', component: page('schedules/assignments.vue'), meta: { roles: ['elder','team_admin'], auth: true }, props: true },
  { path: '/schedule/', name: 'schedules.index', component: page('schedules/index.vue'), meta: { roles: ['elder','team_admin'], auth: true } },
  { path: '/shifts', name: 'schedules.shifts', component: page('schedules/shifts.vue'), meta: { roles: [], auth: true } },

  // TRANSLATION routes
  { path: '/translation', name: 'translation.index', component: page('translation/index.vue'), meta: { roles: ['translator'], auth: true }},

  // ADMIN routes
  { path: '/admin', name: 'admin.index', component: page('admin/index.vue'), meta: { roles: ['super_admin'], auth: true }},


  // ERROR routes
  { path: '/notfound', name: 'notfound', component: page('errors/404.vue'), meta: { roles: [], auth: false } },
  { path: '/accessdenied', name: 'accessdenied', component: page('errors/403.vue'), meta: { roles: [], auth: false } }

]
