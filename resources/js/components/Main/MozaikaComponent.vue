<template>
    <v-main class="mt-12">
        <v-container fluid class="mt-6">
            <v-row>
                <errormozaika-component></errormozaika-component>
            </v-row>
            <v-row>
                <!-- načtení komponentu pro statickou část mozaiky -->
                <staticmozaika-component
                    v-if="loggedUser.mozaika == 'custom'"
                    :usermozaika="loggedUser.customData"
                ></staticmozaika-component>
            </v-row>
            <v-row>
                <div class="ml-12 mt-1 body-1">
                    <v-row class="ml-1">
                        <span class="text--disabled font-weight-medium">
                            Dynamická mozaika
                        </span>
                    </v-row>
                </div>
                <v-row class="mx-auto mt-1 ma-1 mr-1">
                    <v-col
                        v-for="stream in streams"
                        :key="stream.id"
                        class="mt-2"
                    >
                        <v-card
                            link
                            :to="'stream/' + stream.id"
                            class="mx-auto ma-0"
                            height="140"
                            width="250"
                            color="#080808"
                        >
                            <v-img v-if="stream.image == 'false'">
                                <v-row class="fill-height ma-0 mt-6">
                                    <div class="ml-2">
                                        <v-col
                                            cols="12"
                                            v-if="stream.is_problem === 0"
                                        >
                                            <p
                                                class="font-weight-bold text-center"
                                            >
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
            </v-row>
        </v-container>

        <v-container
            class="text-center"
            v-if="streams !== null && streams.length > 0"
        >
            <v-pagination
                class="pt-6"
                color="teal"
                v-model="pagination.current"
                :length="pagination.total"
                @input="onPageChange"
            ></v-pagination>
        </v-container>
    </v-main>
</template>

<script>
import StaticMozaika from "./MozaikaStatic/MozaikaStaticComponent";
import ErrorMozaika from "./MozaikaErrorComponent.vue";
export default {
    metaInfo: {
        title: "IPTV Dohled - mozaika"
    },

    computed: {
        loggedUser() {
            return this.$store.state.loggedUser;
        }
    },
    data: () => ({
        streamsInterval: null,
        paginationInterval: null,
      
        streams: null,
        streamWeb: "",
        pagination: {
            current: 1,
            total: 0
        },
        loading: true,
    }),
    components: {
        "staticmozaika-component": StaticMozaika,
        "errormozaika-component": ErrorMozaika
    },

    created() {
        this.getStreams();
    },
    methods: {
        async getStreams() {
            try {
                await axios
                    .get("pagination?page=" + this.pagination.current)
                    .then(response => {
                        this.streams = response.data.data;
                        this.pagination.current = response.data.current_page;
                        this.pagination.total = response.data.last_page;
                    });
            } catch (error) {}
        },
        onPageChange() {
            this.getStreams();
        }
    },

    mounted() {
        this.streamsInterval = setInterval(
            function() {
                try {
                    this.getStreams();
                } catch (error) {}
            }.bind(this),
            2000
        );

        this.paginationInterval = setInterval(
            function() {
                try {
                    if (this.pagination.current <= this.pagination.total - 1) {
                        this.pagination.current = this.pagination.current + 1;
                        this.getStreams();
                    } else {
                        this.pagination.current = 1;
                        this.getStreams();
                    }
                } catch (error) {}
            }.bind(this),
            30000
        );
    },
    watch: {
        $route(to, from) {
            this.streamsInterval;
            this.paginationInterval;
        }
    },
    beforeDestroy: function() {
        this.streamsInterval;
        this.paginationInterval;
    }
};
</script>
