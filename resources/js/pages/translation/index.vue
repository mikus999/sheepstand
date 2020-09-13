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
                  persistent-hint :prefix="langTargetLocale.toUpperCase() + ': '" 
                  @input.native="updateStrings($event)" :success="validation.name.success">
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
  </v-container>
</template>

<script>

export default {
  middleware: 'auth',
  layout: 'vuetify',

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
    }
  },

  created () {
    this.getCategories('en', 'sr')
  },

  methods: {
    getCategories(source, target) {
      this.langSource = this.$i18n.messages[source]
      this.langTarget = this.$i18n.messages[target]

      Object.keys(this.langSource).forEach (key => {
        this.langSourceCat.push({"text": key, "value": key});
      })

      Object.keys(this.langTarget).forEach (key => {
        this.langTargetCat.push({"text": key, "value": key});
      })
    },

    getStrings(key) {
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
    },

    updateStrings: _.debounce(async function(e) {
      this.validation[e.target.name].success = true
      setTimeout(() => this.validation[e.target.name].success = false, 3000)

      await axios({
        method: 'post',      
        url: '/api/translation/update',
        data: {
          section: this.currSection,
          lang: this.langTargetLocale,
          strings: this.langTargetStrings
        }
      })

      this.getTeams()
    }, 1000),

  }


}
</script>


<style scoped>
  .translation-label {
    color: var(--v-primary-base);
  }
</style>