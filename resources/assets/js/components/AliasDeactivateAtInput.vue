<template>
    <div class="flex flex-col border-t border-b border-grey-lighter py-6 overflow-hidden">
        <transition name="fade" mode="out-in">
            <div class="text-center" v-if="!deactivateAlias" key="false">
                <a href="#" class="text-link text-grey-dark" @click.prevent="deactivateAlias = true">
                    Deactivate this alias automatically</a>
            </div>
            <div v-else key="true">
                <h4 class="mb-4">Deactivate Alias automatically in...</h4>
                <div class="flex flex-row items-start flex-wrap text-grey-dark mb-4 leading-normal">
                    <p class="mr-4">Some suggestions:</p>
                    <a class="mr-4 link-text text-grey-dark" href="#" @click.prevent="chooseSuggestion(0, 0, 10)">10
                        minutes</a>
                    <a class="mr-4 link-text text-grey-dark" href="#" @click.prevent="chooseSuggestion(0, 0, 30)">30
                        minutes</a>
                    <a class="mr-4 link-text text-grey-dark" href="#" @click.prevent="chooseSuggestion(0, 1, 0)">1
                        hour</a>
                    <a class="link-text text-grey-dark" href="#" @click.prevent="chooseSuggestion(1, 0, 0)">1 day</a>
                </div>
                <div class="form-multi-row md:mb-0">
                    <div class="form-group w-full md:w-1/3">
                        <div class="form-addon-wrapper">
                            <input class="form-addon-input text-right" type="number" step="1"
                                   v-model="days"
                                   name="deactivate_at_days"/>
                            <label class="form-addon">Days</label>
                        </div>
                    </div>
                    <div class="form-group w-full md:w-1/3">
                        <div class="form-addon-wrapper">
                            <input class="form-addon-input text-right" type="number" step="1"
                                   v-model="hours"
                                   name="deactivate_at_hours"/>
                            <label class="form-addon">Hours</label>
                        </div>
                    </div>
                    <div class="form-group w-full md:w-1/3">
                        <div class="form-addon-wrapper">
                            <input class="form-addon-input text-right" type="number" step="1"
                                   v-model="minutes"
                                   name="deactivate_at_minutes"/>
                            <label class="form-addon">Minutes</label>
                        </div>
                    </div>
                </div>
                <div class="mt-8 text-center">
                    <a href="#" class="text-link text-grey-dark" @click.prevent="deactivateAlias = false">
                        Don't deactivate this alias automatically</a>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
  props: ['standardDays', 'standardHours', 'standardMinutes'],
  data() {
    return {
      deactivateAlias: false,
      days: 0,
      hours: 0,
      minutes: 0,
    };
  },
  methods: {
    chooseSuggestion(days, hours, minutes) {
      this.days = days;
      this.hours = hours;
      this.minutes = minutes;
    },
  },
  created() {
    this.days = this.standardDays;
    this.hours = this.standardHours;
    this.minutes = this.standardMinutes;
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
 {
  opacity: 0;
}
</style>
