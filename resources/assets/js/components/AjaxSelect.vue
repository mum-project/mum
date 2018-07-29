<template>
    <div class="inline-block relative w-full">
        <select class="form-input" :required="required" :name="name" :id="id">
            <option
                    disabled
                    selected
            >{{ placeholder }}</option>
            <option
                    v-for="option in options"
                    :value="option.value"
                    :selected="option.selected"
            >{{ option.label }}</option>
        </select>
        <div class="pointer-events-none absolute pin-y pin-r flex items-center px-2 text-grey-darker">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
            </svg>
        </div>
    </div>
</template>

<script>
export default {
  props: {
    name: '',
    id: '',
    placeholder: '',
    required: {
      type: Boolean,
      default: false,
    },
    apiUrl: {
      type: String,
      required: true,
    },
    apiMethod: {
      type: String,
    },
    mapApiValues: {
      type: Function,
      required: true,
    },
    selectedValue: String,
  },
  data() {
    return {
      options: [],
    };
  },
  mounted() {
    axios({
      method: this.apiMethod || 'get',
      url: this.apiUrl,
    }).then(response => {
      response.data.data.forEach(d => {
        this.mapApiValues(this.options, d);
      });
      if (this.selectedValue == null) return;
      let selectedOption = this.options.find(option => {
        return option.value == this.selectedValue;
      });
      if (selectedOption == null) return;
      selectedOption.selected = true;
    });
  },
};
</script>
