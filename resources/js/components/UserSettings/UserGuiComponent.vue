<template>
    <v-main>
        <alert-component
            v-if="status != null"
            :status="status"
        ></alert-component>
        <v-container fluid>
            <v-alert type="info" text width="80%">
                Nastavení prostředí
            </v-alert>

            <v-card
                flat
                class="text-center transition-fast-in-fast-out"
                width="80%"
            >
                <v-card-text>
                    <span>
                        Nastavení počtu zobrazených kanálů na stránce v mozaice
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
                    <v-row>
                        <v-spacer></v-spacer>
                        <v-btn
                            @click="GuiEdit()"
                            small
                            class="mr-12"
                            color="success"
                        >
                            Editovat
                        </v-btn>
                    </v-row>
                </v-card-actions>
            </v-card>
        </v-container>
    </v-main>
</template>
<script>
import AlertComponent from "../AlertComponent";
export default {
    props: ["user"],
    data: () => ({
        staticChannels: [],
        status: null,
        customMozaika: "",
        streams: []
    }),
    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.CustomMozaikaFn();
        this.loadChannels();
    },

    methods: {
        loadChannels() {
            let currentObj = this;
            axios.get("/user/streams").then(function(response) {
                currentObj.streams = response.data;
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
            let currentObj = this;
            axios
                .post("user/gui/edit", {
                    pagination: this.user.pagination,
                    customMozaika: this.customMozaika,
                    staticChannels: this.staticChannels
                })
                .then(function(response) {
                    currentObj.status = response.data.status;
                    currentObj.$store.commit("update", response.data.data);

                    setTimeout(function() {
                        currentObj.status = null;
                    }, 5000);
                });
        },
        getStaticChannels() {
            user.staticChannels = this.staticChannels;
        }
    },
    mounted() {
        this.CustomMozaikaFn();
    }

    // mutations: {
    //     status() {
    //         setTimeout(function() {
    //             this.status = null;
    //         }, 5000);
    //     }
    // }
};
</script>
