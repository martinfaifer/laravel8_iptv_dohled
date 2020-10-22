<template>
    <v-main>
        <alert-component
            v-if="status != null"
            :status="status"
        ></alert-component>
        <v-row>
            <span class="headline">Nastavení prostředí</span>
        </v-row>
        <v-spacer></v-spacer>
        <br />
        <v-container fluid>
            <v-row>
                <v-card
                    flat
                    color="transparent"
                    class="text-center pl-10 pr-10 transition-fast-in-fast-out ml-12"
                >
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
                </v-card>
            </v-row>
            <v-row class="mt-3">
                <v-card
                    flat
                    color="transparent"
                    class="text-center pl-10 pr-10 transition-fast-in-fast-out ml-12"
                >
                    <v-col cols="3" sm="12" md="12">
                        <v-switch
                            v-model="customMozaika"
                            label="vytvoření statických kanálů v mozaice"
                        ></v-switch>
                    </v-col>
                </v-card>
            </v-row>
            <v-row v-show="customMozaika === true">
                <v-card
                    flat
                    color="transparent"
                    class="text-center pl-10 pr-10 transition-fast-in-fast-out ml-12"
                >
                    <v-col cols="4" sm="12" md="12">
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
                </v-card>
            </v-row>
            <v-row>
                <v-spacer></v-spacer>
                <v-row>
                    <v-btn
                        @click="GuiEdit()"
                        small
                        class="mr-12"
                        color="success"
                    >
                        Editovat
                    </v-btn>
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
