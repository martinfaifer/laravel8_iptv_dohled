<template>
    <v-main>
        <v-container fluid>
            <v-card color="transparent" class="elevation-0 body-2" flat>
                <div class="text-center">
                    <span class="body-1 white--text">
                        <strong>
                            Chybové záznamy ze systému
                        </strong>
                    </span>
                </div>
                <v-data-table
                    class="mt-3"
                    style="background-color: transparent"
                    background-color="transparent"
                    :headers="header"
                    :items-per-page="items"
                    :items="logs"
                >
                </v-data-table>
            </v-card>
        </v-container>
    </v-main>
</template>

<script>
export default {
    data() {
        return {
            items: 2,
            logs: [],
            header: [
                { text: "Component", value: "component" },
                { text: "Payload", value: "payload" }
            ]
        };
    },
    created() {
        this.loadLogs();
    },
    methods: {
        loadLogs() {
            axios.get("system/logs").then(response => {
                this.logs = response.data;
            });
        }
    }
};
</script>
