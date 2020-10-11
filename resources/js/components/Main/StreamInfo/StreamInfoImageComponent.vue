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
                    v-else
                    height="98%"
                    width="100%"
                    lazy-src="/channelsImages/1.jpg"
                    src="/channelsImages/1.jpg"
                    :aspect-ratio="16 / 9"
                >
                </v-img>
            </v-card>
        </v-hover>
    </v-main>
</template>
<script>
export default {
    props: ["streamId"],
    data: () => ({
        stream: []
    }),

    created() {
        this.getStreamImage();
    },
    methods: {
        getStreamImage() {
            window.axios
                .post("streamInfo/image", {
                    streamId: this.streamId
                })
                .then(response => {
                    this.stream = response.data;
                });
        }
    },

    mounted() {},
    watch: {}
};
</script>
