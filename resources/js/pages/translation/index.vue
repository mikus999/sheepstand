<template>
  <v-container fluid>
    <v-card width="100%">
      <v-card-title>
      <div>
        <v-icon left>{{ icons.mdiGoogleTranslate }}</v-icon>
        {{ $t('translation.management_system') }}
      </div>
      <div class="ml-auto">
        <v-switch v-model="saveFab.autoSave" :label="$t('general.autosave')" @change="toggleAutoSave"></v-switch>
      </div>
      </v-card-title>

      <!-- LANGUAGE SELECTOR -->
      <v-row class="mx-2">
        <v-col cols=12 sm=6>
          <span>{{ $t('translation.source_language')}}</span>
          <div class="mt-6 font-weight-bold">English</div>
        </v-col>

        <v-col cols=12 sm=6>
          <span>{{ $t('translation.target_language')}}</span>
          <v-radio-group v-model="langTargetLocale" @change="getMessages" row>
            <v-radio v-for="lang in languages" :key="lang.id" :label="lang.name" :value="lang.code" class="font-weight-bold"></v-radio>
          </v-radio-group>
        </v-col>
      </v-row>


      <!-- CATEGORIES IN ENGLISH FILE -->
      <v-row class="mx-2">
        <v-col cols=12>
          <div v-if="langTargetLocale !== null" >
            <v-chip  
              v-for="category in langSourceCat" 
              :key="category.value"
              @click="parseStrings(category.value)"
              class="ma-2 font-weight-bold"
              :color="getCategoryColor(category.text) "
            >
              {{ category.text + ' ' + getFinishedPct(category.text) }}%
            </v-chip>
          </div>
          <!--
          <v-select v-if="langTargetLocale !== null" v-model="currSection" :items="langSourceCat" @change="parseStrings($event)">
          </v-select>
          -->
        </v-col>
      </v-row>
      

      <!-- STRINGS FOR SELECTED CATEGORY -->
      <v-row class="mx-2">
        <v-col cols=12>
          <v-data-iterator v-if="currSection !== null" :items="langSourceStrings" hide-default-footer disable-pagination>
            <template v-slot:default="props">
              <v-row v-for="item in props.items" :key="item.key" class="mt-3">
                <v-col cols=12>
                  <v-textarea
                    v-model="langTargetStrings[props.items.indexOf(item)].value" 
                    placeholder="---" 
                    persistent-hint 
                    :hint="'EN: ' + item.value" 
                    :prefix="langTargetLocale.toUpperCase() + ': '" 
                    class="ma-0 pa-0"
                    rows=1
                    auto-grow>
                  </v-textarea>
                </v-col>
              </v-row>
            </template>
          </v-data-iterator>
        </v-col>   

      </v-row>
    </v-card>
    
    <v-fab-transition>
      <v-btn :key="saveFab.icon" :color="saveFab.color" @click="saveNow" fab dark bottom right fixed>
        <span v-show="saveFab.showTime">{{ saveFab.interval1_rem }}</span>
        <v-icon>{{ saveFab.icon }}</v-icon>
      </v-btn>
    </v-fab-transition>

  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

  components: {
  },

  data () {
    return {
      languages: [],
      langSource: [],
      langTarget: [],
      langSourceCat: [],
      langTargetCat: [],
      langSourceStrings: [],
      langTargetStrings: [],
      langSourceLocale: 'en',
      langTargetLocale: null,
      currSection: null,
      validation: {
        name: {
          success: false, 
          message: null
        }
      },
      saveFab: {
        icon: null,
        color: 'primary',
        showTime: true,
        autoSave: true,
        interval_ms: 30000,
        interval_ends: null,
        interval1_rem: null,
        interval1: null,
        interval2: null,
      }
    }
  },

  created () {
    this.saveFab.icon = this.icons.mdiContentSave
    this.getLanguages()
    this.getStrings(this.langSourceLocale, true)
  },

  methods: {
    async getLanguages() {
      await axios({
        method: 'get',      
        url: '/api/translation/permissions',
      })
      .then(response => {
        this.languages = response.data.data.languages
        this.languages = this.languages.filter(lang => lang.code != 'en')
      })
    },


    getMessages() {
      this.langTarget = []
      this.langTargetStrings = []
      this.currSection = null

      this.getStrings(this.langTargetLocale, false)
    },

    getFinishedPct(key) {
      const sourceCount = Object.keys(this.langSource[key]).length
      const targetCount = this.langTarget[key] == undefined ? 0 : Object.keys(this.langTarget[key]).length
      var pctFinished = parseInt((targetCount / sourceCount) * 100)

      if (pctFinished > 100) {
        pctFinished = 100
      }

      return pctFinished
    },

    getCategoryColor(key) {
      const pctFinished = this.getFinishedPct(key)
      var color = ''

      if (pctFinished == 0) {
        color = 'red'
      } else if (pctFinished < 100) {
        color = 'yellow'
      } else {
        color = 'green'
      }

      return color
    },

    async getStrings(lang, isSource) {
      await axios({
        method: 'get',      
        url: '/api/translation/strings/' + lang,
      })
      .then(response => {
        if (isSource) {
          this.langSource = response.data
        } else {
          this.langTarget = response.data
        }
        this.getCategories()
      })
    },

    getCategories() {
      this.langSourceCat = []
      this.langTargetCat = []

      Object.keys(this.langSource).forEach (key => {
        this.langSourceCat.push({"text": key, "value": key});
      })

      Object.keys(this.langTarget).forEach (key => {
        this.langTargetCat.push({"text": key, "value": key});
      })
    },

    parseStrings(key) {
      // First save the current section
      if (this.currSection !== null) {
        this.updateStrings()
      }

      this.currSection = key
      const tempSourceStrings = this.langSource[key]
      this.langSourceStrings = []

      if (this.langTarget[key] === undefined) {
        this.$set(this.langTarget,  key, {})
      }

      const tempTargetStrings = this.langTarget[key]
      this.langTargetStrings = []

      Object.keys(tempSourceStrings).forEach (key => {
        this.langSourceStrings.push({"key": key, "value": tempSourceStrings[key]});
        this.langTargetStrings.push({"key": key, "value": tempTargetStrings[key] === undefined ? '' : tempTargetStrings[key] });
      })

      if (this.saveFab.autoSave) {
        this.setSaveInterval()
      }
      
    },

    setSaveInterval() {
      if (this.saveFab.interval1 !== null) {
        window.clearInterval(this.saveFab.interval1)
      }

      this.startTimer()
      this.saveFab.interval1 = window.setInterval(() => {
        this.startTimer()   
        this.updateStrings()
      }, this.saveFab.interval_ms)

    },

    startTimer() {
      this.saveFab.interval_ends = this.$dayjs().add(this.saveFab.interval_ms, 'ms');

      window.clearInterval(this.saveFab.interval2)

      this.saveFab.interval2 = window.setInterval(() => {
        this.saveFab.interval1_rem = Math.round(this.$dayjs().diff(this.saveFab.interval_ends) * -1 / 1000)
      }, 1000)

    },

    stopTimer() {
      window.clearInterval(this.saveFab.interval1)
      window.clearInterval(this.saveFab.interval2)

      this.saveFab.interval_ends = null
      this.saveFab.interval1_rem = null
      this.saveFab.interval1 = null
      this.saveFab.interval2 = null

      this.toggleFab(true, false)
    },

    saveNow() {
      if (this.currSection !== null) {
        this.stopTimer()
        this.updateStrings()

        if (this.saveFab.autoSave) {
          this.setSaveInterval()
        }
      }
    },


    async updateStrings() {
      const section = this.currSection
      const locale = this.langTargetLocale
      const strings = this.langTargetStrings

      this.toggleFab(false, false)
      
      setTimeout(() => {
        this.toggleFab(true, this.saveFab.autoSave)
      }, 2000)

      await axios({
        method: 'post',      
        url: '/api/translation/update',
        data: {
          section: section,
          lang: locale,
          strings: JSON.stringify(strings)
        }
      })
      .then(response => {
        this.langTarget = response.data
      })

    },
    
    toggleAutoSave(on) {
      if (on) {
        if (this.currSection !== null) {
          this.setSaveInterval()
          this.toggleFab(true, true)
        }
      } else {
        this.stopTimer()
      }
    },

    toggleFab(isSave, withTimer) {
      this.saveFab.color = isSave ? 'primary' : 'green'
      this.saveFab.icon = isSave ? this.icons.mdiContentSave : this.icons.mdiCheckBold
      this.saveFab.showTime = withTimer
    }
  },

  beforeRouteLeave (to, from, next) {
    this.stopTimer()
    this.updateStrings()
    next()
  }
}
</script>


<style scoped>
  .v-messages__message {
    color: var(--v-primary-base) !important;
  }
</style>