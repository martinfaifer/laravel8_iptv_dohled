<template>
    <v-main>
        <v-container fluid v-if="errorStreams != null">

                <div class="ml-12 mt-6 body-1">
                    <v-row>
                        <span class="text--disabled font-weight-medium">
                            Nefunkční / problémové kanály
                            <span class="red--text">{{ count }}</span>
                        </span>
                    </v-row>
                </div>
                <v-row class="mx-auto mt-1 ma-1 mr-1">
                    <v-col
                        v-for="stream in errorStreams"
                        :key="stream.id"
                        class="mt-2"
                    >
                        <v-hover v-slot:default="{ hover }">
                            <v-card
                                link
                                :to="'stream/' + stream.id"
                                :elevation="hover ? 12 : 0"
                                class="mx-auto ma-0 transition-fast-in-fast-out"
                                height="120"
                                width="200"
                                :color="stream.status"
                            >
                                <v-card-text>
                                    <v-col cols="12" sm="12" md="12" lg="12">
                                        <div class="ml-1">
                                            <strong
                                                class="white--text"
                                                v-text="stream.msg"
                                            >
                                            </strong>
                                        </div>
                                    </v-col>
                                </v-card-text>
                            </v-card>
                        </v-hover>
                    </v-col>
                </v-row>

        </v-container>
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
