<template>
    <div>
        <div :class="[classes, addon ? 'form-addon-wrapper' : '']">
            <input
                    v-model="inputText"
                    :class="addon ? 'form-addon-input' : ''"
                    :name="name"
                    :id="id"
                    :placeholder="placeholder"
                    :type="inputType"
                    :required="required"
            >
            <div class="form-addon text-grey-dark">
                {{ addon }}
            </div>
        </div>
        <p class="form-help text-red"
           v-if="validationError"
        >{{ validationError }}</p>
        <p class="form-help"
           :id="id ? id : name + '-form-help'"
        >{{ formHelp }}
            <br v-if="formHelp">
            <button class="link-text" @click.prevent="setRandomValue">Generate</button>
            a random string for this value.
        </p>
    </div>
</template>

<script>
const dwGen = require('diceware-generator');
const en = require('diceware-wordlist-en-eff');
export default {
  props: {
    name: '',
    id: '',
    classes: '',
    required: {
      type: Boolean,
    },
    oldValue: {
      type: String,
      default: null,
    },
    addon: '',
    placeholder: '',
    validationError: '',
    formHelp: '',
    inputType: {
      type: String,
      default: 'text',
    },
    randomProvider: {
      type: String,
      validator: val => ['diceware', 'insecureRandom'].includes(val),
      required: true,
    },
    dicewareWordCount: {
      type: Number,
      default: 6,
    },
    dicewareSeparator: {
      type: String,
      default: '-',
    },
    insecureRandomCharCount: {
      type: Number,
      default: 20,
    },
  },
  data() {
    return {
      inputText: '',
    };
  },
  methods: {
    setRandomValue() {
      this.inputText = this.generateRandomString();
    },
    generateRandomString() {
      if (this.randomProvider === 'insecureRandom') {
        return _.times(this.insecureRandomCharCount, () =>
          _.random(35).toString(36)
        ).join('');
      }
      const options = {
        language: en,
        wordcount: this.dicewareWordCount,
        format: 'array',
      };
      return dwGen(options).join(this.dicewareSeparator);
    },
  },
  created() {
    if (this.oldValue) {
      this.inputText = this.oldValue;
    }
  },
};
</script>
