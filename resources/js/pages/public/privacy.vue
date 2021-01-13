<template>
  <v-card flat class="pa-4 pa-sm-8">
    <v-card-title class="justify-center mt-6">
      <h1 :class="$vuetify.breakpoint.xs ? 'text-h4' : 'text-h3'">{{ $t('general.privacy_policy') }}</h1>
    </v-card-title>
    
    <v-card-text class="text-justify">
      <div class="mt-8">
        This privacy policy will explain how SheepStand uses the personal data we collect from you when you use our website.
      </div>

      <h1 class="text-h4 mt-8 mb-4">Topics:</h1>
      <ul>
        <li>What data do we collect?</li>
        <li>How do we collect and use your data?</li>
        <li>What are your data protection rights?</li>
        <li>What are cookies?</li>
        <li>How do we use cookies?</li>
        <li>Changes to our privacy policy</li>
        <li>How to contact us</li>
        <li>How to contact the appropriate authorities</li>
      </ul>

      <h1 class="text-h5 mt-8 mb-4">What data do we collect?</h1>
      <div>
        <p>SheepStand collects the following data:</p>
        <ul>
          <li>Name</li>
          <li>Email address</li>
        </ul>
      </div>

      <h1 class="text-h5 mt-8 mb-4">How do we collect and use your data?</h1>
      <div>
        <p>
          You directly provide SheepStand with the data we collect. We collect and process data when you register online.
          SheepStand collects your data so that we can authenticate you.
          SheepStand will keep your data indefinitely, our until you delete your account. 
          If you account is inactive for more than one year, we may delete your data.
        </p>
      </div>

      <h1 class="text-h5 mt-8 mb-4">What are your data protection rights?</h1>
      <div>
        <p>SheepStand would like to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:</p>
        <ul>
          <li>The right to access – You have the right to request SheepStand for copies of your personal data. We may charge you a small fee for this service.</li>
          <li>The right to rectification – You have the right to request that SheepStand correct any information you believe is inaccurate. You also have the right to request SheepStand to complete the information you believe is incomplete.</li>
          <li>The right to erasure – You have the right to request that SheepStand erase your personal data, under certain conditions.</li>
          <li>The right to restrict processing – You have the right to request that SheepStand restrict the processing of your personal data, under certain conditions.</li>
          <li>The right to object to processing – You have the right to object to Our Company’s processing of your personal data, under certain conditions.</li>
          <li>The right to data portability – You have the right to request that SheepStand transfer the data that we have collected to another organization, or directly to you, under certain conditions.</li>
        </ul>
        <p>If you make a request, we have one month to respond to you. If you would like to exercise any of these rights, please contact us at our email: <a href="mailto:admin@sheepstand.com">admin@sheepstand.com</a></p>
      </div>

      <h1 class="text-h5 mt-8 mb-4">What are cookies?</h1>
      <div>
        <p>
          Cookies are text files placed on your computer to collect standard Internet log information and visitor behavior information. 
          When you visit our websites, we may collect information from you automatically through cookies or similar technology. 
          You can set your browser not to accept cookies, and the above website tells you how to remove cookies from your browser. 
          However, in a few cases, some of our website features may not function as a result.
        </p>
        <p>For further information, visit <a href="https://allaboutcookies.org" target="_blank">allaboutcookies.org</a>.</p>
      </div>

      <h1 class="text-h5 mt-8 mb-4">How do we use cookies?</h1>
      <div>
        <p>
          SheepStand uses these cookies so that we recognize you on our website and remember your previously selected preferences. 
          These could include what language you prefer and location you are in.
        </p>
      </div>

      <h1 class="text-h5 mt-8 mb-4">Changes to our privacy policy</h1>
      <div>
        <p>SheepStand keeps its privacy policy under regular review and places any updates on this web page. This privacy policy was last updated on 12 January 2021.</p>
      </div>

      <h1 class="text-h5 mt-8 mb-4">How to contact us</h1>
      <div>
        <p>If you have any questions about our privacy policy, the data we hold on you, or you would like to exercise one of your data protection rights, please do not hesitate to contact us.</p>
        <p>Email us at: <a href="mailto:admin@sheepstand.com">admin@sheepstand.com</a></p>
      </div>

      <h1 class="text-h5 mt-8 mb-4">How to contact the appropriate authority</h1>
      <div>
        <p>Should you wish to report a complaint or if you feel that SheepStand has not addressed your concern in a satisfactory manner, you may contact the Information Commissioner’s Office.</p>
      </div>
      
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  layout: 'default',
  middleware: '',

}
</script>