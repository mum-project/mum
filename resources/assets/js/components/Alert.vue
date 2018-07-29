<template>
    <div v-show="show"
         :class="'relative rounded px-6 py-4 border-t-4 border-' + getColor() +  ' bg-' + getColor() +'-lightest shadow'">
        <div class="flex flex-row items-center">
            <i :class="'fas ' + getIconName() + ' fa-fw text-xl text-' + getColor() + ' mr-6'"></i>
            <div :class="'text-' + getColor() + '-darkest'">
                <h3 class="font-bold mb-2" v-if="showTitle">{{ getTitle() }}</h3>
                <p>
                    <slot></slot>
                </p>
            </div>
        </div>
        <div v-if="dismissible" class="absolute pin-t pin-r mt-1 mr-3 text-xl">
            <a @click.prevent="show = false" href="" aria-label="close"
               :class="'no-underline text-' + getColor() + '-darkest hover:text-'  + getColor() + '-dark'">&times;</a>
        </div>
    </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
    },
    type: {
      type: String,
      required: true,
      validator: value => {
        let validValues = ['error', 'warning', 'info', 'success'];
        return validValues.includes(value);
      },
    },
    dismissible: {
      type: Boolean,
      default: true,
    },
    showTitle: {
      type: Boolean,
      default: true,
    },
    iconClass: {
      type: String,
    },
  },
  data() {
    return {
      show: true,
    };
  },
  methods: {
    getColor() {
      switch (this.type) {
        case 'error':
          return 'red';
        case 'warning':
          return 'orange';
        case 'info':
          return 'blue';
        case 'success':
          return 'green';
      }
    },
    getIconName() {
      if (this.iconClass != null) {
        return this.iconClass;
      }
      switch (this.type) {
        case 'error':
          return 'fa-times-circle';
        case 'warning':
          return 'fa-exclamation-circle';
        case 'info':
          return 'fa-info-circle';
        case 'success':
          return 'fa-check-circle';
      }
    },
    getTitle() {
      let defaultTitle;
      switch (this.type) {
        case 'error':
          defaultTitle = 'Something went wrong!';
          break;
        case 'warning':
          defaultTitle = 'Attention!';
          break;
        case 'info':
          defaultTitle = 'Did you know?';
          break;
        case 'success':
          defaultTitle = 'Good news!';
          break;
      }
      return this.title ? this.title : defaultTitle;
    },
  },
};
</script>
