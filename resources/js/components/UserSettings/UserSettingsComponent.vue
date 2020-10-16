<template>
    <v-main>
        <alert-component :status="status"></alert-component>
        <v-row>
            <span class="headline">Nastavení</span>
        </v-row>
        <v-spacer></v-spacer>
        <br />

        <!-- container pro nastavení -->
        <v-container>
            <!-- Obecne nastavení -->
            <v-row>
                <v-row>
                    <v-card
                        flat
                        color="grey darken-4
"
                        class="text-center"
                    >
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
                                <v-card-actions>
                                    <v-btn
                                        @click="ObecneNastaveniEdit()"
                                        :disabled="
                                            user.name == '' || user.email == ''
                                        "
                                        type="submit"
                                        small
                                        color="success"
                                        >Editovat</v-btn
                                    >
                                </v-card-actions>
                            </v-row>
                        </v-container>
                    </v-card>
                </v-row>
                <!-- Změna hesla -->
                <v-row>
                    <v-card
                        flat
                        color="grey darken-4
"
                        class="text-center"
                    >
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
                                <v-btn
                                    @click="ZmenaHesla()"
                                    :disabled="password != passwordCheck"
                                    type="submit"
                                    small
                                    color="success"
                                    >Editovat</v-btn
                                >
                            </v-card-actions>
                        </v-container>
                    </v-card>
                </v-row>
            </v-row>

            <!-- Detailní informace -->
            <br />
            <v-row>
                <v-row>
                    <v-card
                        flat
                        color="grey darken-4
"
                        class="text-center"
                    >
                        <span>
                            <strong>
                                Detailní nastavení
                            </strong>
                        </span>
                        <v-container>
                            <!-- obsah -->
                            <v-row>
                                <v-col cols="1" md="12">
                                    <v-text-field
                                        dense
                                        v-model="user.userDetail.company"
                                        label="Společnost"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="1" md="12">
                                    <v-text-field
                                        dense
                                        v-model="user.userDetail.tel_number"
                                        label="Telefonní kontakt"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="1" md="12">
                                    <v-text-field
                                        dense
                                        v-model="user.userDetail.nickname"
                                        label="Přezdívka"
                                    ></v-text-field>
                                </v-col>
                                <v-card-actions>
                                    <v-btn @click="DetailEdit()" type="submit" small color="success"
                                        >Editovat</v-btn
                                    >
                                </v-card-actions>
                            </v-row>
                        </v-container>
                    </v-card>
                </v-row>
            </v-row>
        </v-container>
    </v-main>
</template>
<script>
import AlertComponent from "../AlertComponent";
export default {
    props: ["user"],
    data: () => ({
        password: null,
        passwordCheck: null,
        status: null
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
                });
        },

        ZmenaHesla() {
            window.axios
                .post("user/heslo/edit", {
                    password: this.upassword,
                    passwordCheck: this.passwordCheck
                })
                .then(response => {
                    this.status = response.data;
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
                });
        }
    },
    watch: {
        status() {
            if (this.status != null) {
                setTimeout(function() {
                    this.status = null;
                }, 5000);
            }
        }
    }
};
</script>
