<template>
    <div>
        <div>
            <alert-component
                v-if="status != null"
                :status="status"
            ></alert-component>
        </div>
        <v-container>
            <v-card color="transparent" class="elevation-0 body-2">
                <v-card-title>
                    <v-text-field
                        autofocus
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Vyhledat uživatele ..."
                        single-line
                        hide-details
                    ></v-text-field>
                </v-card-title>
                <v-data-table
                    dense
                    :headers="usersHeader"
                    :items="users"
                    :search="search"
                >
                    <!-- ZOBRZAENÍ STAVŮ V TABULCE  -->
                    <template v-slot:item.mozaika="{ item }">
                        <span v-if="item.mozaika == 'default'">
                            <span>základní</span>
                        </span>
                        <span v-else>
                            <span>uživatelem nadefinována</span>
                        </span>
                    </template>

                    <template v-slot:item.status="{ item }">
                        <span v-if="item.status == 'active'">
                            <strong>
                                <span class="green--text">Aktivní</span>
                            </strong>
                        </span>
                        <span v-if="item.status == 'blocket'">
                            <strong>
                                <span class="red--text">Blokován</span>
                            </strong>
                        </span>
                    </template>

                    <!-- AKCE U JEDNOTLIVÝC STREAMŮ -->
                    <template v-slot:item.akce="{ item }">
                        <!-- edit -->
                        <v-icon @click="" small class="mr-2">mdi-pencil</v-icon>

                        <!-- delete -->
                        <v-icon @click="" small color="red">mdi-delete</v-icon>
                    </template>
                </v-data-table>
            </v-card>
        </v-container>

        <!-- dialogs -->
    </div>
</template>

<script>
import AlertComponent from "../AlertComponent";
export default {
    data() {
        return {
            status: null,

            search: "",
            users: [],
            usersHeader: [
                {
                    text: "Název",
                    align: "start",
                    value: "name"
                },
                { text: "E-mail", value: "email" },
                { text: "Role", value: "role_id" },
                { text: "Typ mozaiky", value: "mozaika" },
                { text: "Streamy", value: "customData" },
                { text: "Počet kanálů v mozaice", value: "pagination" },
                { text: "Status", value: "status" },
                { text: "Akce", value: "akce" }
            ]
        };
    },
    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.loadUsers();
    },
    methods: {
        loadUsers() {
            // get req
            window.axios.get("users").then(response => {
                this.users = response.data;
            });
        }
    }
};
</script>
