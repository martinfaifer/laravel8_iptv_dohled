<template>
    <v-main v-if="streams != null">
        <v-toolbar background-color="transparent" color="transparent">
            <v-row>
                <v-col
                    cols="12"
                    sm="12"
                    md="3"
                    lg="3"
                    v-for="stream in errorStreams"
                    :key="stream.id"
                >
                    <v-alert :type="stream.status" outlined>
                        {{ stream.msg }}
                    </v-alert>
                </v-col>
            </v-row>
        </v-toolbar>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        streams: null,
        count: 0,
        errorStreams: null
    }),

    created() {
        this.getErrorStreams();
    },

    methods: {
        getErrorStreams() {
            axios.get("streamAlerts").then(response => {
                if (response.data.length > 0) {
                    this.errorStreams = response.data;
                    this.count = response.data.length;
                } else {
                    this.errorStreams = null;
                    this.count = 0;
                }
            });
        }
    },

    mounted() {
        setInterval(
            function() {
                try {
                    this.getErrorStreams();
                } catch (error) {}
            }.bind(this),
            2000
        );
    }
};
</script>
