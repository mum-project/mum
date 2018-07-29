<template>
    <div>
        <div v-if="!payload.options.isExternal">
            <div class="form-label mb-2">Search for a Mailbox</div>
            <div class="form-addon-wrapper">
                <input class="form-addon-input" type="text" v-model="searchInput"
                       @keydown="triggerFetch"
                       @keydown.enter.prevent="fetchMailboxSearchResults"
                       placeholder="jon.doe@example.com">
                <div class="form-addon">
                    <button
                            class="text-grey hover:text-grey-darker focus:text-grey-darker"
                            @click.prevent="fetchMailboxSearchResults"
                    >
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="flex flex-row flex-wrap justify-center -mx-4 my-2">
                <label class="checkbox-label mx-4 my-1">
                    Add to Senders
                    <input type="checkbox" v-model="payload.options.isSender">
                    <span class="checkmark"></span>
                </label>
                <label class="checkbox-label mx-4 my-1">
                    Add to Recipients
                    <input type="checkbox" v-model="payload.options.isRecipient">
                    <span class="checkmark"></span>
                </label>
            </div>
            <div class="my-2 text-grey-dark text-sm text-center italic">
                Please select one of the mailboxes below:
            </div>
            <div class="border-grey-lighter border rounded relative">
                <div v-if="!payload.options.isSender && !payload.options.isRecipient"
                     class="absolute pin-x pin-y cursor-not-allowed bg-grey-light opacity-25">
                </div>
                <div class="h-48 overflow-y-scroll">
                    <div v-if="mailboxSearchResponse">
                        <div class="py-1 px-3 border-b border-grey-lightest"
                             v-for="mailbox in mailboxSearchResponse.data">
                            <a class="block py-3 no-underline text-grey-darker hover:text-black group"
                               href="#"
                               @click.prevent="addMailbox(mailbox)"
                            >
                                <i class="fas fa-inbox mr-2 text-grey group-hover:text-grey-darker"></i>
                                {{ mailbox.address }}
                            </a>
                        </div>
                    </div>
                    <div v-else class="flex flex-col h-full items-center justify-center">
                        <div class="text-center italic text-grey-dark">
                            Loading...
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="">
            <div class="form-label mb-2">Add External Recipient</div>
            <div :class="{'form-addon-wrapper': true, 'border-red': validationError}">
                <input class="form-addon-input" type="email" v-model="searchInput"
                       placeholder="jon.doe@example.com"
                       @keydown.enter.prevent="addExternalRecipient">
                <div class="form-addon">
                    <button
                            class="text-grey hover:text-grey-darker focus:text-grey-darker"
                            @click.prevent="addExternalRecipient"
                    >
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <p v-if="validationError" class="form-help text-red mt-2">{{ validationError }}</p>
            <p class="form-help mt-2">
                Please specify the external address that should receive all incoming emails
                for this alias.
            </p>
        </div>
    </div>
</template>

<script>
export default {
  props: {
    payload: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      searchInput: '',
      mailboxSearchResponse: null,
      validationError: '',
    };
  },
  methods: {
    triggerFetch: _.debounce(function() {
      this.fetchMailboxSearchResults();
    }, 300),
    fetchMailboxSearchResults() {
      axios
        .get('/mailboxes', {
          params: {
            search: this.searchInput,
          },
        })
        .then(response => {
          this.mailboxSearchResponse = response.data;
        })
        .catch(fail => {
          console.log(fail.response.data);
        });
    },
    addMailbox(mailbox) {
      this.payload.options.show = false;
      this.payload.callback(this.payload.options, mailbox);
      this.$emit('close');
    },
    addExternalRecipient() {
      let re = /^\S+@[^@.\s]+[^@.\s]+(\.[^@.\s]+)*$/;
      if (!re.test(this.searchInput)) {
        this.validationError = 'Please enter a valid email address.';
        return;
      }
      this.payload.options.show = false;
      this.payload.callback(this.payload.options, this.searchInput);
      this.$emit('close');
    },
  },
  mounted() {
    if (!this.payload.options.external) {
      this.fetchMailboxSearchResults();
    }
  },
};
</script>

<style scoped>
.popfade-enter-active,
.popfade-leave-active {
  transition: all 0.2s;
  transform: scale(1);
}

.popfade-enter, .popfade-leave-to /* .fade-leave-active below version 2.1.8 */
 {
  opacity: 0;
  transform: scale(0.9) translateY(2em);
}
</style>
