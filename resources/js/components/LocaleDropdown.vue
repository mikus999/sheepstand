<template>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <fa icon="globe" class="navbar-icon" fixed-width />
      <span class="d-lg-none ml-2">{{ $t('language') }}</span>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
      <a v-for="(value, key) in locales" :key="key" class="dropdown-item" href="#" @click.prevent="setLocale(key)">
        {{ value }}
      </a>
    </div>
  </li>
</template>

<script>
import { mapGetters } from 'vuex'
import { loadMessages } from '~/plugins/i18n'

export default {
  computed: mapGetters({
    locale: 'lang/locale',
    locales: 'lang/locales'
  }),

  methods: {
    setLocale (locale) {
      if (this.$i18n.locale !== locale) {
        //loadMessages(locale)
        this.$i18n.locale = locale
        this.$store.dispatch('lang/setLocale', { locale })
      }
    }
  }

}
</script>

<style scoped>
.navbar-icon {
  width: 1.3rem;
  height: 1.3rem;
}
</style>
