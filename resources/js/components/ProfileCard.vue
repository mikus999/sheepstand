<template>
  <v-card width="300">
    <v-card-title class="justify-center my-4" v-if="user">
      <v-avatar size="70" color="white">
        <v-img :src="user.photo_url" v-if="user.photo_url" />
        <v-icon v-else>{{ icons.mdiAccount }}</v-icon>
      </v-avatar>
    </v-card-title>

    <v-card-subtitle class="text-center mb-2" v-if="user">
      <h1 class="text-h6">{{ user.name }}</h1>
      <h1 class="text-subtitle-2">{{ user.email }}</h1>

      <v-btn rounded outlined small :to="{ name: 'account.index' }" active-class="" class="mt-4">
        {{ $t('general.manage_account')}}
      </v-btn>
    </v-card-subtitle>

    <v-card-text>
      <v-divider />

      <v-list dense>

        <v-list-item class="text-decoration-none" v-if="user && hasTeam">
          <v-list-item-icon>
            <v-icon>{{ icons.mdiAccountGroup }}</v-icon>
          </v-list-item-icon>
          <v-list-item-content>
            <TeamSelector v-if="user && hasTeam" />
          </v-list-item-content>
        </v-list-item>
        <v-list-item class="text-decoration-none">
          <v-list-item-icon>
            <v-icon>{{ icons.mdiTranslate }}</v-icon>
          </v-list-item-icon>
          <v-list-item-content>
            <LocaleSelector />
          </v-list-item-content>
        </v-list-item>
      </v-list>

      <v-divider />


      <v-btn dark block @click.prevent="logout" class="my-4" v-if="user">
        <v-icon>{{ icons.mdiLogout}}</v-icon>
        <span>{{ $t('auth.logout')}}</span>
      </v-btn>

      <v-btn dark block :to="{ name: 'login' }" class="my-4" v-else>
        <v-icon>{{ icons.mdiLogin}}</v-icon>
        <span>{{ $t('auth.login')}}</span>
      </v-btn>

      <v-divider />

      <div class="my-4 text-center">
        <router-link :to="{ name: 'privacy' }" class="text-caption text-decoration-none">{{ $t('general.privacy_policy')}}</router-link>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
import helper from '../mixins/helper'
import TeamSelector from './TeamSelector'
import LocaleSelector from './LocaleSelector'

export default {
  mixins: [helper],
  components: {
    LocaleSelector,
    TeamSelector
  }

}
</script>