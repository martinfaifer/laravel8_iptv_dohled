<template>
    <v-main class="mt-12">
        <v-container>
            <aside>
                <usersidebar-component :user="user"></usersidebar-component>
            </aside>
        </v-container>
        <v-row class="justify-center mt-6">
            <v-toolbar color="transparent" fixed class="mb-6" dense flat>
                <v-spacer></v-spacer>

                <v-btn link to="/user/prehled" icon>
                    <v-icon :color="prehledColor">
                        mdi-view-dashboard
                    </v-icon>
                </v-btn>
                <v-btn link to="/user/editace" icon>
                    <v-icon :color="editaceColor"
                        >mdi-account-cog-outline</v-icon
                    >
                </v-btn>
                <v-btn link to="/user/gui" icon>
                    <v-icon :color="guiColor">mdi-television-guide</v-icon>
                </v-btn>
                <v-btn link to="/user/alert" icon>
                    <v-icon :color="alertColor">mdi-message-alert</v-icon>
                </v-btn>
                <v-spacer></v-spacer>
            </v-toolbar>
        </v-row>
        <v-container class="rightFromSIdePanel">
            <div>
                <v-row>
                    <userdashboard-component
                        v-if="prehled == true"
                        :user="user"
                    ></userdashboard-component>
                    <usersettings-component v-if="editace == true" :user="user">
                    </usersettings-component>
                    <usergui-component
                        v-if="gui == true"
                        :user="user"
                    ></usergui-component>
                </v-row>
                <!-- <v-row class="justify-center body-2">
                    <v-col cols="1" md="2">
                        <v-text-field
                            dense
                            v-model="user.name"
                            label="Jméno"
                            required
                        ></v-text-field>
                    </v-col>

                    <v-col cols="1" md="2">
                        <v-text-field
                            dense
                            v-model="user.email"
                            label="E-mail"
                            required
                        ></v-text-field>
                    </v-col>
                    <v-col cols="1" class="mt-2">
                        <v-btn
                            dense
                            :disabled="user.name == '' || user.email == ''"
                            small
                            type="submit"
                            color="success"
                            >editovat</v-btn
                        >
                    </v-col>
                </v-row> -->

                <!-- <v-row class="justify-center body-2">
                    <v-col cols="1" md="2">
                        <v-text-field
                            dense
                            type="password"
                            v-model="password"
                            label="Heslo"
                            required
                        ></v-text-field>
                    </v-col>
                    <v-col cols="1" md="2">
                        <v-text-field
                            dense
                            type="password"
                            v-model="passwordCheck"
                            label="Ověření hesla"
                            required
                        ></v-text-field>
                    </v-col>

                    <v-col cols="1" class="mt-2">
                        <v-btn
                            dense
                            :disabled="
                                password != passwordCheck ||
                                    password == '' ||
                                    passwordCheck == ''
                            "
                            small
                            type="submit"
                            color="success"
                            >Změnit heslo</v-btn
                        >
                    </v-col>
                </v-row> -->

                <!-- <v-row class="justify-center body-2 mr-5">
                    <span class="mt-6">
                        počet zobrazených náhledů v mozaice:
                    </span>
                    <v-col cols="1" md="2">
                        <v-text-field
                            dense
                            v-model="user.pagination"
                            type="number"
                            autofocus
                        ></v-text-field>
                    </v-col>
                    <v-col cols="1" class="mt-2">
                        <v-btn x-small type="submit" color="success"
                            >Změnit počet</v-btn
                        >
                    </v-col>
                </v-row> -->

                <!-- <v-row class="justify-center body-2 mr-12">
                    <v-switch
                        dense
                        v-model="staticChannels"
                        label="Nastavení statických kanálů"
                    ></v-switch>
                </v-row>
                <v-row v-if="staticChannels == true" class="justify-center">
                    <v-col cols="1" md="3">
                        <v-autocomplete
                            v-model="staticChannelsData"
                            :items="streams"
                            item-text="nazev"
                            item-value="id"
                            label="Vyberte kanály"
                            multiple
                            persistent-hint
                            small-chips
                        ></v-autocomplete>
                    </v-col>
                    <v-btn
                        class="mt-10"
                        @click="setStaticStreams()"
                        x-small
                        type="submit"
                        color="success"
                    >
                        Nastavit
                    </v-btn>
                </v-row> -->
            </div>
        </v-container>
    </v-main>
