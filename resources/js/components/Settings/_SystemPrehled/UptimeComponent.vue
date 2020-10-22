<template>
    <div>
        <span class="green--text">
            server funguje {{uptime}}
        </span>
    </div>
</template>
<script>
export default {
    data() {
        return {
            uptime: ""
        };
    },
    created() {
        this.loadUptime();
    },
    methods: {
        loadUptime() {
            window.axios.get("uptime").then(response => {
                this.uptime = response.data;
            });
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadUptime();
            }.bind(this),
            60000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.interval);
    }
};
</script>
