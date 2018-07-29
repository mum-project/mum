<template>
    <div>
        <div class="flex flex-row items-center">
            <div class="form-label">{{ title }}</div>
            <div class="ml-2 text-xs">
                <a class="text-grey no-underline hover:text-grey-dark focus:text-grey-dark"
                   :title="'Create ' + title"
                   href="#"
                   @click.prevent="$emit('create')"
                >
                    <i class="fas fa-plus mr-2" aria-hidden="true"></i>
                </a>
            </div>
        </div>
        <div class="flex flex-row flex-wrap -mx-1 mt-1" v-if="accounts.length > 0">
            <div class="address-pill"
                 v-for="account in accounts"
            >
                <i class="fas fa-inbox text-grey mr-2" v-if="areAccountsMailboxes"></i>
                {{ account.address }}
                <a class="inline px-2 -mr-1 text-grey-dark hover:text-black focus:text-black no-underline select-none"
                   v-if="allowDeleting"
                   :title="'Remove ' + account.address"
                   href="#"
                   @click.prevent="remove(account)">
                    &times;
                </a>
            </div>
        </div>
        <div v-else class="text-sm italic text-grey-dark py-2">{{ noneSelectedText }}</div>
        <div v-if="validationError" class="form-help text-red py-2">{{ validationError }}</div>
    </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      required: true,
    },
    accounts: {
      type: Array,
      required: true,
    },
    noneSelectedText: {
      type: String,
      default: "You haven't selected any addresses yet.",
    },
    allowDeleting: {
      type: Boolean,
      default: true,
    },
    areAccountsMailboxes: {
      type: Boolean,
      default: true,
    },
    validationError: {
      type: String,
      default: null,
    },
  },
  methods: {
    remove(account) {
      this.$emit('remove', account);
    },
  },
};
</script>
