<template>
  <v-container fluid>
    <v-row>
      <h1 class="display-1">
        {{ $t('translation.management_system') }}
      </h1>
    </v-row>

    <v-row>
      <v-col cols=12>
        <v-select :items="langSourceCat" @change="getStrings($event)">
        </v-select>
      </v-col>
    </v-row>
    
    <v-row>
      <v-col cols=12>
        <v-data-iterator :items="langSourceStrings" hide-default-footer disable-pagination>
          <template v-slot:default="props">
            <v-row v-for="item in props.items" :key="item.key" class="mt-3">
              <v-col cols=12>
                <v-text-field :hint="item.key" v-model="langTargetStrings[props.items.indexOf(item)].value" placeholder="---" 
                  persistent-hint :prefix="langTargetLocale.toUpperCase() + ': '">
                  <template v-slot:label>
                    <span class="translation-label">EN: {{ item.value}}</span>
                  </template>
                </v-text-field>
              </v-col>
            </v-row>
          </template>
        </v-data-iterator>
      </v-col>   

    </v-row>

    <v-fab-transition>
      <v-btn :key="saveFab.icon" :color="saveFab.color" @click="saveNow" fab dark bottom right fixed>
        <span v-show="saveFab.showTime">{{ remaining }}</span>
        <v-icon small>{{ saveFab.icon }}</v-icon>
      </v-btn>
    </v-fab-transition>

  </v-container>
</template>

<script>
import axios from 'axios'
import moment from 'moment'
import helper from '../../mixins/helper'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

  components: {
  },

  data () {
    return {
      langSource: {},
      langTarget: {},
      langSourceCat: [],
      langTargetCat: [],
      langSourceStrings: [],
      langTargetStrings: [],
      langSourceLocale: 'en',
      langTargetLocale: 'sr',
      currSection: '',
      validation: {
        name: {
          success: false, 
          message: null
        }
      },
      saveFab: {
        icon: 'mdi-content-save',
        color: 'primary',
        showTime: true,
        interval_ms: 30000,
        interval_ends: null,
        interval1_rem: null,
        interval1: null,
        interval2: null,
      }
    }
  },

  computed: {
    remaining: function () {
      return this.saveFab.interval1_rem
    }
  },

  created () {
    this.getMessages('en', 'sr')
    this.getCategories()
  },

  methods: {
    getMessages(source, target) {
      this.langSource = this.$i18n.messages[source]
      this.langTarget = this.$i18n.messages[target]
    },

    getCategories() {
      Object.keys(this.langSource).forEach (key => {
        this.langSourceCat.push({"text": key, "value": key});
      })

      Object.keys(this.langTarget).forEach (key => {
        this.langTargetCat.push({"text": key, "value": key});
      })
    },

    getStrings(key) {
      if (this.currSection !== '') {
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

      this.setSaveInterval()
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
      this.saveFab.interval_ends = moment().add(this.saveFab.interval_ms, 'ms');

      window.clearInterval(this.saveFab.interval2)

      this.saveFab.interval2 = window.setInterval(() => {
        this.saveFab.interval1_rem = Math.round(moment().diff(this.saveFab.interval_ends) * -1 / 1000)
      }, 1000)

    },

    saveNow() {
      this.startTimer()
      this.updateStrings()
    },

    async updateStrings() {
      const section = this.currSection
      const locale = this.langTargetLocale
      const strings = this.langTargetStrings

      this.saveFab.color = 'green'
      this.saveFab.icon = 'mdi-check-bold'
      this.saveFab.showTime = false
      setTimeout(() => {
        this.saveFab.color = 'primary'
        this.saveFab.icon = 'mdi-content-save'
        this.saveFab.showTime = true
      }, 2000)

      console.log('update')
      await axios({
        method: 'post',      
        url: '/api/translation/update',
        data: {
          section: section,
          lang: locale,
          strings: JSON.stringify(strings)
        }
      })

    },

  }


}
</script>


<style scoped>
  .translation-label {
    color: var(--v-primary-base);
  }
</style>