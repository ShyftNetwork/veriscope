<template>
    <button 
        class="btn"
        :class="{ 'btn--disabled': disabled }"
        @click=onClick
    >
        <slot>Next Step</slot>
    </button>
</template>
<script>
    export default {
        props: {
            onClick: {
                type: Function,
                required: true
            },
            disabled: {
                type: Boolean,
            }
        }
    }
</script>