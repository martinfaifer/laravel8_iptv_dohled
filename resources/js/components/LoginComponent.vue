<template>
    <v-main>
        <v-snackbar v-if="status != null" dense top color="success" v-model="status">
            <div class="body-1 text-center">
                Nějaký text
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
                            <v-btn @click="login()" :disabled="password === '' || email === ''" type="submit" color="success"
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
    props: {
        source: String
    },
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
        // let currentObj = this;
        // axios.get("/api/user/get").then(function(response) {
        //     if (response.data.stat === "error") {
        //         currentObj.status = response.data;
        //     } else {
        //         currentObj.$router.push("/");
        //     }
        // });
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
                    currentObj.status = response.data;
                    if (currentObj.status.status === "success") {
                        currentObj.$router.push("/");
                    }
                })
                .catch(function(error) {
                    currentObj.chybaOdpovediServeru = true;
                });
        }
    },
    watch: {
        status: function() {
            setTimeout(() => (this.status = false), 3000);
        }
    }
};
</script>
