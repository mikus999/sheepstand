<template>

  <gmap-map 
    ref="map" 
    :map-type-id="mapType"
    :center="mapCenter" 
    :zoom="13" 
    style="width: 100%; height: 800px" 
    :options="{
      zoomControl: true,
      mapTypeControl: false,
      scaleControl: false,
      streetViewControl: false,
      rotateControl: false,
      fullscreenControl: false,
      disableDefaultUi: false,
      draggable: readonly,
      draggableCursor: 'default'
    }"
    @rightclick="handleClickForDelete"
    >
      <template #visible>
        <gmap-drawing-manager
          v-if="!readonly"
          position="MIDDLE_RIGHT"
          :rectangle-options="shapeOptions"
          :circle-options="shapeOptions"
          :polygon-options="shapeOptions"
          :shapes="shapes"
          @rightclick="handleClickForDelete()"
        >
          <template v-slot="on">
            <v-toolbar dense dark>
              <v-btn-toggle mandatory>
                <v-btn @click="updateMapType('roadmap')">
                  <v-icon>mdi-map</v-icon>
                </v-btn>
                <v-btn @click="updateMapType('satellite')">
                  <v-icon>mdi-satellite</v-icon>
                </v-btn>
              </v-btn-toggle>

              <v-spacer></v-spacer>

              <v-item-group class="v-btn-toggle" right>
                <v-btn @click="on.setDrawingMode('rectangle')">
                  <v-icon>mdi-vector-rectangle</v-icon>
                </v-btn>
                <v-btn @click="on.setDrawingMode('circle')">
                  <v-icon>mdi-vector-circle</v-icon>
                </v-btn>
                <v-btn @click="on.setDrawingMode('polygon')">
                  <v-icon>mdi-vector-polygon</v-icon>
                </v-btn>
                <v-btn @click="on.deleteSelection()">
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
                <v-btn @click="showJSON()">
                  <v-icon>mdi-marker</v-icon>
                </v-btn>
              </v-item-group>
            </v-toolbar>
          </template>
        </gmap-drawing-manager>
      </template>
  </gmap-map>

</template>

<script>
export default {
  name: 'LocationMap',
  props: {
    readonly: {
      type: Boolean,
      default: false
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    width: {
      type: [String, Number],
      default: '500px'
    },
    fill: {
      type: String,
      default: '#777777'
    }
  },

  data () {
    return {
      edited: null,
      mapCenter: {
        lat: 1,
        lng: 1
      },
      paths: [],
      shapes: [],
      shapeOptions: {
        fillColor: this.fill,
        fillOpacity: 0.4,
        strokeWeight: 2,
        strokeColor: '#999999',
        draggable: true,
        editable: true,
        clickable: true
      },
      locatorOptions: {
        enableHighAccuracy: true,
      },
      mapDraggable: this.readonly,
      mapCursor: this.readonly ? null : 'default',
      mapType: 'roadmap',
      drawingMode: 0,
    }
  },

  computed: {

  },

  created () {
    navigator.geolocation.getCurrentPosition(position => {
       this.mapCenter.lat = position.coords.latitude;
       this.mapCenter.lng = position.coords.longitude;
    }, null, this.locatorOptions)

  },

  methods: {
    handleClickForDelete($event) {
      this.$refs.polygon.$polygonObject.getPaths()
        .getAt($event.path)
        .removeAt($event.vertex)
    },

    updateMapType (type) {
      this.mapType = type
    },

    updateDrawingType (type) {
      console.log(type)
    },

    showJSON () {
      this.$refs.map.$mapObject.data.add(this.shapes)
      this.displayGeoJson()
    },

    displayGeoJson() {
      var geoJson
      var mapData = this.$refs.map.$mapObject.data

      mapData.toGeoJson(geo => {
        geoJson = JSON.stringify(geo, null, 2)
        console.log(geoJson)

      })

      
    }
  }
}
</script>

<style scoped>
.aa {
  background: #424242 !important;
}

</style>