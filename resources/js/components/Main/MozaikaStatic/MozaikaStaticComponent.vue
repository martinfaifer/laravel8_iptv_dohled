<template>
    <div>
        <v-container fluid>
            <div class="ml-12 body-1">
                <v-row>
                    <strong>
                        Statické kanály
                    </strong>
                </v-row>
            </div>
            <v-row class="mx-auto mt-1 ma-1 mr-1">
                <v-col
                    v-for="stream in usermozaika"
                    :key="stream.id"
                    class="mt-2"
                >
                    <v-hover v-slot:default="{ hover }">
                        <v-card
                            link
                            :to="'stream/' + stream.id"
                            :elevation="hover ? 24 : 0"
                            class="mx-auto ma-0 transition-fast-in-fast-out"
                            height="180"
                            width="280"
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
                                                    čeká se na vytvoření náhledu
                                                    ...
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
                                :lazy-src="stream.image"
                                :src="stream.image"
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
            <div class="ml-12 mt-6 body-1">
                <v-row>
                    <strong>
                        Dynamické kanály
                    </strong>
                </v-row>
            </div>
        </v-container>
    </div>
</template>
<script>
export default {
    props: ["usermozaika"],

    data: () => ({
        streams: null
    }),

    mounted() {
        setInterval(
            function() {
                try {
                    this.usermozaika();
                } catch (error) {}
            }.bind(this),
            2000
        );
    }
};
</script>
