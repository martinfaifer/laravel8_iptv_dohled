<template>
    <v-main>
        <v-container fluid>
            <!-- Soupis e-mailů, na které se zasílají alerty -->
            <v-card color="transparent" class="elevation-0 body-2">
                <v-card-title> </v-card-title>

                <v-data-table
                    :loading="loadingTable"
                    :headers="header"
                    :items="cron"
                >
                    <template v-slot:item.stav="{ item }">
                        <span v-if="item.stav === 'on'">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else>
                            <v-icon color="red">mdi-close</v-icon>
                        </span>
                    </template>

                    <template v-slot:item.akce="{ item }">
                        <v-icon
                            @click="openDialog(item.id)"
                            small
                            color="info"
                            style="cursor: pointer"
                            >mdi-pencil</v-icon
                        >
                    </template>
                </v-data-table>
            </v-card>
        </v-container>

        <v-row justify="center">
            <v-dialog v-model="dialog" persistent max-width="800px">
                <v-card>
                    <v-card-title> </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" sm="12" md="12" lg="12">
                                <v-switch
                                    v-model="cron_data.stav"
                                    label="Stav modulu"
                                ></v-switch>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            @click="closeDialog()"
                            color="red darken-1"
                            text
                            outlined
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            :loading="submitLoading"
                            color="green darken-1"
                            @click="saveDialog()"
                            text
                            outlined
                        >
                            Upravit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </v-main>
</template>

<script>
export default {
    data() {
        return {
            submitLoading: false,
            dialog: false,
            loadingTable: true,
            cron_data: [],
            cron: [],
            header: [
                {
                    text: "Modul",
                    align: "start",
                    value: "modul"
                },
                { text: "Stav", value: "stav" },
                { text: "", value: "akce" }
            ]
        };
    },
    created() {
        this.loadCron();
    },
    methods: {
        loadCron() {
            axios.get("system/cron").then(response => {
                this.loadingTable = false;
                this.cron = response.data;
            });
        },
        openDialog(id) {
            axios.get("system/cron/" + id).then(response => {
                this.cron_data = response.data;
                this.dialog = true;
            });
        },
        closeDialog() {
            this.cron_data =  [];
            this.dialog = false;
        },

        saveDialog() {
            this.submitLoading = true;
            axios.patch("system/cron", {
                id: this.cron_data.id,
                stav: this.cron_data.stav
            }).then(response => {
                this.$store.state.alerts = response.data.alert;
                this.submitLoading = false;
                this.dialog = false;
                this.loadCron();
            })
        }
    }
};
</script>
