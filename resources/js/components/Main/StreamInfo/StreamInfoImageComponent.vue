<template>
    <v-main>
        <div class="text-center mt-2 pd-12">
            <small>
                <strong>
                    {{ stream.nazev }}
                </strong>
            </small>
        </div>
        <v-hover v-slot:default="{ hover }">
            <v-card
                :elevation="hover ? 24 : 0"
                class="mx-auto ma-0 transition-fast-in-fast-out"
                height="100%"
                width="100%"
                :class="{
                    success: stream.status == 'success',
                    error: stream.status == 'error',
                    warning: stream.status == 'issue',
                    transparent: stream.status == 'waiting'
                }"
            >
                <!-- status stream waiting -->
                <v-img
                    v-if="stream.status == 'waiting'"
                    :elevation="hover ? 12 : 0"
                    class="transition-fast-in-fast-out"
                >
                    <v-row class="fill-height ma-0 mt-12" justify="center">
                        <v-progress-circular
                            indeterminate
                            color="blue lighten-2"
                        ></v-progress-circular>
                        <div class="ml-2">
                            {{ stream.nazev }}
                            <v-row>
                                <small class="ml-3 blue--text">
                                    čeká na zpracování ...
                                </small>
                            </v-row>
                        </div>
                    </v-row>
                </v-img>

                <v-img
                    v-else-if="stream.image == 'false'"
                    :elevation="hover ? 24 : 0"
                    class="transition-fast-in-fast-out"
                >
                    <v-row class="fill-height ma-0 mt-12" justify="center">
                        <div class="ml-2">
                            {{ stream.nazev }}
                            <v-row
                                v-if="
                                    stream.status == 'success' ||
                                        stream.status == 'issue' ||
                                        stream.status == 'diagnostic_crash'
                                "
                            >
                                <small class="ml-3 white--text">
                                    <strong>
                                        čeká se na vytvoření náhledu ...
                                    </strong>
                                </small>
                            </v-row>
                            <v-row v-if="stream.status == 'error'">
                                <small class="ml-3 white--text">
                                    <strong>
                                        stream je ve výpadku ...
                                    </strong>
                                </small>
                            </v-row>
                        </div>
                    </v-row>
                </v-img>
                <v-img
                    v-else
                    height="98%"
                    width="100%"
                    :lazy-src="stream.image"
                    :src="stream.image"
                    :aspect-ratio="16 / 9"
                >
                </v-img>
            </v-card>
        </v-hover>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        stream: [],
        streamId: null
    }),

    created() {
        this.getStreamImage();
    },
    methods: {
        getStreamImage() {
            window.axios
                .post("streamInfo/image", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    this.stream = response.data;
                });
        },

        exitWebsocektChannels() {
            Echo.leaveChannel("StreamImage" + this.$route.params.id);
        },

        websocketData() {
            Echo.leaveChannel("StreamImage" + this.$route.params.id);

            Echo.channel("StreamImage" + this.$route.params.id).listen(
                "StreamImage",
                e => {
                    this.stream.image = e[0];
                }
            );
        },

        waitForConnectToWebsocket() {
            let currentObj = this;
            setTimeout(function() {
                currentObj.websocketData();
            }, 2000);
        }
    },

    mounted() {
        // StreamImage
        Echo.channel("StreamImage" + this.$route.params.id).listen(
            "StreamImage",
            e => {
                this.stream.image = e[0];
            }
        );
    },
    watch: {
        $route(to, from) {
            this.getStreamImage();
            this.waitForConnectToWebsocket()
        },
    },

    destroyed() {
        Echo.leaveChannel("StreamImage" + this.$route.params.id);
    }
};
</script>
