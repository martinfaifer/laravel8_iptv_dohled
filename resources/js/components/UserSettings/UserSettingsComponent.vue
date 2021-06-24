<template>
    <v-main class="ml-12">
        <v-container>
            <v-col cols="12" sm="12" lg="12" md="12">
                <v-alert type="info" text outlined>
                    Nastavení prostředí
                </v-alert>
            </v-col>

            <v-row class="ml-1 mr-1">
                <v-col cols="12" sm="12" md="6" lg="6">
                    <v-card flat class="text-center">
                        <v-card-text>
                            <span>
                                <strong class="white--text">
                                    Obecné nastavení
                                </strong>
                            </span>
                            <v-container>
                                <v-row>
                                    <v-col cols="12" sm="12" md="12" lg="12">
                                        <v-text-field
                                            dense
                                            v-model="user.name"
                                            label="Jméno"
                                            required
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="12" md="12" lg="12">
                                        <v-text-field
                                            dense
                                            v-model="user.email"
                                            label="email"
                                            required
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn
                                        outlined
                                        text
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
                            </v-container>
                        </v-card-text>
                    </v-card>
                </v-col>

                <v-col cols="12" sm="12" lg="6" md="6">
                    <v-card flat class="text-center">
                        <v-card-text>
                            <span>
                                <strong class="white--text">
                                    Změna hesla
                                </strong>
                            </span>
                            <v-container>
                                <!-- obsah -->
                                <v-row>
                                    <v-col cols="12" sm="12" md="12" lg="12">
                                        <v-text-field
                                            dense
                                            type="password"
                                            v-model="password"
                                            label="Heslo"
                                            required
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="12" md="12" lg="12">
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
                                    <v-spacer></v-spacer>
                                    <v-btn
                                        outlined
                                        text
                                        @click="ZmenaHesla()"
                                        :disabled="password != passwordCheck"
                                        type="submit"
                                        small
                                        color="success"
                                        >Editovat</v-btn
                                    >
                                </v-card-actions>
                            </v-container>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Detailní informace -->
        </v-container>
    </v-main>
</template>
<script>
export default {
    props: ["user"],
    data: () => ({
        avatar: null,
        password: null,
        passwordCheck: null,
        status: null,
    }),
    methods: {
        ObecneNastaveniEdit() {
            axios
                .post("user/obecne/edit", {
                    name: this.user.name,
                    email: this.user.email
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                });
        },

        ZmenaHesla() {
            axios
                .post("user/heslo/edit", {
                    password: this.password,
                    passwordCheck: this.passwordCheck
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                });
        },

        DetailEdit() {
            window.axios
                .post("user/detail/edit", {
                    company: this.user.userDetail.company,
                    nickname: this.user.userDetail.nickname
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                });
        }
    }
    
};
</script>
