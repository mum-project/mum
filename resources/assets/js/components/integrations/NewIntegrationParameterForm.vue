<template>
    <div>
        <h2 class="font-extrabold mb-6">New Parameter</h2>
        <div class="mb-4">
                Preview:
                <code class="inline-code leading-normal">{{ previewString.length > 1 ? previewString : '&nbsp;' }}</code>
        </div>
        <div class="form-multi-row mb-3">
            <div class="form-group sm:w-1/2">
                <label class="mb-3 form-label">Option</label>
                <input class="mb-3 form-input" type="text" placeholder="--option" v-model="inputOption">
            </div>
            <div class="form-group sm:w-1/2">
                <label class="mb-3 form-label">Value</label>
                <input class="mb-3 form-input" type="text" placeholder="value" v-model="inputValue">
                <p class="form-help">You may use placeholders in this field.</p>
            </div>
        </div>
        <div class="form-row">
                <label class="checkbox-label">
                    Use equal sign between option and value
                    <input type="checkbox" v-model="inputUseEqualSign">
                    <span class="checkmark"></span>
                </label>
        </div>
        <div class="form-footer">
            <button @click.prevent="addParameter" class="btn btn-primary">Add Parameter</button>
        </div>
        <div class="mt-6 border-t-2 border-grey-lighter pt-4 text-grey-darker" v-if="availablePlaceholders != null">
            <p>Available Placeholders:
                <code class="inline-code mx-1 my-1"
                      v-for="placeholder in availablePlaceholders">
                    {{ placeholder }}
                </code>
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
      inputOption: '',
      inputUseEqualSign: false,
      inputValue: '',
    };
  },
  computed: {
    previewString() {
      let delimiter = this.inputUseEqualSign ? '=' : ' ';
      let option =
        this.inputOption.length > 0 ? this.inputOption + delimiter : '';
      return (
        option + (this.inputValue.length > 0 ? '"' + this.inputValue + '"' : '')
      );
    },
    availablePlaceholders() {
      return this.payload.availablePlaceholders;
    },
  },
  methods: {
    addParameter() {
      let parameter = {
        option: this.inputOption,
        value: this.inputValue,
        use_equal_sign: this.inputUseEqualSign,
      };
      this.payload.callback(parameter);
      this.$emit('close');
    },
  },
};
</script>
