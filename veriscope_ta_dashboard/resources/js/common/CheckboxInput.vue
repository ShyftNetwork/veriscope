<template>
    <div class="form-control">
        <label v-if="title">{{ title }}</label>
        <div class="relative" :key="option.value" v-for="(option, i) in options">
            <div class="form__checkbox form__checkbox--alt">
                <input type="checkbox" :id="getId(i)" :value="option.id" :checked="isChecked(option.id)" :name="getName()" v-model="checkedItems" @change=updateValues>
                <label :for="getId(i)"><span></span> {{ option.name }}</label>
            </div>
            <a v-if="option.description" href="#0" class="tooltipTrigger" v-tooltip="{ content: option.description, trigger: 'click hover focus'}" style="top: 0;"><img src="/images/icon-info.svg" alt="Info"></a>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            checkedItems: this.checked || []
        }
    },
    props: {
        title: {
            type: String,
            required: true
        },
        options: {
            type: Array,
            required: true
        },
        model: {
            type: String,
            required: true
        },
        checked: {
            type: Array,
            required: false,
            default: () => []
        }
    },
    watch: {
        checked(value) {
            this.checkedItems = value;
        }
    },
    methods: {
        getId: function(index) {
            return `${this.getName()}_${index}`;
        },
        getName: function() {
            return `${this.model}[]`;
        },
        updateValues() {
            this.$emit('input', this.checkedItems);
        },
        isChecked(val) {
            return this.checkedItems.includes(val);
        }
    }
}
</script>