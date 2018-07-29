<template>
    <div :class="[classes, 'form-addon-wrapper']">
        <input
                v-model="password"
                class="form-addon-input"
                :name="name"
                :id="id"
                :placeholder="placeholder"
                :type="inputType"
                :required="required"
                :minlength="minlength"
        >
        <div class="form-addon text-grey">
            <i
                    :class="{ fas: true, 'cursor-pointer': true, 'fa-eye': !showPassword, 'fa-eye-slash': showPassword }"
                    @click="showPassword = !showPassword"
                    aria-label="Show password"
            ></i>
        </div>
    </div>
</template>

<script>
export default {
  props: {
    name: '',
    id: '',
    classes: '',
    required: {
      type: Boolean,
    },
    placeholder: '',
    minlength: '',
  },
  data() {
    return {
      showPassword: false,
      password: '',
    };
  },
  computed: {
    inputType() {
      return this.showPassword ? 'text' : 'password';
    },
  },
};
</script>
