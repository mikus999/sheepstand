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
      <!--
      <template #visible>
        <gmap-drawing-manager
          v-if="!readonly"
          ref="dm"
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
      -->
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
      polygons: [],
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

  mounted () {
    this.loadMapDrawingManager()
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
        console.log(geo)

      })

    },

    savePolygon(paths) {
      this.polygons.push(paths);
      console.log(this.polygons)
    },
    
    loadMapDrawingManager() {    
      let self = this;
      this.$refs.map.$mapPromise.then((mapObject) => {
        const drawingManager = new google.maps.drawing.DrawingManager({
          drawingControl: true,
          drawingControlOptions: {
              position: google.maps.ControlPosition.RIGHT_CENTER,
              drawingModes: [
                google.maps.drawing.OverlayType.MARKER,
                google.maps.drawing.OverlayType.RECTANGLE,
                google.maps.drawing.OverlayType.CIRCLE,
                google.maps.drawing.OverlayType.POLYGON,
              ]
          }
        });
        
        drawingManager.setMap(this.$refs.map.$mapObject); 

        google.maps.event.addListener(drawingManager, 'overlaycomplete', (event) => {
          if (event.type == 'rectangle') {
            var paths = event.overlay.getBounds();
          } else if (event.type == 'polygon') {
            var paths = event.overlay.getPaths().getArray();
          } else if (event.type == 'marker') {
            var paths = event.overlay.getPosition();
          } else if (event.type == 'circle') {
            var paths = event.overlay.getRadius();
          }
          console.log(event.overlay)
          // Get overlay paths
          // Remove overlay from map
          //event.overlay.setMap(null);

          // Disable drawingManager
          drawingManager.setDrawingMode(null);

          // Create Polygon
          //self.savePolygon(paths);
        });
      });
    },
  }
}
</script>

<style scoped>
.aa {
  background: #424242 !important;
}

</style>