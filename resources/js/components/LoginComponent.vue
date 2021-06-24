<template>
    <v-main>
        <transition name="slide-fade" mode="out-in">
            <v-snackbar
                v-if="status != null"
                :timeout="5000"
                border="left"
                class="pt-12"
                transition="slide-x-transition"
                top
                right
                dark
                elevation-12
                :color="status.status"
                v-model="status"
            >
                <v-row class="ml-1">
                    <v-icon>
                        mdi-close
                    </v-icon>

                    <span
                        class="text--secondary ml-12 body-1"
                        v-html="status.msg"
                    ></span>
                </v-row>
            </v-snackbar>
        </transition>
        <v-container class="fill-height" fluid>
            <v-row align="center" justify="center">
                <v-col cols="12" sm="12" md="6" lg="6">
                    <v-form @submit.prevent="login()">
                        <v-card class="elevation-12">
                            <v-card-text>
                                <h1 class="text-center mb-4 mt-4">
                                    <v-icon color="error" large>
                                        mdi-television-play
                                    </v-icon>
                                    <strong>IPTV Dohled</strong>
                                </h1>
                                <v-col cols="12" sm="12" md="12" lg="12">
                                    <v-text-field
                                        v-model="email"
                                        :rules="mailRule"
                                        label="email"
                                        name="login"
                                        prepend-inner-icon="mdi-account"
                                        type="text"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" md="12" lg="12">
                                    <v-text-field
                                        v-model="password"
                                        :rules="passwordRule"
                                        label="heslo"
                                        name="password"
                                        prepend-inner-icon="mdi-lock"
                                        type="password"
                                    ></v-text-field>
                                </v-col>
                            </v-card-text>
                            <v-card-actions class="pb-3 mr-3">
                                <v-spacer></v-spacer>

                                <v-btn
                                    :loading="isLoading"
                                    outlined
                                    text
                                    type="submit"
                                    color="success"
                                    >Přihlášení</v-btn
                                >
                            </v-card-actions>
                        </v-card>
                    </v-form>
                </v-col>
            </v-row>
        </v-container>
    </v-main>
</template>

<script>
export default {
    data() {
        return {
            isLoading: false,
            status: true,
            email: null,
            password: null,
            status: null,

            mailRule: [v => !!v || "chybí e-mailová adresa"],
            passwordRule: [v => !!v || "chybí heslo"]
        };
    },

    created() {
        this.loadUser();
    },
    methods: {
        login() {
            this.isLoading = true;
            axios
                .post("login", {
                    email: this.email,
                    password: this.password
                })
                .then(response => {
                    this.isLoading = false;
                    if (response.data.status === "success") {
                        this.$router.push("/");
                    } else {
                        this.status = response.data.alert;
                    }

                    setTimeout(() => {
                        this.status = null;
                    }, 5000);
                });
        },

        loadUser() {
            axios.get("user").then(response => {
                if (response.data.status == "error") {
                    // currentObj.$router.push("/login");
                    // currentObj.status = response.data;
                } else {
                    this.$router.push("/");
                }
            });
        }
    },
    watch: {}
};
</script>
