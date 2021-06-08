<template>
    <div>
        <v-container fluid v-if="errorStreams != null">
            <div class="ml-12 body-1">
                <v-row>
                    <strong>
                        Nefunknčí kanály
                    </strong>
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
                            height="180"
                            width="300"
                            :class="{
                                'green darken-1': stream.status == 'success',
                                'green darken-3':
                                    stream.status == 'diagnostic_crash',
                                'red darken-4': stream.status == 'error',
                                'deep-orange accent-1':
                                    stream.status == 'issue',
                                '#202020': stream.status == 'waiting'
                            }"
                        >
                            <v-img
                                :elevation="hover ? 24 : 0"
                                class="transition-fast-in-fast-out"
                            >
                                <v-row
                                    class="fill-height ma-0 mt-12"
                                    justify="center"
                                >
                                    <div class="ml-1">
                                        <v-row>
                                            <strong>
                                                {{ stream.nazev }} je ve výpadku
                                            </strong>
                                        </v-row>
                                    </div>
                                </v-row>
                            </v-img>
                        </v-card>
                    </v-hover>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>
<script>
export default {
    data: () => ({
        streams: null,
        errorStreams: null
    }),

    created() {
        this.getErrorStreams();
    },

    methods: {
        getErrorStreams() {
            axios.get("error/streams").then(response => {
                if (response.data.status === "success") {
                    this.errorStreams = response.data.data;
                } else {
                    this.errorStreams = null;
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
