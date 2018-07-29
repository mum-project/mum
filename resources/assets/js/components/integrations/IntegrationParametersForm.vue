<template>
    <div>
        <div class="mb-2 flex flex-row items-center">
            <h3>Parameters</h3>
            <div class="ml-2 text-xs" v-if="availablePlaceholders">
                <a class="text-grey no-underline hover:text-grey-dark focus:text-grey-dark"
                   title="Add new Parameter"
                   href="#"
                   @click.prevent="emitModalContentData"
                >
                    <i class="fas fa-plus mr-2" aria-hidden="true"></i>
                </a>
            </div>
        </div>
        <div>
            <p class="text-grey-dark italic text-sm" v-if="parameters.length < 1 && availablePlaceholders">
                You haven't configured any parameters for this integration.
            </p>
            <p class="text-grey-dark italic text-sm" v-if="parameters.length < 1 && !availablePlaceholders">
                Please select a model to add new parameters.
            </p>
            <div class="inline-code my-1 mr-2" v-for="parameter in parameters">
                <code>{{ parameterString(parameter) }}</code>
                <button class="ml-2 text-grey-dark hover:text-black" @click.prevent="deleteParameter(parameter)"
                        title="Remove Parameter">&times;
                </button>
            </div>
        </div>

        <div>
            <input type="hidden" v-for="parameter in parameters"
                   :name="'parameters[' + parameters.indexOf(parameter) + '][option]'"
                   :value="parameter.option">
            <input type="hidden" v-for="parameter in parameters"
                   :name="'parameters[' + parameters.indexOf(parameter) + '][value]'"
                   :value="parameter.value">
            <input type="hidden" v-for="parameter in parameters"
                   :name="'parameters[' + parameters.indexOf(parameter) + '][use_equal_sign]'"
                   :value="parameter.use_equal_sign ? 1 : 0">
        </div>
    </div>
</template>

<script>
export default {
  props: {
    availablePlaceholders: {
      type: Array,
    },
    oldParameters: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      parameters: [],
      showForm: false,
    };
  },
  methods: {
    addParameter(parameter) {
      this.parameters.push(parameter);
    },
    deleteParameter(parameter) {
      this.parameters.splice(this.parameters.indexOf(parameter), 1);
    },
    parameterString(parameter) {
      let delimiter = parameter.use_equal_sign ? '=' : ' ';
      let option = parameter.option != null ? parameter.option + delimiter : '';
      return option + '\'' + parameter.value + '\'';
    },
    emitModalContentData() {
      let callback = this.addParameter;
      this.$emit('set-modal-content-payload', {
        availablePlaceholders: this.availablePlaceholders,
        modalWidthLarge: true,
        callback: function(data) {
          callback(data);
        },
      });
      this.$emit(
        'set-modal-content-identifier',
        'new-integration-parameter-form'
      );
    },
  },
  created() {
    this.parameters = this.oldParameters;
  },
};
</script>
