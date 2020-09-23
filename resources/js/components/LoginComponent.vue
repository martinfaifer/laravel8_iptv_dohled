<template>
    <v-main>
        <v-snackbar top color="success" multi-line v-model="status">
            <p class="subtitle-2 text-center">
                <strong>Nějaký text</strong>
            </p>
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
                                <strong>IPTV Dohled</strong>
                            </h1>
                            <v-form>
                                <v-text-field
                                    label="email"
                                    name="login"
                                    prepend-icon="mdi-account"
                                    type="text"
                                ></v-text-field>

                                <v-text-field
                                    label="heslo"
                                    name="password"
                                    prepend-icon="mdi-lock"
                                    type="password"
                                ></v-text-field>
                            </v-form>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn type="submit" color="success"
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
            status: true
        };
    }
};
</script>
