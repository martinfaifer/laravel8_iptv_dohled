<template>
    <v-main>
        <br />
        <v-divider></v-divider>
        <div class="text-center mt-12">
            <span>
                <strong> Výpis z dokumentace </strong>
            </span>
        </div>
        <v-card flat color="transparent">
            <v-container v-if="!stream.status">
                <!-- obsah z dokumentace získaný po API -->
            </v-container>
            <v-container v-else>
                <div>
                    <v-alert dense text type="info">
                        <strong>{{stream.message}} </strong>
                    </v-alert>
                </div>
            </v-container>
        </v-card>
    </v-main>
</template>
<script>
export default {
    props: ["streamId"],
    data: () => ({
        stream: []
    }),

    created() {
        this.getStreamDokuInfo();
    },
    methods: {
        getStreamDokuInfo() {
            window.axios
                .post("streamInfo/doku", {
                    streamId: this.streamId
                })
                .then(response => {
                    this.stream = response.data;
                });
        }
    },

    mounted() {},
    watch: {
        $route(to, from) {
            this.getStreamDokuInfo();
        }
    }
};
</script>

