<template>
    <v-main>
        <v-container fluid>
            <v-row>
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
                            <v-row
                                class="fill-height ma-0 mt-12"
                                justify="center"
                            >
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
                            <v-row
                                class="fill-height ma-0 mt-12"
                                justify="center"
                            >
                                <div class="ml-2">
                                    {{ stream.nazev }}
                                    <v-row
                                        v-if="
                                            stream.status == 'success' ||
                                                stream.status == 'issue' ||
                                                stream.status ==
                                                    'diagnostic_crash'
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
                <!-- <doku-component></doku-component> -->

                <streaminfosheduler-component></streaminfosheduler-component>
            </v-row>
        </v-container>
    </v-main>
</template>
<script>
// import DokuComponent from "./StreamInfoApiDokuComponent";
import StreamInfoShedulerComponent from "./StreamInfoShedulerComponent";
export default {
    data: () => ({
        stream: [],
        streamId: null,
        routeId: null
    }),

    components: {
        // "doku-component": DokuComponent,
        "streaminfosheduler-component": StreamInfoShedulerComponent
    },
    created() {
        this.getStreamImage();
        this.bindRouteId();
    },
    methods: {
        bindRouteId() {
            this.routeId = this.$route.params.id;
        },
        async getStreamImage() {
            try {
                axios
                    .post("streamInfo/image", {
                        streamId: this.$route.params.id
                    })
                    .then(response => {
                        this.stream = response.data;
                    });
            } catch (error) {
                console.log(error);
            }
        },

        websocketData() {
            try {
                Echo.channel("StreamImage" + this.$route.params.id).listen(
                    "StreamImage",
                    e => {
                        this.stream.image = e[0];
                    }
                );
            } catch (error) {
                this.stream = 'false';
            }
        },

        waitForConnectToWebsocket() {
            setTimeout(() => {
                this.websocketData();
            }, 15000);
        }
    },

    mounted() {
       this.websocketData();
    },
    watch: {
        $route(to, from) {
            this.bindRouteId();
            this.getStreamImage();
            Echo.leaveChannel("StreamImage" + from.params.id);

            Echo.channel("StreamImage" + to.params.id).listen(
                "StreamImage",
                e => {
                    this.stream.image = e[0];
                }
            );
        }
    },

    beforeDestroy() {
        Echo.leaveChannel("StreamImage" + this.routeId);
    }
};
</script>
