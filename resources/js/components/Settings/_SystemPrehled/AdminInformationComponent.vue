<template>
    <v-main>
        <v-row>
            <!-- tabulka se serverovým nastavením -->
            <v-card v-if="system != null" flat color="transparent elevation-0">
                <div class="text-center">
                    <span class="body-2"> Přehled procesů </span>
                </div>
                <v-data-table
                    :headers="headerSystem"
                    :items="system"
                    flat
                    dense
                    class="elevation-0 mt-2"
                >
                </v-data-table>
            </v-card>
        </v-row>
    </v-main>
</template>
<script>
export default {
    data() {
        return {
            system: null,
            headerSystem: [
                {
                    text: "Proces",
                    align: "start",
                    value: "process_name"
                },
                {
                    text: "Pid",
                    value: "pid"
                }
            ]
        };
    },

    created() {
        this.getSystemAdminInfo();
    },
    methods: {
        getSystemAdminInfo() {
            window.axios.get("admin/system/info").then(response => {
                this.system = response.data;
            });
        }
    },

    mounted() {
        setInterval(
            function() {
                this.loadHistory();
            }.bind(this),
            10000
        );
    }
};
</script>
