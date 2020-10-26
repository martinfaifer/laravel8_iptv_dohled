<template>
    <div>
        <div>
            <alert-component
                v-if="status != null"
                :status="status"
            ></alert-component>
        </div>
        <v-container>
            <!-- Soupis e-mailů, na které se zasílají alerty -->

            <v-card color="transparent" class="elevation-0 body-2">
                <v-card-title>
                    <v-text-field
                        dense
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Hledat ..."
                        single-line
                        hide-details
                    ></v-text-field>
                </v-card-title>
                <v-data-table
                    dense
                    :headers="header"
                    :items="emails"
                    :search="search"
                >
                    <template v-slot:item.Akce="{ item }">
                        <!-- edit -->
                        <v-icon
                            @click="openEditDialog((emailId = item.id))"
                            small
                            color="green"
                            class="mr-2"
                            >mdi-pencil</v-icon
                        >
                        <!-- delete -->
                        <v-icon
                            @click="openDeleteDialog((emailId = item.id))"
                            small
                            color="red"
                            >mdi-delete</v-icon
                        >
                    </template>
                </v-data-table>
            </v-card>
        </v-container>

        <!-- dialogs -->

        <v-row justify="center">
            <v-dialog v-model="deleteDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline text-center"
                            >Odebrat E-mailovou adresu?</span
                        >
                    </v-card-title>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="red darken-1"
                            text
                            @click="closeDeleteDialog()"
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            color="green darken-1"
                            text
                            @click="sendDelete()"
                        >
                            Smazat
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
        <!-- end dialogs -->
    </div>
</template>

<script>
import AlertComponent from "../AlertComponent";
export default {
    data() {
        return {
            emailId: null,
            deleteDialog: false,
            emails: [],
            search: "",
            status: null,
            header: [
                {
                    text: "E-mail",
                    align: "start",
                    value: "email"
                },

                { text: "Zasílat alerty kanálů", value: "channels" },
                { text: "Zasílat systémové alerty", value: "system" },
                { text: "Akce", value: "Akce" }
            ]
        };
    },
    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.loadEmails();
    },
    methods: {
        loadEmails() {
            window.axios.get("notifications/mails").then(response => {
                this.emails = response.data;
            });
        }
    }
};
</script>
