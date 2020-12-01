<template>
    <div>
        <h1>test</h1>
        <v-sparkline
            height="20%"
            :smooth="16"
            :gradient="['#DAE0E2']"
            auto-draw
            :line-width="1"
            :value="data"
            stroke-linecap="round"
        ></v-sparkline>
    </div>
</template>
<script>
export default {
    data() {
        return {
            interval: false,
            percent: "",
            data: [],
            labels: []
        };
    },
    created() {},
    watch: {},
    methods: {},
    mounted() {
        this.interval = setInterval(
            function() {
                let currentObj = this;
                window.axios.get("system/usage/areaChart").then(response => {
                    this.data.push(response.data.data);
                    this.labels.push(response.data.nyni);
                });
            }.bind(this),
            1000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.interval);
    }
};
</script>
