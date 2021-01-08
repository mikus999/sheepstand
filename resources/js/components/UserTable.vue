<template>
  <v-card width="100%">
    <v-card-title v-if="!teamUsers">
      <v-icon left>{{ icons.mdiSecurity }}</v-icon>
      Site Security
    </v-card-title>

    <v-card-title v-else-if="$vuetify.breakpoint.xs">
      <v-icon left>{{ icons.mdiAccountMultiple }}</v-icon>
      {{ $t('teams.members')}}
    </v-card-title>

    <v-data-table 
      :headers="teamUsers ? userHeadersTeam : userHeadersAdmin" 
      :items="userData"
      :search="userSearch"
      sort-by="team_role"
      sort-desc
      >
      <template v-slot:top>
        <v-toolbar flat>
          <v-text-field
            v-model="userSearch"
            :label="$t('general.search')"
            :prepend-inner-icon="icons.mdiMagnify"
            single-line
            hide-details
          ></v-text-field>
          <v-spacer v-if="teamUsers" />
          <v-btn 
            color="secondary" 
            class="mb-2" 
            @click="dialog = true"
            v-if="teamUsers"
            >
            <v-icon small :left="$vuetify.breakpoint.smAndUp">{{ icons.mdiAccountPlus }}</v-icon>
            <span v-if="$vuetify.breakpoint.smAndUp">{{ $t('teams.add_user') }}</span>
          </v-btn>

          <v-dialog v-model="dialog" max-width="500px" v-if="teamUsers">
            <v-card>
              <v-card-title>
                <span class="headline">{{ $t('teams.add_user_to_team') }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12">
                      <v-text-field v-model="newUserCode" :label="$t('account.user_code')"></v-text-field>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn text @click="close">{{ $t('general.cancel') }}</v-btn>
                <v-btn color="primary" @click="addUser">{{ $t('general.save') }}</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>

      <template v-slot:item.marriage_mate="{ item }">
        {{ item.marriage_mate ? item.marriage_mate.name : '' }}
      </template>

      <template v-slot:item.fts_status="{ item }">
        {{ getFTSStatus(item.fts_status).text }}
      </template>

      <template v-slot:item.driver="{ item }">
        <v-icon v-if="item.driver">{{ icons.mdiCar }}</v-icon>
      </template>      

      <template v-slot:item.team_role="{ item }">
        <span v-if="team && (team.user_id != item.id)">
          {{ item.team_role ? $t('roles.' + item.team_role) : $t('roles.not_assigned') }}
        </span>

        <a @click="showChangeOwnerOverlay(item)" class="text-no-decoration" v-else>
          <v-icon small>{{ icons.mdiShieldAccount }}</v-icon>
          {{ $t('teams.owner') }}
        </a>
      </template>

      <template v-slot:item.site_roles="{ item }">
        <div v-for="role in item.site_roles">{{ role ? $t('roles.' + role) : $t('roles.not_assigned') }}</div>
      </template>


      <template v-slot:item.actions="{ item }">   
        <v-btn icon @click="showFTSOverlay(item)">
          <v-icon>{{ icons.mdiAccountCog }}</v-icon>
        </v-btn>

        <v-btn icon @click="showRolesOverlay(item)" v-if="!teamUsers">
          <v-icon>{{ icons.mdiShieldEdit }}</v-icon>
        </v-btn>

        <v-btn icon @click="removeUser(item)" v-if="teamUsers && team && (team.user_id != item.id)">
          <v-icon>{{ icons.mdiAccountMinus }}</v-icon>
        </v-btn>
      </template>

    </v-data-table>


    <v-overlay :value="changeOwnerOverlay" :dark="theme=='dark'">
      <ChangeOwner :data="userData" :owner="currUser" width="300px" height="100%" @close="changeOwnerOverlay = false; getUserData()"></ChangeOwner>
    </v-overlay>

    <v-overlay :value="changeFTSOverlay" :dark="theme=='dark'">
      <UserProfile :data="currUser" :admin-roles="!teamUsers" width="300px" height="100%" @close="changeFTSOverlay = false; getUserData()"></UserProfile>
    </v-overlay>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ChangeOwner from '~/components/ChangeOwner.vue'
import UserProfile from '~/components/UserProfile.vue'

export default {
  name: 'UserTable',
  mixins: [helper],
  components: { 
    ChangeOwner,
    UserProfile
  },
  
  props: {
    teamUsers: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      dialog: false,
      userData: [],
      newUserCode: '',
      rolesOverlay: false,
      changeOwnerOverlay: false,
      changeFTSOverlay: false,
      currUser: null,
      userSearch: '',
      userHeadersTeam: [
        {
          text: this.$t('general.name'),
          align: 'start',
          value: 'name'
        },
        {
          text: this.$t('general.email'),
          value: 'email'
        },
        {
          text: this.$t('account.marriage_mate'),
          value: 'marriage_mate'
        },
        {
          text: this.$t('account.fts_status'),
          value: 'fts_status'
        },
        {
          text: this.$t('account.has_auto'),
          value: 'driver',
          align: 'center',
          sortable: false
        },
        {
          text: this.$t('account.user_role'),
          value: 'team_role'
        },
        {
          text: this.$t('general.actions'),
          value: 'actions',
          sortable: false
        },
      ],
      userHeadersAdmin: [
        {
          text: this.$t('general.name'),
          align: 'start',
          value: 'name'
        },
        {
          text: this.$t('general.email'),
          value: 'email'
        },
        {
          text: this.$t('account.marriage_mate'),
          value: 'marriage_mate'
        },
        {
          text: this.$t('account.site_roles'),
          value: 'site_roles'
        },
        {
          text: this.$t('general.actions'),
          value: 'actions',
          sortable: false
        },
      ],
    }
  },

  created() {
    this.getUserData()
  },

  methods: {
    
    async getUserData() {
      var url = null
      if (this.teamUsers) {
        url = '/api/teams/' + this.team.id + '/users/'
      } else {
        url = '/api/users'
      }

      await axios.get(url)
        .then(response => {
          this.userData = response.data.data.users
        })
    },


    async removeUser(user) {
      if (await this.$root.$confirm(this.$t('teams.confirm_remove_user'), null, 'error')) {
        await axios({
            method: 'post',
            url: '/api/teams/leaveteam',
            data: {
              team_id: this.team.id,
              user_id: user.id
            }
          })
          .then(response => {
            this.getUserData()
            this.showSnackbar(this.$t('teams.success_remove_user'), 'success')
          })
      }
    },


    close() {
      this.dialog = false
    },

    showRolesOverlay(user) {
      this.currUser = user
      this.rolesOverlay = true
    },

    showChangeOwnerOverlay(user) {
      this.currUser = user
      this.changeOwnerOverlay = true
    },

    showFTSOverlay(user) {
      this.currUser = user
      this.changeFTSOverlay = true
    },

    async addUser() {
      await axios({
          method: 'post',
          url: '/api/teams/jointeam',
          data: {
            team_id: this.team.id,
            user_code: this.newUserCode
          }
        })
        .then(response => {
            this.getUserData()
            this.showSnackbar(this.$t('teams.success_add_user'), 'success')
        })
        .catch(error => {
          this.showSnackbar(this.$t('general.error_alert_text'), 'error')
        })

      this.newUserCode = ''
      this.close()
    },

  },
}
</script>