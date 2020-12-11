<template>
    <v-main>
        <alert-component
            v-if="status != null"
            :status="status"
        ></alert-component>
        <!-- container pro nastavení -->
        <v-container fluid>
            <!-- Obecne nastavení -->
            <v-row>
                <v-alert type="info" text width="80%">
                    Nastavení prostředí
                </v-alert>

                <v-row>
                    <v-hover v-slot:default="{ hover }">
                        <v-card
                            :elevation="hover ? 1 : 0"
                            flat
                            class="text-center transition-fast-in-fast-out"
                        >
                            <v-card-text>
                                <span>
                                    <strong>
                                        Obecné nastavení
                                    </strong>
                                </span>
                                <v-container>
                                    <!-- obsah -->
                                    <v-row>
                                        <v-col cols="1" md="12">
                                            <v-text-field
                                                dense
                                                v-model="user.name"
                                                label="Jméno"
                                                required
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="1" md="12">
                                            <v-text-field
                                                dense
                                                v-model="user.email"
                                                label="email"
                                                required
                                            ></v-text-field>
                                        </v-col>
                                    </v-row>
                                    <v-card-actions>
                                        <v-row>
                                            <v-spacer></v-spacer>
                                            <v-btn
                                                @click="ObecneNastaveniEdit()"
                                                :disabled="
                                                    user.name == '' ||
                                                        user.email == ''
                                                "
                                                type="submit"
                                                small
                                                color="success"
                                                >Editovat</v-btn
                                            >
                                        </v-row>
                                    </v-card-actions>
                                </v-container>
                            </v-card-text>
                        </v-card>
                    </v-hover>
                </v-row>
                <!-- Změna hesla -->
                <v-row>
                    <v-hover v-slot:default="{ hover }">
                        <v-card
                            :elevation="hover ? 1 : 0"
                            flat
                            class="text-center transition-fast-in-fast-out"
                        >
                            <v-card-text>
                                <span>
                                    <strong>
                                        Změna hesla
                                    </strong>
                                </span>
                                <v-container>
                                    <!-- obsah -->
                                    <v-row>
                                        <v-col cols="1" md="12">
                                            <v-text-field
                                                dense
                                                type="password"
                                                v-model="password"
                                                label="Heslo"
                                                required
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="1" md="12">
                                            <v-text-field
                                                dense
                                                type="password"
                                                v-model="passwordCheck"
                                                label="Ověření hesla"
                                                required
                                            ></v-text-field>
                                        </v-col>
                                    </v-row>
                                    <v-card-actions>
                                        <v-row>
                                            <v-spacer></v-spacer>
                                            <v-btn
                                                @click="ZmenaHesla()"
                                                :disabled="
                                                    password != passwordCheck
                                                "
                                                type="submit"
                                                small
                                                color="success"
                                                >Editovat</v-btn
                                            >
                                        </v-row>
                                    </v-card-actions>
                                </v-container>
                            </v-card-text>
                        </v-card>
                    </v-hover>
                </v-row>
            </v-row>

            <!-- Detailní informace -->
        </v-container>
    </v-main>
</template>
<script>
import AlertComponent from "../AlertComponent";
export default {
    props: ["user"],
    data: () => ({
        avatar: null,
        password: null,
        passwordCheck: null,
        status: null,
        rules: [
            value =>
                !value ||
                value.size < 2000000 ||
                "Velikost musí být menší než 2 MB!"
        ]
    }),
    components: {
        "alert-component": AlertComponent
    },
    methods: {
        ObecneNastaveniEdit() {
            window.axios
                .post("user/obecne/edit", {
                    name: this.user.name,
                    email: this.user.email
                })
                .then(response => {
                    this.status = response.data;
                    setTimeout(function() {
                        this.status = null;
                    }, 5000);
                });
        },

        ZmenaHesla() {
            window.axios
                .post("user/heslo/edit", {
                    password: this.password,
                    passwordCheck: this.passwordCheck
                })
                .then(response => {
                    this.status = response.data;
                    setTimeout(function() {
                        this.status = null;
                    }, 5000);
                });
        },

        DetailEdit() {
            window.axios
                .post("user/detail/edit", {
                    company: this.user.userDetail.company,
                    tel_number: this.user.userDetail.tel_number,
                    nickname: this.user.userDetail.nickname
                })
                .then(response => {
                    this.status = response.data;
                    setTimeout(function() {
                        this.status = null;
                    }, 5000);
                });
        }
    }
    // watch: {
    //     status() {
    //         if (this.status != null) {
    //             setTimeout(function() {
    //                 this.status = null;
    //             }, 5000);
    //         }
    //     }
    // }
};
</script>
