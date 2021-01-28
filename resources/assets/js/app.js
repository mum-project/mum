/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue'
import VueSelect from 'vue-select';

import Fuse from 'fuse.js';

import moment from 'moment';

import VTooltip from 'v-tooltip';
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.use(VTooltip);
Vue.component('v-select', VueSelect);

Vue.component('mu-alert', require('./components/Alert.vue').default);
Vue.component('custom-password', require('./components/PasswordInput.vue').default);
Vue.component('ajax-select', require('./components/AjaxSelect.vue').default);
Vue.component(
  'alias-deactivate-at-input',
  require('./components/AliasDeactivateAtInput.vue').default
);
Vue.component('popup-modal', require('./components/PopupModal.vue').default);
Vue.component(
  'integrations-form',
  require('./components/integrations/IntegrationsForm.vue').default
);
Vue.component(
  'edit-integration-parameters',
  require('./components/integrations/IntegrationParametersForm.vue').default
);
Vue.component(
  'size-measurements-chart',
  require('./components/SizeMeasurementsChart.vue').default
);
Vue.component(
  'input-with-random-generator',
  require('./components/InputWithRandomGenerator.vue').default
);
Vue.component(
  'alias-senders-recipients-form',
  require('./components/aliasSendersRecipients/AliasSendersRecipientsForm.vue').default
);
Vue.component(
  'modal-content-provider',
  require('./components/ModalContentProvider.vue').default
);
Vue.component('index-search', require('./components/IndexSearch.vue').default);

const app = new Vue({
  el: '#root',
  data: {
    showPopupModal: false,
    modalContentIdentifier: null,
    modalContentPayload: null,
  },
  methods: {
    setModalContentIdentifier(identifier) {
      this.modalContentIdentifier = identifier;
      this.showPopupModal = identifier != null;
    },
    setModalContentPayload(payload) {
      this.modalContentPayload = payload;
    },
  },
});
