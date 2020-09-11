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
            <v-row v-for="item in props.items" :key="item.key">
              <v-col cols=6>
                <v-text-field :label="item.key" :value="item.value" readonly></v-text-field>
              </v-col>
              <v-col cols=6>
                <v-text-field :label="item.key" :value="langTargetStrings[props.items.indexOf(item)].value" placeholder="---"></v-text-field>
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
      const tempSourceStrings = this.langSource[key]
      this.langSourceStrings = []

      if (this.langTarget[key] === undefined) {
        this.$set(this.langTarget,  key, {})
      }

      const tempTargetStrings = this.langTarget[key]
      this.langTargetStrings = []

      Object.keys(tempSourceStrings).forEach (key => {
        this.langSourceStrings.push({"key": key, "value": tempSourceStrings[key]});
        this.langTargetStrings.push({"key": key, "value": tempTargetStrings[key] === null ? '' : tempTargetStrings[key] });
      })

    }

  }


}
</script>
