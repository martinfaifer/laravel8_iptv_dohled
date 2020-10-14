<template>
    <v-main>
        <v-snackbar
            v-if="status != null"
            rounded="pill"
            transition="scale-transition"
            dense
            top
            color="warning"
            v-model="status"
        >
            <div class="text-center">
                <span class="subtitle-1"> {{ status.msg }} </span>
            </div>
            <template v-slot:action="{ attrs }">
                <v-btn color="blue" text v-bind="attrs"> </v-btn>
            </template>
        </v-snackbar>
        <v-container class="fill-height" fluid>
            <v-row align="center" justify="center">
                <v-col cols="12" sm="8" md="4">
                    <v-card class="elevation-12">
                        <v-card-text>
                            <h1 class="text-center mb-4 mt-4">
                                <v-icon color="error" large>
                                    mdi-television-play
                                </v-icon>
                                <strong>TS Analyzator</strong>
                            </h1>
                            <v-form>
                                <v-text-field
                                    v-model="email"
                                    :rules="mailRule"
                                    label="email"
                                    name="login"
                                    prepend-icon="mdi-account"
                                    type="text"
                                ></v-text-field>

                                <v-text-field
                                    v-model="password"
                                    :rules="passwordRule"
                                    label="heslo"
                                    name="password"
                                    prepend-icon="mdi-lock"
                                    type="password"
                                ></v-text-field>
                            </v-form>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                @click="login()"
                                :disabled="password === null || email === null"
                                type="submit"
                                color="success"
                                >Přihlášení</v-btn
                            >
                        </v-card-actions>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </v-main>
</template>

<script>
export default {
    data() {
        return {
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
            let currentObj = this;
            axios
                .post("login", {
                    email: this.email,
                    password: this.password
                })
                .then(function(response) {
                    console.log(response.data);
                    if (response.data.status === "success") {
                        currentObj.$router.push("/");
                    } else {
                        currentObj.status = response.data;
                    }
                });
        },

        loadUser() {
            let currentObj = this;
            window.axios.get("user").then(response => {
                if (response.data.status == "error") {
                    // currentObj.$router.push("/login");
                    // currentObj.status = response.data;
                } else {
                    currentObj.$router.push("/");
                }
            });
        }
    },
    watch: {}
};
</script>
