<template>
    <v-main class="ml-12">
        <v-container>
            <v-col cols="12" sm="12" lg="12" md="12">
                <v-alert type="info" outlined text>
                    Nastavení prostředí
                </v-alert>
            </v-col>
            <v-col cols="12" sm="12" md="12" lg="12">
                <v-card flat class="text-center">
                    <v-card-text>
                        <span class="white--text">
                            Nastavení počtu zobrazených kanálů na stránce v
                            mozaice
                        </span>
                        <v-col cols="4" sm="12" md="12">
                            <v-text-field
                                v-model="user.pagination"
                                type="number"
                                autofocus
                            ></v-text-field>
                        </v-col>
                        <v-col cols="3" sm="12" md="12">
                            <v-switch
                                v-model="customMozaika"
                                label="vytvoření statických kanálů v mozaice"
                            ></v-switch>
                        </v-col>
                        <v-col
                            cols="4"
                            sm="12"
                            md="12"
                            v-show="customMozaika === true"
                        >
                            <v-autocomplete
                                v-model="staticChannels"
                                :items="streams"
                                item-text="nazev"
                                item-value="id"
                                label="Vyberte kanály"
                                hint="Doporučení 7 kanálu pro 1080p a 14 kanálů pro 4K rozlišení"
                                multiple
                            ></v-autocomplete>
                        </v-col>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            @click="GuiEdit()"
                            small
                            text
                            outlined
                            class="mr-12"
                            color="success"
                        >
                            Editovat
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-container>
    </v-main>
</template>
<script>
export default {
    props: ["user"],
    data: () => ({
        staticChannels: [],
        status: null,
        customMozaika: "",
        streams: []
    }),

    created() {
        this.CustomMozaikaFn();
        this.loadChannels();
    },

    methods: {
        loadChannels() {
            axios.get("/user/streams").then(response => {
                this.streams = response.data;
            });
        },
        CustomMozaikaFn() {
            if (this.user.mozaika === "custom") {
                this.customMozaika = true;
            } else {
                this.customMozaika = false;
            }
        },
        GuiEdit() {
            axios
                .post("user/gui/edit", {
                    pagination: this.user.pagination,
                    customMozaika: this.customMozaika,
                    staticChannels: this.staticChannels
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.$store.commit("update", response.data.data);
                });
        },
        getStaticChannels() {
            user.staticChannels = this.staticChannels;
        }
    },
    mounted() {
        this.CustomMozaikaFn();
    }
};
</script>
