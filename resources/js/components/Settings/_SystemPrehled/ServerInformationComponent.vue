<template>
    <v-main>
        <v-row>
            <!-- tabulka se serverovým nastavením -->
            <v-card flat color="transparent elevation-0">
                <div class="text-center">
                    <span class="body-2"> Přehled Serveru </span>
                </div>
                <v-data-table
                    :headers="headers"
                    :items="serverInfo"
                    hide-default-header
                    hide-default-footer
                    flat
                    dense
                    class="elevation-0 mt-2"
                >
                    <template v-slot:item.data="{ item }">
                        <span v-if="item.data === 'true'">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.data === 'false'">
                            <v-icon color="red">mdi-close</v-icon>
                        </span>
                        <span v-else>
                            {{ item.data }}
                        </span>
                    </template>
                </v-data-table>
            </v-card>
        </v-row>
    </v-main>
</template>
<script>

export default {
    data() {
        return {
            serverInfo: [],
            headers: [
                {
                    text: "Popis",
                    align: "start",
                    value: "popis"
                },
                { text: "Data", value: "data" }
            ]
        };
    },

    created() {
        this.loadServerInfo();
    },
    methods: {
        loadServerInfo() {
            window.axios.get("server/satatus").then(response => {
                this.serverInfo = response.data;
            });
        }
    },

    mounted() {},
    watch: {}
};
</script>
