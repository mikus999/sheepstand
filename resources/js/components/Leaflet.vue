<template>
  <div ref="mainDiv">
    <v-card elevation="2">
      <v-toolbar v-if="!readonly" dense dark>
        <v-item-group class="v-btn-toggle">
          <v-btn @click="updateMapType()">
            <v-icon>{{ mapType === 'roadmap' ? icons.mdiSatellite : icons.mdiMap }}</v-icon>
          </v-btn>
        </v-item-group>

        <v-spacer></v-spacer>

        <v-item-group class="v-btn-toggle" right>
          <v-btn @click="saveShapes()" :input-value="isChanged" active-class="save-btn">
            <v-icon>{{ icons.mdiContentSave }}</v-icon>
          </v-btn>
          <v-btn @click="revertChanges()">
            <v-icon>{{ icons.mdiUndo }}</v-icon>
          </v-btn>
          <v-btn @click="$emit('close')">
            <v-icon>{{ icons.mdiClose }}</v-icon>
          </v-btn>
        </v-item-group>
      </v-toolbar>

      <v-toolbar v-else dense dark>
        <v-item-group class="v-btn-toggle">
          <v-btn @click="updateMapType()">
            <v-icon>{{ mapType === 'roadmap' ? icons.mdiSatellite : icons.mdiMap }}</v-icon>
          </v-btn>
        </v-item-group>

        <v-spacer></v-spacer>

        <v-item-group class="v-btn-toggle" right>
          <v-btn @click="$emit('close')">
            <v-icon>{{ icons.mdiClose }}</v-icon>
          </v-btn>
        </v-item-group>
      </v-toolbar>


      <LMap
        ref="map" 
        :center="center" 
        :zoom="zoom"
        :style="'width: ' + width + '; height: ' + height" 
        @ready="onMapReady()">
        
        <LTileLayer :url="url" :attribution="attribution" />

      </LMap>


      <v-toolbar v-if="!readonly" dense dark bottom>
        <v-item-group class="v-btn-toggle">
          <v-btn @click="changeDrawingMode('marker')" :input-value="isActiveButton('marker')">
            <v-icon>{{ icons.mdiMap }}-marker</v-icon>
          </v-btn>
          <v-btn @click="changeDrawingMode('rectangle')" :input-value="isActiveButton('rectangle')">
            <v-icon>{{ icons.mdiVectorRectangle }}</v-icon>
          </v-btn>
          <v-btn @click="changeDrawingMode('polygon')" :input-value="isActiveButton('polygon')">
            <v-icon>{{ icons.mdiVectorPolygon }}</v-icon>
          </v-btn>
          <v-btn @click="changeDrawingMode('circle')" :input-value="isActiveButton('circle')">
            <v-icon>{{ icons.mdiVectorCircle }}</v-icon>
          </v-btn>
        </v-item-group>

        <v-spacer />

        <v-item-group class="v-btn-toggle" right>
          <v-btn @click="deleteSelection()">
            <v-icon>{{ icons.mdiDelete }}</v-icon>
          </v-btn>
        </v-item-group>

      </v-toolbar>
    </v-card>
  </div>
</template>


<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'
import { LMap, LTileLayer } from 'vue2-leaflet'
import drawControl from 'leaflet-draw'
import 'leaflet-fullscreen/dist/leaflet.fullscreen.css'
import 'leaflet-fullscreen/dist/Leaflet.fullscreen'
import cloneDeep from 'lodash/cloneDeep'

delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
  iconRetinaUrl: '/images/leaflet/marker-icon-2x.png',
  iconUrl: '/images/leaflet/marker-icon.png',
  shadowUrl: '/images/leaflet/marker-shadow.png'
});

