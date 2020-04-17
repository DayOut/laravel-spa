<template>
    <div class="relative pb-4">
        <label :for="name" class="text-blue-500 uppercase text-xs font-bold absolute">{{label}}</label>
        <input :id="name" type="text"
               class="pt-8 w-full text-gray-900 border-b pb-2 focus:outline-none focus:border-blue-500"
               :placeholder="placeholder"
               v-model="value"
               @input="updateField()"
        />
    </div>
</template>

<script>
  export default {
    name: "InputField",
    props: [
        'name', 'label', 'placeholder'
    ],

    data: function() {
      return {
        value: ''
      }
    },

    methods: {
      updateField: function () {
		this.$emit('update:field', this.value)
      }
    }
  }
</script>

<style scoped>

</style>
