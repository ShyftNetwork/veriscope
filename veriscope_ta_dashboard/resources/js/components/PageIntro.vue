<template>
    <div>
        <h1 class="h2" v-if="title">{{ title }}</h1>
        <p class="text-darkgray" v-if="intro">{{ intro }}</p>
    </div>
</template>
<script>
    export default {
        props: {
            title: {
                type: String
            },
            intro: {
                type: String
            }
        }
    };
</script>