</template>
<script>
import UserSideNavBarInfoComponent from "./UserSideBarInfoComponent";
import UserDashboardComponent from "./UserDashboardComponent";
import UserSettingsComponent from "./UserSettingsComponent";
import UserGuiComponent from "./UserGuiComponent";
export default {
    data: () => ({
        userId: null,
        user: [],
        password: "",
        passwordCheck: "",
        staticChannels: null,
        staticChannelsData: [],
        streams: [],
        status: "",
        colorIconGui: "white",
        prehled: false,
        prehledColor: "white",

        editace: false,
        editaceColor: "white",

        gui: false,
        guiColor: "white",

        alert: false,
        alertColor: "white"
    }),
    created() {
        this.loaduserData();
        this.checkRouteStatus();
    },

    components: {
        "usersidebar-component": UserSideNavBarInfoComponent,
        "userdashboard-component": UserDashboardComponent,
        "usersettings-component": UserSettingsComponent,
        "usergui-component": UserGuiComponent
    },

    methods: {
        checkRouteStatus() {
            if (this.$route.params.id == "prehled") {
                this.prehled = true;
                this.prehledColor = "green";

                this.editace = false;
                this.editaceColor = "white";

                this.gui = false;
                this.guiColor = "white";

                this.alert = false;
                this.alertColor = "white";
            }

            if (this.$route.params.id == "editace") {
                this.editace = true;
                this.editaceColor = "green";

                this.prehled = false;
                this.prehledColor = "white";

                this.gui = false;
                this.guiColor = "white";

                this.alert = false;
                this.alertColor = "white";
            }

            if (this.$route.params.id == "gui") {
                this.gui = true;
                this.guiColor = "green";

                this.editace = false;
                this.editaceColor = "white";

                this.prehled = false;
                this.prehledColor = "white";

                this.alert = false;
                this.alertColor = "white";
            }

            if (this.$route.params.id == "alert") {
                this.alert = true;
                this.alertColor = "green";

                this.editace = false;
                this.editaceColor = "white";

                this.prehled = false;
                this.prehledColor = "white";

                this.gui = false;
                this.guiColor = "white";
            }
        },
        loaduserData() {
            let currentObj = this;
            window.axios.get("user/detail").then(response => {
                currentObj.user = response.data;
                currentObj.userId = response.data.id;

                currentObj.connectToWebsocket();

                this.loadStreams();
                if (response.data.mozaika == "default") {
                    currentObj.staticChannels = false;
                } else {
                    currentObj.staticChannels = true;
                }
            });
        },

        connectToWebsocket() {
            Echo.channel("UserEdit" + this.userId).listen("UserEdit", e => {
                if (e[0].length > 0) {
                    this.user = e[0];
                }
            });
        },

        loadStreams() {
            let currentObj = this;
            window.axios.get("user/streams").then(response => {
                currentObj.streams = response.data;
            });
        },

        setStaticStreams() {
            let currentObj = this;

            window.axios
                .post("user/streams/set", {
                    staticChannelsData: this.staticChannelsData
                })
                .then(function(response) {
                    console.log(response.data);
                    currentObj.status = response.data;
                });
        }
    },
    watch: {
        $route() {
            if (this.$route.params.id == "prehled") {
                this.checkRouteStatus();
            }
        },
        $route(to, from) {
            this.loaduserData();
            this.checkRouteStatus();
        }
    },

    mounted() {
        this.connectToWebsocket();
    }
};
</script>
