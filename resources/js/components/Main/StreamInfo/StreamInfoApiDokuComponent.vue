<template>
    <v-main>
        <div class="text-center">
            <span>
                <strong> Výpis z dokumentace </strong>
            </span>
            <v-card flat color="transparent">
                <v-container
                    v-if="dataFromDoku === null || dataFromDoku.length == 0"
                >
                    <v-alert outlined text type="info">
                        <strong
                            >Stream se nepodařilo vyhledat v dokumentaci</strong
                        >
                    </v-alert>
                </v-container>
                <v-container v-else class="text-center subtitle-2">
                    <v-card flat color="transparent">
                        <v-card-text class="white--text">
                            <v-row>
                                <v-col
                                    cols="12"
                                    sm="1"
                                    md="1"
                                    lg="1"
                                    v-if="dataFromDoku.logo != null"
                                >
                                    <v-img
                                        :lazy-src="dataFromDoku.logo"
                                        max-height="64"
                                        max-width="64"
                                        :src="dataFromDoku.logo"
                                    ></v-img>
                                </v-col>
                                <v-col cols="12" sm="10" md="10" lg="10">
                                    <h2 class="mt-6">
                                        {{ dataFromDoku.channel_name }}
                                    </h2>
                                </v-col>
                            </v-row>

                            <v-col cols="12">
                                <span>
                                    <strong>
                                        Proklik do dokumentace:
                                    </strong>
                                    <a
                                        style="text-decoration:none"
                                        :href="dataFromDoku.channel_uri"
                                        target="_blink"
                                    >
                                        {{ dataFromDoku.channel_uri }}
                                    </a>
                                </span>
                            </v-col>

                            <v-row v-if="dataFromDoku.device">
                                <v-col cols="12">
                                    <span>
                                        <strong>
                                            Zařízení:
                                        </strong>
                                        <span class="white--text">
                                            {{ dataFromDoku.device.name }}
                                        </span>
                                    </span>
                                </v-col>
                                <v-col cols="12">
                                    <span v-if="dataFromDoku.device.ip != null">
                                        <strong>
                                            IP zařízení:
                                        </strong>
                                        <a
                                            style="text-decoration:none"
                                            :href="dataFromDoku.device.ip"
                                            target="_blink"
                                        >
                                            {{ dataFromDoku.device.ip }}
                                        </a>
                                    </span>
                                </v-col>
                                <v-col cols="12">
                                    <span
                                        v-if="
                                            dataFromDoku.device.controller_ip !=
                                                null
                                        "
                                    >
                                        <strong>
                                            IP kontroleru:
                                        </strong>
                                        <a
                                            style="text-decoration:none"
                                            :href="
                                                dataFromDoku.device
                                                    .controller_ip
                                            "
                                            target="_blink"
                                        >
                                            {{
                                                dataFromDoku.device
                                                    .controller_ip
                                            }}
                                        </a>
                                    </span>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </v-container>
            </v-card>
        </div>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        tab: null,
        items: ["Multicast", "H264", "H264"],
        streamData: [],
        multicast: null,
        h264: null,
        h264Device: null,
        h264DeviceId: null,
        h265: null,
        h265Device: null,
        h265DeviceId: null,
        multiplexer: null,
        prijem: null,
        dataFromDoku: null,
        streamId: null
    }),

    created() {
        this.getStreamDokuInfo();
    },
    methods: {
        getStreamDokuInfo() {
            axios
                .post("/api/iptvdoku/search/stream", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    if (response.data.status !== "success") {
                        return this.dataFromDoku = null;
                    }

                    if (response.data.alert) {
                        return this.dataFromDoku = null;
                    }

                    return this.dataFromDoku = response.data;
                })
                .catch(error => {
                    this.dataFromDoku = null;
                });
        }
    },
    watch: {
        $route(to, from) {
            this.getStreamDokuInfo();
        }
    }
};
</script>
