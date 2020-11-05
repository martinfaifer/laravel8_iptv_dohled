<template>
    <v-main>
        <div class="text-center mt-12">
            <span>
                <strong> Plánované akce </strong>
            </span>
            <v-card flat color="transparent">
                <v-container class="text-center body-2" v-if="sheduler != null">
                    <!-- obsah z dokumentace získaný po API -->
                    <v-card flat color="transparent" dense>
                        <v-data-table
                            dense
                            :headers="headers"
                            :items="sheduler"
                            :items-per-page="5"
                            class="elevation-0"
                        ></v-data-table>
                    </v-card>
                </v-container>
                <v-container v-else>
                    <div>
                        <v-alert dense text type="info">
                            <strong>Není plánovaná žádná událost</strong>
                        </v-alert>
                    </div>
                </v-container>
            </v-card>
        </div>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        sheduler: null,
        headers: [
            {
                text: "Začátek",
                align: "start",
                value: "start"
            },
            { text: "Konec", value: "end" }
        ]
    }),

    created() {
        this.getShedulerInfo();
    },
    methods: {
        getShedulerInfo() {
            window.axios
                .post("streamInfo/sheduler", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    if (response.data.status == "empty") {
                        this.sheduler = null;
                    } else {
                        this.sheduler = response.data;
                    }
                });
        }
    },

    computed: {},
    mounted() {},
    watch: {
        $route(to, from) {
            this.getShedulerInfo();
        }
    }
};
</script>
