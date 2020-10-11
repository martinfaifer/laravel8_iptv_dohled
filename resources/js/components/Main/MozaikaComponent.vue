<template>
    <v-main class="mt-12">
        <v-container fluid>
            <v-row>
                <v-row class="mx-auto mt-1 ma-1 mr-1">
                    <v-col
                        v-for="stream in streams"
                        :key="stream.id"
                        class="mt-2"
                    >
                        <v-hover v-slot:default="{ hover }">
                            <v-card
                                link
                                :to="'stream/'+ stream.id"
                                target="_blank"
                                :elevation="hover ? 24 : 0"
                                class="mx-auto ma-0 transition-fast-in-fast-out"
                                height="150"
                                width="250"
                                :class="{
                                    success: stream.status == 'success',
                                    green: stream.status == 'diagnostic_crash',
                                    error: stream.status == 'error',
                                    warning: stream.status == 'issue',
                                    transparent: stream.status == 'waiting'
                                }"
                            >
                                <!-- mdi-loading -->
                                <!-- status stream waiting -->
                                <v-img
                                    v-if="stream.status == 'waiting'"
                                    :elevation="hover ? 24 : 0"
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
                                    v-else
                                    lazy-src="/channelsImages/1.jpg"
                                    src="/channelsImages/1.jpg"
                                    :aspect-ratio="16 / 9"
                                >
                                    <v-expand-transition>
                                        <div
                                            v-if="hover"
                                            class="d-flex transition-fast-in-fast-out grey darken-4 v-card--reveal display-1 white--text"
                                            style="height: 100%;"
                                        >
                                            {{ stream.nazev }}
                                        </div>
                                    </v-expand-transition>
                                </v-img>
                            </v-card>
                        </v-hover>
                    </v-col>
                </v-row>
            </v-row>
        </v-container>

        <v-container class="text-center">
            <v-pagination
                v-model="pagination.current"
                :length="pagination.total"
                @input="onPageChange"
                circle
            ></v-pagination>
        </v-container>
    </v-main>
</template>

<script>
export default {
    data: () => ({
        streams: [],
        pagination: {
            current: 1,
            total: 0
        }
    }),

    created() {
        this.getStreams();
    },
    methods: {
        getStreams() {
            window.axios
                .get("pagination?page=" + this.pagination.current)
                .then(response => {
                    this.streams = response.data.data;
                    this.pagination.current = response.data.current_page;
                    this.pagination.total = response.data.last_page;
                });
        },
        onPageChange() {
            this.getStreams();
        }
    },

    mounted() {},
    watch: {}
};
</script>
