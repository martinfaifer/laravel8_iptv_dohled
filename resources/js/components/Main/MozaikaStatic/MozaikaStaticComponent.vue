<template>
    <v-main>
        <v-container fluid>
            <div class="ml-12 mt-1 body-1">
                <v-row class="ml-1">
                    <span class="text--disabled font-weight-medium">
                        Statické streamy
                    </span>
                </v-row>
            </div>
            <v-row class="mx-auto mt-1 ma-1 mr-1">
                <v-col
                    v-for="stream in usermozaika"
                    :key="stream.id"
                    class="mt-2"
                >
                    <v-card
                        link
                        :to="'stream/' + stream.id"
                        class="mx-auto ma-0 "
                        height="160"
                        width="280"
                        color="#080808"
                    >
                        <!-- mdi-loading -->
                        <!-- status stream waiting -->
                        <v-img v-if="stream.status == 'waiting'">
                            <v-row
                                class="fill-height ma-0 mt-12"
                                justify="center"
                            >
                                <div class="ml-2">
                                    <p class="white--text text-center">
                                        čeká se na zpracování...
                                        <i
                                            style="color:#BDBDBD"
                                            class="fas fa-spinner fa-spin fa-2x"
                                        ></i>
                                    </p>
                                </div>
                            </v-row>
                        </v-img>

                        <v-img v-else-if="stream.image == 'false'">
                            <v-row class="fill-height ma-0 mt-6">
                                <div class="ml-2">
                                    <v-col cols="12">
                                        <p class="white--text text-center">
                                            {{ stream.nazev }}
                                        </p>
                                        <p class="white--text text-center">
                                            čeká se na náhled...
                                            <i
                                                style="color:#BDBDBD"
                                                class="fas fa-spinner fa-spin fa-2x ml-6"
                                            ></i>
                                        </p>
                                    </v-col>
                                </div>
                            </v-row>
                            <p class="font-weight-bold text-center">
                                {{ stream.nazev }}
                            </p>
                        </v-img>

                        <v-img
                            v-else
                            :lazy-src="stream.image"
                            :src="stream.image"
                            :aspect-ratio="16 / 9"
                        >
                            <p
                                class="font-weight-bold text-center black--text"
                                style="text-shadow: 1px 1px white;"
                            >
                                {{ stream.nazev }}
                            </p>
                        </v-img>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </v-main>
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
