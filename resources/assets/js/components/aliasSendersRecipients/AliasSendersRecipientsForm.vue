<template>
    <div class="relative">
        <div class="flex flex-row items-start justify-between flex-wrap mb-2">
            <h4 class="font-extrabold">Senders and Recipients</h4>
            <div class="tab-bar justify-end">
                <button :class="{'tab-title': true, 'active': currentTab === 'easy'}"
                        @click.prevent="tabSetting = 'easy'"
                        :disabled="needsAdvancedView">
                    Easy
                </button>
                <button :class="{'tab-title': true, 'active': currentTab === 'advanced'}"
                        @click.prevent="tabSetting = 'advanced'">
                    Advanced
                </button>
            </div>
        </div>
        <transition name="fade" mode="out-in">
            <div v-if="currentTab === 'easy'" key="easy">
                <address-pill-view
                        title="Sender and Recipient Mailboxes"
                        :accounts="senderAndRecipientMailboxes"
                        @remove="removeMailboxFromSendersAndRecipients"
                        @create="emitModalContentData(true, true, false)"
                        :validation-error="validationErrorsSendersAndRecipients"
                ></address-pill-view>
            </div>
            <div v-else key="advanced">
                <div>
                    <address-pill-view
                            title="Sender Mailboxes"
                            :accounts="senderMailboxes"
                            @remove="removeMailboxFromSenders"
                            @create="emitModalContentData(true, false, false)"
                            :validation-error="validationErrors.senderMailboxes"
                    ></address-pill-view>
                </div>
                <div class="mt-4">
                    <address-pill-view
                            title="Recipient Mailboxes"
                            :accounts="recipientMailboxes"
                            @remove="removeMailboxFromRecipients"
                            @create="emitModalContentData(false, true, false)"
                            :validation-error="validationErrors.recipientMailboxes"
                    ></address-pill-view>
                </div>
                <div class="mt-4">
                    <address-pill-view
                            title="External Recipients"
                            :accounts="externalRecipients"
                            @remove="removeFromExternalRecipients"
                            @create="emitModalContentData(false, false, true)"
                            :are-accounts-mailboxes="false"
                            :validation-error="validationErrors.externalRecipients"
                    ></address-pill-view>
                </div>
            </div>
        </transition>
        <div>
            <input
                    v-for="mailbox in senderMailboxes"
                    type="hidden"
                    :name="'sender_mailboxes[' + senderMailboxes.indexOf(mailbox) + '][id]'"
                    :value="mailbox.id"
            >
            <input
                    v-for="mailbox in senderMailboxes"
                    type="hidden"
                    :name="'sender_mailboxes[' + senderMailboxes.indexOf(mailbox) + '][address]'"
                    :value="mailbox.address"
            >
            <input
                    v-for="mailbox in recipientMailboxes"
                    type="hidden"
                    :name="'recipient_mailboxes[' + recipientMailboxes.indexOf(mailbox) + '][id]'"
                    :value="mailbox.id"
            >
            <input
                    v-for="mailbox in recipientMailboxes"
                    type="hidden"
                    :name="'recipient_mailboxes[' + recipientMailboxes.indexOf(mailbox) + '][address]'"
                    :value="mailbox.address"
            >
            <input
                    v-for="recipient in externalRecipients"
                    type="hidden"
                    :name="'external_recipients[' + externalRecipients.indexOf(recipient) + '][address]'"
                    :value="recipient.address"
            >
        </div>
    </div>
</template>

<script>
import AddressPillView from './AddressPillView';