export default {
  name: 'Leaflet',
  mixins: [helper],
  components: {
    LMap,
    LTileLayer
  },
  props: {
    location: {
      type: Object,
    },
    readonly: {
      type: Boolean,
      default: false
    },
    width: {
      type: [String, Number],
      default: '500'
    },
    height: {
      type: [String, Number],
      default: '500'
    },
    fill: {
      type: String,
      default: '#777777'
    }
  },

  data () {
    return {
      map: null,
      url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
      attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
      edited: null,
      center: L.latLng(1, 1),
      zoom: 5,
      shapeOptions: {
        fillColor: this.getFill,
        fillOpacity: 0.4,
        stroke: true,
        weight: 2,
        color: '#999999',
        editable: true,
        clickable: true,
        bubblingMouseEvents: false
      },
      markerOptions: {
        draggable: true,
        clickable: true,
        bubblingMouseEvents: false
      },
      locatorOptions: {
        enableHighAccuracy: true,
      },
      closeBtn: {
        display: 'fixed',
        top: '50px',
        left: '50px',
        zIndex: '500'
      },
      satelliteBtn: {
        display: 'fixed',
        top: '50px',
        right: '35px',
        zIndex: '501'
      },
      mapDraggable: this.readonly,
      mapCursor: this.readonly ? null : 'default',
      mapType: 'roadmap',
      drawingMode: 0,
      dataLayer: null,
      geoJson: null,
      selectedShape: null,
      isChanged: null,
      drawingManager: null,
      shapes: null,
      drawControl: null,
      drawMode: null,
      drawFeature: null,
    }
  },

  computed: {
    getFill() {
      return this.fill ? this.fill : this.location.color_code
    }
  },

  created () {
  },

  mounted () {
    this.closeBtn.left = (this.$refs.mainDiv.clientWidth - 50) + 'px'
    this.satelliteBtn.top = (this.$refs.mainDiv.clientHeight - 50) + 'px'
  },

  methods: {
    onMapReady() {
      this.map = this.$refs.map.mapObject
      if (this.location.map === null) {
        this.getMyPosition()
      }
      this.loadDrawControl()
    },

    
    getMyPosition() {
      navigator.geolocation.getCurrentPosition(position => {
       this.center = L.latLng(position.coords.latitude, position.coords.longitude)
      }, null, this.locatorOptions)
    },

    recenterMap() {
      this.map.fitBounds(this.shapes.getBounds())
    },


    loadDrawControl() {    
      this.drawControl = new L.Control.Draw({
        draw: false
      })
      this.map.addControl(this.drawControl)

      this.map.addControl(new L.Control.Fullscreen());


      this.shapes = new L.FeatureGroup()
      this.map.addLayer(this.shapes)


      // Map event handlers (mouse)
      this.map.on(L.Draw.Event.CREATED, (e) => {
        var type = e.layerType
        var layer = e.layer
        var feature = layer.feature = layer.feature || {}
        feature.type = "Feature"
        feature.properties = feature.properties || {}
        feature.properties["shape"] = type

        if (type == 'circle') {
          feature.properties["radius"] = layer.getRadius()
        }

        // Shape event handlers
        this.setShapeEvents(layer)

        this.shapes.addLayer(layer)
        this.drawMode = null
      });

      
      this.map.on('click', (e) => {
        this.clearSelection()
      })


      // Map event handlers (keyboard)
      document.addEventListener('keydown', (event) => {
        switch (event.key) {
          case 'Delete': 
          case 'Backspace':
            this.deleteSelection()
            break

          case 'Escape':
            this.clearSelection()
            break
        }
      })


      // Load existing features
      this.loadGeoJSON()
    },


    setShapeEvents(shape) {
      if (!this.readonly) {
        shape.on('click', (e) => {
          L.DomEvent.stopPropagation(e)
          this.setSelection(e.sourceTarget)
        })
      }
    },


    updateMapType () {
      switch (this.mapType) {
        case 'satellite':
          this.url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
          this.attribution = '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
          this.mapType = 'roadmap'
          break
        case 'roadmap':
          this.url = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
	        this.attribution = 'Tiles &copy; Esri'
          this.mapType = 'satellite'
          break
      }
    },

    changeDrawingMode (mode) {
      if (this.drawMode === mode) {
        this.drawMode = null
        this.drawFeature.disable()
        this.drawFeature = null
      } else {
        if (this.drawFeature !== null) {
          this.drawFeature.disable()
          this.drawFeature = null
        }
        switch (mode) {
          case 'polygon':
            this.drawFeature = new L.Draw.Polygon(this.map, this.shapeOptions)
            break
          case 'rectangle':
            this.drawFeature = new L.Draw.Rectangle(this.map, this.shapeOptions)
            break
          case 'circle':
            this.drawFeature = new L.Draw.Circle(this.map, this.shapeOptions)
            break
          case 'marker':
            this.drawFeature = new L.Draw.Marker(this.map, this.markerOptions)
            break
        }

        this.drawMode = mode
        this.drawFeature.enable()
      }

    },

    isActiveButton (mode) {
      var isActive = null
      if (this.drawMode !== null) {
        if (this.drawMode === mode) {
          isActive = true
        }
      }
      return isActive
    },

    revertChanges () {
      this.shapes.clearLayers()

      // Then, load features again
      this.loadGeoJSON()
    },

    loadGeoJSON() {
     
      if (this.location.map !== null) {
        var jsonData = JSON.parse(this.location.map)
        var features = L.geoJson(jsonData, {
            style: this.shapeOptions,
            pointToLayer: (feature, latlng) => {
              if (feature.properties.radius) {
                return new L.Circle(latlng, feature.properties.radius);
              } else {
                return new L.Marker(latlng);
              }
            },
            onEachFeature: this.loadFeature,
        })

        this.recenterMap()
      }

      this.isChanged = null
    },

    loadFeature(feature, layer) {
      if (feature.properties.shape === 'rectangle') {
        layer = new L.Rectangle(layer.getLatLngs())
      }
      this.setShapeEvents(layer)
      this.shapes.addLayer(layer)
    },


    saveShapes () {
      this.saveToDB()
    },

    async saveToDB () {
      var tempData = cloneDeep(this.location)
      var aMethod = 'patch'
      var aUrl = '/api/teams/' + this.team.id + '/locations/' + tempData.id

      var mapStr = null
      if (this.shapes.getLayers().length > 0) {
        mapStr = JSON.stringify(this.shapes.toGeoJSON())
      }

      this.location.map = mapStr
      tempData.map = mapStr

      await axios({
        method: aMethod,      
        url: aUrl,
        data: tempData
      })
      .then(response => {
        this.recenterMap()
        this.showSnackbar(this.$t('teams.success_location_update'), 'success')
        this.isChanged = null
      })

    },

    setSelection (shape) {
      this.clearSelection()

      if (shape.editing.enabled()) {
        this.selectedShape = null
        shape.editing.disable()
      } else {
        this.selectedShape = shape
        shape.editing.enable()
      }
    },

    clearSelection () {
      this.shapes.eachLayer((layer) => {
        layer.editing.disable()
      })
      this.selectedShape = null
      this.drawMode = null
    },

    deleteSelection () {
      if (this.selectedShape !== null) {
        this.shapes.removeLayer(this.selectedShape)
        this.selectedShape = null
      }
    },


  }
}
</script>


<style scoped>
.aa {
  background: #424242 !important;
}

@keyframes blink {
    0% { background-color: rgba(255,0,0,1) }
    50% { background-color: rgba(255,0,0,0.5) }
    100% { background-color: rgba(255,0,0,1) }
}
@-webkit-keyframes blink {
    0% { background-color: rgba(255,0,0,1) }
    50% { background-color: rgba(255,0,0,0.5) }
    100% { background-color: rgba(255,0,0,1) }
}

 .save-btn {
    -moz-transition:all 0.5s ease-in-out;
    -webkit-transition:all 0.5s ease-in-out;
    -o-transition:all 0.5s ease-in-out;
    -ms-transition:all 0.5s ease-in-out;
    transition:all 0.5s ease-in-out;
    -moz-animation:blink normal 1.5s infinite ease-in-out;
    /* Firefox */
    -webkit-animation:blink normal 1.5s infinite ease-in-out;
    /* Webkit */
    -ms-animation:blink normal 1.5s infinite ease-in-out;
    /* IE */
    animation:blink normal 1.5s infinite ease-in-out;
    /* Opera */
}


</style>