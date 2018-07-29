<template>
    <div>
        <slot
                v-bind:modelClass="selectedModelClass"
                v-bind:availablePlaceholders="availablePlaceholders"
                v-bind:integrationType="selectedIntegrationType"
        ></slot>
    </div>
</template>

<script>
export default {
  props: {
    modelClass: {
      type: String,
    },
    integrationType: {
      type: String,
    },
  },
  data() {
    return {
      selectedModelClass: {
        value: '',
      },
      selectedIntegrationType: {
        value: '',
      },
    };
  },
  created() {
    this.selectedModelClass.value = this.modelClass;
    this.selectedIntegrationType.value = this.integrationType;
  },
  computed: {
    availablePlaceholders() {
      switch (this.selectedModelClass.value) {
        case 'App\\Domain':
          return [
            '%{id}',
            '%{domain}',
            '%{description}',
            '%{quota}',
            '%{max_quota}',
            '%{max_aliases}',
            '%{max_mailboxes}',
            '%{active}',
          ];
          break;
        case 'App\\Mailbox':
          return [
            '%{id}',
            '%{local_part}',
            '%{name}',
            '%{domain}',
            '%{alternative_email}',
            '%{quota}',
            '%{homedir}',
            '%{maildir}',
            '%{is_super_admin}',
            '%{address}',
            '%{send_only}',
            '%{active}',
          ];
          break;
        case 'App\\Alias':
          return [
            '%{id}',
            '%{local_part}',
            '%{address}',
            '%{description}',
            '%{domain}',
            '%{active}',
          ];
          break;
        default:
          return null;
      }
    },
  },
};
</script>
