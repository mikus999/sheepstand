import Vue from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

// import { } from '@fortawesome/free-regular-svg-icons'

import {
  faUser, faUsers, faLock, faSignOutAlt, faCog, faCogs, faSignInAlt, faGlobe,
  faGlobeAmericas, faUserCircle, faBars, faTachometerAlt, faChevronRight, faChevronLeft, 
  faHome, faWindowClose
} from '@fortawesome/free-solid-svg-icons'

import {
  faGithub, faGoogle, faFacebook
} from '@fortawesome/free-brands-svg-icons'

library.add(
  faUser, faUsers, faLock, faSignOutAlt, faCog, faCogs, faSignInAlt, faGlobe,
  faGlobeAmericas, faUserCircle, faBars, faGithub, faGoogle, faFacebook,
  faTachometerAlt, faChevronRight, faHome, faWindowClose, faChevronLeft
)

Vue.component('fa', FontAwesomeIcon)
