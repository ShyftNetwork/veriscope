<template>
    <div :class="className" @click="onClick">
        <p>
            <img :src="imagePath"> 
            {{ message }}
        </p>
    </div>
</template>
<script>
export default {
    props: {
        message: {
            type: String,
            required: true
        },        
        type: {
            type: String,
            default: '',
        },
    },
    computed: {
        className: function() {
            return (this.type === 'error' || this.type === 'success') ? `notification notification--open notification--${this.type}` : 'notification notification--open';
        },
        imagePath: function() {
            return (this.type === 'error' || this.type === 'success') ? `/images/notifications/${this.type}.svg` : '/images/notifications/general.svg';
        }
    },
    methods: {
        onClick() {
            this.$emit('click');
        }
    }
}
</script>