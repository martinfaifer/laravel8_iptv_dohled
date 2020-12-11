<template>
    <div>
        <div>
            <alert-component
                v-if="status != null"
                :status="status"
            ></alert-component>
        </div>
        <v-container fluid>
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
                    <v-spacer></v-spacer>
                    <v-btn
                        :loading="loadingCreateBtn"
                        @click="OpenCreateDialog()"
                        small
                        color="success"
                    >
                        <v-icon left dark>
                            mdi-plus
                        </v-icon>
                        Přidat
                    </v-btn>
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
                        <v-icon
                            @click="
                                openEditDialog(),
                                    (userId = item.id),
                                    (jmeno = item.name),
                                    (email = item.email)
                            "
                            small
                            class="mr-2"
                            >mdi-pencil</v-icon
                        >

                        <!-- delete -->
                        <v-icon @click="removeUser(item.id)" small color="red"
                            >mdi-delete</v-icon
                        >
                    </template>
                </v-data-table>
            </v-card>
        </v-container>

        <!-- dialogs -->
        <!-- create dialog -->
        <v-row justify="center">
            <v-dialog v-model="createDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title> </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field
                                        autofocus
                                        v-model="jmeno"
                                        label="Jméno"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field
                                        v-model="email"
                                        label="email"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" sm="6" md="6">
                                    <v-btn
                                        @click="generatePassword()"
                                        small
                                        color="primary"
                                        >Generovat Heslo</v-btn
                                    >
                                </v-col>
                            </v-row>
                            <v-row v-if="generatedPassword != null">
                                <v-col cols="12" sm="12" md="12">
                                    <v-text-field
                                        autofocus
                                        v-model="generatedPassword"
                                        label="Heslo"
                                        readonly
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row v-if="generatedPassword == null">
                                <v-col cols="12" sm="12" md="12">
                                    <v-text-field
                                        v-model="password"
                                        label="Heslo"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <!-- user role -->
                            <v-select
                                :items="userRoles"
                                v-model="role_id"
                                item-value="id"
                                item-text="role_name"
                                label="Uživatelská role"
                            ></v-select>
                            <!-- user role -->
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            @click="closeCreateDialog()"
                            color="red darken-1"
                            text
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            @click="createUser()"
                            color="green darken-1"
                            text
                        >
                            Vytvořit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
        <!-- end create dialog -->

        <!-- edit dialog -->
        <v-row justify="center">
            <v-dialog v-model="editDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title> </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field
                                        autofocus
                                        v-model="jmeno"
                                        label="Jméno"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field
                                        v-model="email"
                                        label="email"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <!-- user role -->
                            <v-select
                                :items="userRoles"
                                v-model="role_id"
                                item-value="id"
                                item-text="role_name"
                                label="Uživatelská role"
                            ></v-select>
                            <!-- user role -->

                            <!-- status -->
                            <v-row>
                                <v-switch
                                    v-model="userActiveStatus"
                                    label="blokovat uživatele"
                                >
                                </v-switch>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            @click="closeEditDialog()"
                            color="red darken-1"
                            text
                        >
                            Zavřít
                        </v-btn>
                        <v-btn @click="editUser()" color="green darken-1" text>
                            Upravit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
        <!-- end edit dialog -->
        <!-- end dialogs -->
    </div>
</template>

<script>
import AlertComponent from "../AlertComponent";
export default {
    data() {
        return {
            userActiveStatus: false,
            editDialog: false,
            userId: null,
            jmeno: null,
            email: null,
            password: null,
            role_id: null,
            userRoles: null,
            createDialog: false,
            loadingCreateBtn: false,
            status: null,
            generatedPassword: null,
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
        },
        OpenCreateDialog() {
            window.axios.get("user/roles").then(response => {
                this.userRoles = response.data;
                this.createDialog = true;
            });
        },
        createUser() {
            let currentObj = this;
            axios
                .post("user/create", {
                    jmeno: this.jmeno,
                    email: this.email,
                    password: this.password,
                    role_id: this.role_id,
                    generatedPassword: this.generatedPassword
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        currentObj.status = response.data;
                        currentObj.createDialog = false;
                        // currentObj.returnToDefalt();
                        currentObj.loadUsers();
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 5000);
                    } else {
                        currentObj.status = response.data;
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 5000);
                    }
                });
        },
        closeCreateDialog() {
            this.createDialog = false;

            // return to default all
        },
        // funkce na generování hesla z backengu
        generatePassword() {
            window.axios.get("user/password/generator").then(response => {
                this.generatedPassword = response.data;
            });
        },
        openEditDialog() {
            window.axios.get("user/roles").then(response => {
                this.userRoles = response.data;
                this.editDialog = true;
            });
        },
        closeEditDialog() {
            this.editDialog = false;
        },
        editUser() {
            let currentObj = this;
            axios
                .post("user/edit", {
                    userId: this.userId,
                    jmeno: this.jmeno,
                    email: this.email,
                    role_id: this.role_id,
                    status: this.userActiveStatus
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        currentObj.status = response.data;
                        currentObj.editDialog = false;
                        currentObj.loadUsers();
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 5000);
                    } else {
                        currentObj.status = response.data;
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 5000);
                    }
                });
        },
        removeUser(id) {
            let currentObj = this;
            axios
                .post("user/delete", {
                    userId: id
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        currentObj.status = response.data;
                        currentObj.loadUsers();
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 5000);
                    } else {
                        currentObj.status = response.data;
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 5000);
                    }
                });
        }
    }
};
</script>
