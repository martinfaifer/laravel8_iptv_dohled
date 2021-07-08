<template>
    <v-main class="mt-12">
        <v-row>
            <v-col cols="12" sm="2" md="2" lg="2">
                <aside>
                    <usersidebar-component :user="user"></usersidebar-component>
                </aside>
            </v-col>
            <v-col cols="12" sm="9" md="9" lg="9">
                <v-row class="justify-center mt-6">
                    <v-toolbar
                        color="transparent"
                        fixed
                        class="mb-6"
                        dense
                        flat
                    >
                        <v-tabs
                            background-color="transparent"
                            centered
                            dense
                            dark
                            color="teal"
                        >
                            <v-tabs-slider color="teal"></v-tabs-slider>
                            <v-tab link to="/user/prehled" href="#prehled">
                                <v-icon>mdi-view-dashboard</v-icon>
                            </v-tab>

                            <v-tab link to="/user/editace" href="#editace">
                                <v-icon>mdi-account-cog-outline</v-icon>
                            </v-tab>

                            <v-tab
                                link
                                to="/user/gui"
                                href="#nastavení mozaiky"
                            >
                                <v-icon>mdi-television-guide</v-icon>
                            </v-tab>

                            <v-tab link to="/user/alert" href="#notifikace">
                                <v-icon>mdi-message-alert</v-icon>
                            </v-tab>
                        </v-tabs>
                        <v-spacer></v-spacer>

                        <v-spacer></v-spacer>
                    </v-toolbar>
                </v-row>
                <v-container class="mr-12">
                    <v-row>
                        <userdashboard-component
                            v-if="prehled == true"
                            :user="user"
                        ></userdashboard-component>
                        <usersettings-component
                            v-if="editace == true"
                            :user="user"
                        >
                        </usersettings-component>
                        <usergui-component
                            v-if="gui == true"
                            :user="user"
                        ></usergui-component>
                        <useralert-component
                            v-if="alert == true"
                            :user="user"
                        ></useralert-component>
                    </v-row>
                </v-container>
            </v-col>
        </v-row>
    </v-main>
</template>
<script>
import UserSideNavBarInfoComponent from "./UserSideBarInfoComponent";
import UserDashboardComponent from "./UserDashboardComponent";
import UserSettingsComponent from "./UserSettingsComponent";
import UserGuiComponent from "./UserGuiComponent";
import UserAlertComponent from "./UserAlertComponent";
export default {
    metaInfo: {
        title: "IPTV Dohled - uživatel"
    },
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
        "usergui-component": UserGuiComponent,
        "useralert-component": UserAlertComponent
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
