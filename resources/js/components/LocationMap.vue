<template>

  <gmap-map 
    ref="map" 
    :center="mapCenter" 
    :zoom="13" 
    style="width: 100%; height: 800px" 
    :options="{
      zoomControl: true,
      mapTypeControl: true,
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
          :rectangle-options="rectangleOptions"
          :circle-options="circleOptions"
          :polygon-options="polygonOptions"
          :shapes="shapes"
          @rightclick="handleClickForDelete()"
        >
          <template v-slot="on">
            <v-toolbar dense>
              <v-btn-toggle>
                <v-btn @click="on.setDrawingMode('rectangle')">
                  <v-icon>mdi-vector-rectangle</v-icon>
                </v-btn>
                <v-btn @click="on.setDrawingMode('circle')">
                  <v-icon>mdi-vector-circle</v-icon>
                </v-btn>
                <v-btn @click="on.setDrawingMode('polygon')">
                  <v-icon>mdi-vector-polygon</v-icon>
                </v-btn>
              </v-btn-toggle>

              <v-btn icon @click="on.deleteSelection()">
                <v-icon>mdi-delete</v-icon>
              </v-btn>

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
    'readonly': false,
    'width': '100%',
    'height': '500px',
  },

  data () {
    return {
      edited: null,
      mapCenter: {
        lat: 1,
        lng: 1
      },
      paths: [
        [ {lat: 1.380, lng: 103.800}, {lat:1.380, lng: 103.810}, {lat: 1.390, lng: 103.810}, {lat: 1.390, lng: 103.800} ]
      ],
      shapes: [],
      rectangleOptions: {
        fillColor: '#777',
        fillOpacity: 0.4,
        strokeWeight: 2,
        strokeColor: '#999',
        draggable: true,
        editable: true,
        clickable: true
      },
      circleOptions: {
        fillColor: '#777',
        fillOpacity: 0.4,
        strokeWeight: 2,
        strokeColor: '#999',
        draggable: true,
        editable: false,
        clickable: true
      },
      polygonOptions: {
        fillColor: '#777',
        fillOpacity: 0.4,
        strokeWeight: 2,
        strokeColor: '#999',
        draggable: true,
        editable: true,
        clickable: true
      },
      locatorOptions: {
        enableHighAccuracy: true,
      },
      mapDraggable: this.readonly,
      mapCursor: this.readonly ? null : 'default',
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
  }

}
</script>