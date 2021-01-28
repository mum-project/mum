<template>
    <div class="relative">
        <form method="GET">
            <input type="hidden"
                   v-for="hidden in hiddenInputValues"
                   :name="hidden.name"
                   :value="hidden.value">
            <div class="form-addon-wrapper">
                <input class="form-addon-input py-2" type="search" name="search"
                       @keydown="triggerFetch"
                       @focus="hasFocus = true"
                       @blur="triggerLooseFocus"
                       autocomplete="off"
                       placeholder="Search..." v-model="searchInput">
                <div class="form-addon py-2">
                    <button type="submit" class="text-grey-dark hover:text-grey-darkest focus:text-grey-darkest">
                        <i class="fas fa-search" title="Search"></i>
                    </button>
                </div>
            </div>
            <transition name="fade">
                <div class="absolute pin-x pin-t-100"
                     v-if="searchResults && searchResults.length > 0 && shouldShowSuggestions">
                    <div class="overflow-y-scroll w-auto rounded max-w-full bg-white shadow-lg">
                        <div class="flex flex-col w-auto overflow-hidden">
                            <a v-for="result in searchResults"
                               :href="resultLinkUrlBase + '/' + outputIdFunction(result)"
                               class="px-4 py-3 border-b border-grey-lighter text-grey-darker hover:text-black focus:text-black no-underline truncate"
                               :title="outputTextFunction(result)"
                            >{{ outputTextFunction(result) }}</a>
                            <button v-if="hasMoreResults" type="submit"
                                    class="text-center bg-grey-lightest text-sm px-4 py-2 text-grey-darker hover:text-black focus:text-black">
                                See all results
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </form>
    </div>
</template>

<script>
export default {
  props: {
    apiUrl: {
      type: String,
      required: true,
    },
    resultLinkUrlBase: {
      type: String,
      required: true,
    },
    oldValue: {
      type: String,
    },
    outputTextFunction: {
      type: Function,
      required: true,
    },
    outputIdFunction: {
      type: Function,
      default: r => r.id,
    },
    hiddenInputValues: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      searchInput: '',
      hasFocus: false,
      hasHover: false,
      searchResults: null,
      hasMoreResults: false,
    };
  },
  computed: {
    shouldShowSuggestions() {
      return this.hasFocus || this.hasHover;
    },
  },
  methods: {
    mouseOver(bool) {
      console.log('mouse over ' + bool);
    },
    fetchApi() {
      if (this.searchInput.length < 1) {
        return;
      }
      let params = {};
      this.hiddenInputValues.forEach(
        hidden => (params[hidden.name] = hidden.value)
      );
      params.search = this.searchInput;
      axios
        .get(this.apiUrl, {
          params,
        })
        .then(response => {
          this.searchResults = response.data.data.slice(0, 4);
          this.hasMoreResults = response.data.data.length > 4;
        })
        .catch(fail => {
          console.log(fail.response);
        });
    },
    triggerFetch: _.debounce(function() {
      this.fetchApi();
    }, 100),
    triggerLooseFocus: _.debounce(function() {
      this.hasFocus = false;
    }, 300),
  },
  created() {
    if (this.oldValue) {
      this.searchInput = this.oldValue;
    }
  },
  watch: {
    searchInput() {
      if (this.searchInput.length < 1) {
        this.searchResults = null;
      }
    },
  },
};
</script>

<style scoped>
.pin-t-100 {
  top: 100%;
}
</style>
