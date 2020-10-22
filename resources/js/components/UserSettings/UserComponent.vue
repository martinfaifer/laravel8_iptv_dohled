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