export default {
  components: {
    AddressPillView,
  },
  props: {
    defaultTab: {
      type: String,
      validator: val => ['easy', 'advanced'].includes(val),
      default: 'easy',
    },
    oldSenderMailboxes: {
      type: Array,
      default: () => [],
    },
    oldRecipientMailboxes: {
      type: Array,
      default: () => [],
    },
    oldExternalRecipients: {
      type: Array,
      default: () => [],
    },
    validationErrorsProp: {
      type: Object,
      default: {
        senderMailboxes: null,
        recipientMailboxes: null,
        externalRecipients: null,
      },
    },
  },
  data() {
    return {
      allMailboxes: [],
      senderMailboxes: [],
      recipientMailboxes: [],
      externalRecipients: [],
      tabSetting: 'easy',
      formModalOptions: {
        show: false,
      },
      validationErrors: {
        senderMailboxes: null,
        recipientMailboxes: null,
        externalRecipients: null,
      },
    };
  },
  computed: {
    senderAndRecipientMailboxes() {
      let senderMailboxes = this.senderMailboxes;
      let filteredMailboxes = this.recipientMailboxes.filter(
        mailbox => !senderMailboxes.some(m => m.id === mailbox.id)
      );
      return _.union(senderMailboxes, filteredMailboxes);
    },
    needsAdvancedView() {
      return (
        this.senderAndRecipientMailboxes.length !==
          this.senderMailboxes.length ||
        this.senderAndRecipientMailboxes.length !==
          this.recipientMailboxes.length ||
        this.externalRecipients.length > 0
      );
    },
    currentTab() {
      if (this.needsAdvancedView) {
        this.tabSetting = 'advanced';
        return 'advanced';
      }
      return this.tabSetting;
    },
    validationErrorsSendersAndRecipients() {
      if (
        this.validationErrors.senderMailboxes &&
        this.validationErrors.recipientMailboxes
      ) {
        return (
          this.validationErrors.senderMailboxes +
          ' ' +
          this.validationErrors.recipientMailboxes
        );
      }
      if (this.validationErrors.senderMailboxes) {
        return this.validationErrors.senderMailboxes;
      }
      return this.validationErrors.recipientMailboxes;
    },
  },
  methods: {
    removeMailboxFromSendersAndRecipients(mailbox) {
      this.senderMailboxes.splice(this.senderMailboxes.indexOf(mailbox), 1);
      this.recipientMailboxes.splice(
        this.recipientMailboxes.indexOf(mailbox),
        1
      );
    },
    removeMailboxFromSenders(mailbox) {
      this.senderMailboxes.splice(this.senderMailboxes.indexOf(mailbox), 1);
      this.validationErrors.senderMailboxes = null;
    },
    removeMailboxFromRecipients(mailbox) {
      this.recipientMailboxes.splice(
        this.recipientMailboxes.indexOf(mailbox),
        1
      );
      this.validationErrors.recipientMailboxes = null;
    },
    removeFromExternalRecipients(recipient) {
      this.externalRecipients.splice(
        this.externalRecipients.indexOf(recipient),
        1
      );
      this.validationErrors.externalRecipients = null;
    },
    addMailbox(options, mailbox) {
      if (
        options.isSender &&
        this.senderMailboxes.filter(mb => mailbox.id === mb.id).length === 0
      ) {
        this.senderMailboxes.push(mailbox);
        this.validationErrors.senderMailboxes = null;
      }
      if (
        options.isRecipient &&
        this.recipientMailboxes.filter(mb => mailbox.id === mb.id).length === 0
      ) {
        this.recipientMailboxes.push(mailbox);
        this.validationErrors.recipientMailboxes = null;
      }
    },
    addExternalRecipient(address) {
      this.externalRecipients.push({ address: address });
      this.validationErrors.externalRecipients = null;
    },
    emitModalContentData(isSender, isRecipient, isExternal) {
      let options = {
        isSender,
        isRecipient,
        isExternal,
      };
      let callback = this.modalCallback;
      this.$emit('set-modal-content-payload', {
        options,
        callback: function(o, d) {
          callback(o, d);
        },
      });
      this.$emit('set-modal-content-identifier', 'alias-mailbox-form');
    },
    modalCallback(options, data) {
      if (options.isExternal) {
        this.addExternalRecipient(data);
        return;
      }
      this.addMailbox(options, data);
    },
  },
  created() {
    this.tabSetting = this.defaultTab;
    this.senderMailboxes = this.oldSenderMailboxes;
    this.recipientMailboxes = this.oldRecipientMailboxes;
    this.externalRecipients = this.oldExternalRecipients;
    this.validationErrors = this.validationErrorsProp;
  },
};
</script>
