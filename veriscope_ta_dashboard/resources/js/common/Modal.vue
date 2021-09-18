<template>
    <transition name="appear">
        <div class="modal" v-if="title" @click.self=modalClose @keyup.esc=modalClose>
            <div class="modal__frame">
                <span class="modal__close" @click=modalClose>
                    <svg width="22px" height="22px" viewBox="0 0 22 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <g id="Close" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <path d="M10.6066017,8.48528137 L19.0918831,-2.84217094e-14 L21.2132034,2.12132034 L12.7279221,10.6066017 L21.2132034,19.0918831 L19.0918831,21.2132034 L10.6066017,12.7279221 L2.12132034,21.2132034 L-2.8643754e-14,19.0918831 L8.48528137,10.6066017 L-2.88657986e-14,2.12132034 L2.12132034,-2.70894418e-14 L10.6066017,8.48528137 Z" id="Desktop-Close" fill="currentColor"></path>
                        </g>
                    </svg>
                </span>
                <img v-if="headerImg" :src=headerImg class="modal__img">
                <div class="modal__content">
                    <p v-if="title" class="modal__title"><strong>{{ title }}</strong></p>
                    <slot></slot>
                </div>
                <a v-if="driverLink && driverLabel" :href=driverLink class="modal__cta btn">{{ driverLabel }}</a>
            </div>
        </div>
    </transition>
</template>
<script>
export default {
    props: {
        headerImg: {
            type: String,
        },
        title: {
            type: String,
        },
        driverLink: {
            type: String,
        },
        driverLabel: {
            type: String,
        },
    },
    methods: {
        modalClose() {
            this.$emit('modal-close');
        },
    }
}
</script>
<style>
    .appear-enter-active, .appear-leave-active {
        transition: all .3s ease;
    }
    .appear-enter, .appear-leave-to {
        opacity: 0;
        padding-top: 40px;
    }
</style>