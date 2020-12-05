<template>
  <v-card width="100%">

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
            prepend-inner-icon="mdi-magnify"
            single-line
            hide-details
          ></v-text-field>
          <v-spacer />
          <v-btn 
            color="secondary" 
            class="mb-2" 
            @click="dialog = true"
            >
            <v-icon small :left="$vuetify.breakpoint.smAndUp">mdi-account-plus</v-icon>
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
                <v-btn color="blue darken-1" text @click="close">{{ $t('general.cancel') }}</v-btn>
                <v-btn color="blue darken-1" text @click="addUser">{{ $t('general.save') }}</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>

      <template v-slot:item.team_role="{ item }">
        <a @click="showRolesOverlay(item)" class="text-no-decoration" v-if="team && (team.user_id != item.id)">
          <v-icon small>mdi-shield-account</v-icon>
          {{ item.team_role ? $t('roles.' + item.team_role) : $t('roles.not_assigned') }}
        </a>

        <a @click="showChangeOwnerOverlay(item)" class="text-no-decoration" v-else>
          <v-icon small>mdi-shield-account</v-icon>
          {{ $t('teams.owner') }}
        </a>
      </template>

      <template v-slot:item.site_roles="{ item }">
        <div v-for="role in item.site_roles">{{ role ? $t('roles.' + role) : $t('roles.not_assigned') }}</div>
      </template>


      <template v-slot:item.actions="{ item }">           
        <v-btn icon small @click="showRolesOverlay(item)" v-if="!teamUsers">
          <v-icon small>mdi-shield-edit</v-icon>
        </v-btn>

        <v-btn icon small @click="removeUser(item)" v-if="team && (team.user_id != item.id)">
          <v-icon small>mdi-account-minus</v-icon>
        </v-btn>
      </template>

    </v-data-table>



    <v-overlay :value="rolesOverlay" :dark="theme=='dark'">
      <UserRoles :data="currUser" width="300px" height="100%" :admin-roles="!teamUsers" @close="rolesOverlay = false; getUserData()"></UserRoles>
    </v-overlay>

    <v-overlay :value="changeOwnerOverlay" :dark="theme=='dark'">
      <ChangeOwner :data="userData" :owner="currUser" width="300px" height="100%" @close="changeOwnerOverlay = false; getUserData()"></ChangeOwner>
    </v-overlay>
    
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import UserRoles from '~/components/UserRoles.vue'
import ChangeOwner from '~/components/ChangeOwner.vue'

export default {
  name: 'UserTable',
  mixins: [helper],
  components: { 
    UserRoles,
    ChangeOwner
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
          text: this.$t('account.user_code'),
          value: 'user_code'
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
          text: this.$t('account.user_code'),
          value: 'user_code'
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
        url = '/api/teams/users/' + this.team.id
      } else {
        url = '/api/users'
      }

      await axios.get(url)
        .then(response => {
          this.userData = response.data
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
          if (!response.data.error) {
            this.getUserData()
            this.showSnackbar(this.$t('teams.success_add_user'), 'success')
          } else {
            this.showSnackbar(this.$t(response.data.message, 'error'))
          }
        })

      this.newUserCode = ''
      this.close()
    },

  },
}
</script>