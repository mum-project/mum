<template>
    <popup-modal
            @close="$emit('close')"
            :class="{ 'max-w-md': modalContentPayload && modalContentPayload.hasOwnProperty('modalWidthLarge') }"
    >
        <alias-add-sender-recipient-form
                v-if="modalContentIdentifier === 'alias-mailbox-form'"
                :payload="modalContentPayload"
                @close="$emit('close')"
        ></alias-add-sender-recipient-form>
        <new-integration-parameter-form
                v-if="modalContentIdentifier === 'new-integration-parameter-form'"
                :payload="modalContentPayload"
                @close="$emit('close')"
        ></new-integration-parameter-form>
    </popup-modal>
</template>

<script>
import AliasAddSenderRecipientForm from './aliasSendersRecipients/AliasAddSenderRecipientForm';
import NewIntegrationParameterForm from './integrations/NewIntegrationParameterForm';

export default {
  props: {
    modalContentIdentifier: {
      type: String,
      validator: val =>
        ['alias-mailbox-form', 'new-integration-parameter-form'].includes(val),
      required: true,
    },
    modalContentPayload: {},
  },
  components: {
    AliasAddSenderRecipientForm,
    NewIntegrationParameterForm,
  },
};
</script>
