<template>
    <v-main>
        <br />
        <div class="text-center mt-12">
            <span>
                <strong> Výpis z dokumentace </strong>
            </span>
            <v-card flat color="transparent">
                <v-container
                    class="text-center body-2"
                    v-if="streamStatus == 'success'"
                >
                    <!-- obsah z dokumentace získaný po API -->
                    <v-card flat color="transparent" dense>
                        <v-toolbar
                            dense
                            color="transparent"
                            flat
                            class="elevation-0"
                        >
                            <template v-slot:extension>
                                <v-tabs dense v-model="tab">
                                    <v-tabs-slider
                                        dense
                                        color="primary"
                                    ></v-tabs-slider>

                                    <v-tab>
                                        Multicast
                                    </v-tab>
                                    <v-tab>
                                        H264
                                    </v-tab>
                                    <v-tab>
                                        H265
                                    </v-tab>
                                </v-tabs>
                            </template>
                        </v-toolbar>

                        <v-tabs-items
                            v-model="tab"
                            class="body-2"
                            color="transparent"
                        >
                            <v-tab-item v-show="tab == 0">
                                <v-card
                                    flat
                                    class="mt-1 ml-2"
                                    color="transparent"
                                >
                                    <v-card-text v-if="multicast != null">
                                        <v-row>
                                            <strong>
                                                <a
                                                    id="link"
                                                    :href="
                                                        'http://iptvdoku.grapesc.cz/#/channel/' +
                                                            multicast.id
                                                    "
                                                    target="_blank"
                                                >
                                                    Proklik na kanál do
                                                    dokumentace
                                                </a>
                                            </strong>
                                        </v-row>
                                        <v-divider
                                            class="mt-2 mb-2"
                                        ></v-divider>
                                        <v-row>
                                            <span>
                                                IP k STB:
                                                <span class="ml-1">
                                                    <strong>
                                                        {{ multicast.ipKstb }}
                                                    </strong>
                                                </span>
                                            </span>
                                        </v-row>
                                        <v-row class="mt-1">
                                            <span>
                                                Multiplexer:
                                                <span class="ml-1">
                                                    <strong>
                                                        <a
                                                            id="link"
                                                            :href="
                                                                'http://iptvdoku.grapesc.cz/#/device/' +
                                                                    multiplexer.id
                                                            "
                                                            target="_blank"
                                                        >
                                                            {{
                                                                multiplexer.name
                                                            }}
                                                        </a>
                                                    </strong>
                                                </span>
                                            </span>
                                        </v-row>
                                        <v-row class="mt-1">
                                            <span>
                                                Příjem:
                                                <span class="ml-1">
                                                    <strong>
                                                        <a
                                                            id="link"
                                                            :href="
                                                                'http://iptvdoku.grapesc.cz/#/device/' +
                                                                    prijem.id
                                                            "
                                                            target="_blank"
                                                        >
                                                            {{ prijem.name }}
                                                        </a>
                                                    </strong>
                                                </span>
                                            </span>
                                        </v-row>
                                    </v-card-text>
                                    <v-card-text v-else flat> </v-card-text>
                                    <!-- Nic zde není -->
                                </v-card>
                            </v-tab-item>
                            <v-tab-item v-show="tab == 1">
                                <v-card class="mt-1 ml-2" flat>
                                    <v-card-text v-if="h264 != null">
                                        <v-row>
                                            <span>
                                                Transcoder:
                                                <span class="ml-1">
                                                    <strong>
                                                        <a
                                                            id="link"
                                                            :href="
                                                                'http://iptvdoku.grapesc.cz/#/device/' +
                                                                    h264DeviceId
                                                            "
                                                            target="_blank"
                                                        >
                                                            {{ h264Device }}
                                                        </a>
                                                    </strong>
                                                </span>
                                            </span>
                                        </v-row>
                                        <v-row class="mt-1">
                                            <span>
                                                Chunk storeId:
                                                <span class="ml-1">
                                                    <strong>
                                                        {{
                                                            h264.chunk_Store_id
                                                        }}
                                                    </strong>
                                                </span>
                                            </span>
                                        </v-row>
                                        <v-row class="mt-1">
                                            <span>
                                                HLS kdekoliv
                                                <span class="ml-1">
                                                    <strong>
                                                        {{
                                                            h264.m3u8_HLS_kdekoliv
                                                        }}
                                                    </strong>
                                                </span>
                                            </span>
                                        </v-row>
                                        <v-row class="mt-1">
                                            <span>
                                                HLS mobile
                                                <span class="ml-1">
                                                    <strong>
                                                        {{ h264.m3u8_mobile }}
                                                    </strong>
                                                </span>
                                            </span>
                                        </v-row>
                                        <v-row class="mt-1">
                                            <span>
                                                HLS stb
                                                <span class="ml-1">
                                                    <strong>
                                                        {{ h264.m3u8_stb }}
                                                    </strong>
                                                </span>
                                            </span>
                                        </v-row>
                                    </v-card-text>
                                    <v-card-text v-else flat>
                                        <!-- nic zde není -->
                                    </v-card-text>
                                </v-card>
                            </v-tab-item>
                            <v-tab-item v-show="tab == 2">
                                <v-card class="mt-1 ml-2" flat>
                                    <v-card-text v-if="h265 != null">
                                        <v-row>
                                            <span>
                                                Transcoder:
                                                <span class="ml-1">
                                                    <strong>
                                                        <a
                                                            id="link"
                                                            :href="
                                                                'http://iptvdoku.grapesc.cz/#/device/' +
                                                                    h265DeviceId
                                                            "
                                                            target="_blank"
                                                        >
                                                            {{ h265Device }}
                                                        </a>
                                                    </strong>
                                                </span>
                                            </span>
                                        </v-row>
                                    </v-card-text>
                                    <v-card-text v-else>
                                        <!-- nic zde není -->
                                    </v-card-text>
                                </v-card>
                            </v-tab-item>
                        </v-tabs-items>
                    </v-card>
                </v-container>
                <v-container v-else>
                    <div>
                        <v-alert dense text type="info">
                            <strong
                                >Stream se nepodařilo vyhledat v
                                dokumentaci</strong
                            >
                        </v-alert>
                    </div>
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
        streamStatus: null,
        streamId: null
    }),

    created() {},
    methods: {
        getStreamDokuInfo() {
            if (this.iptvDokuConnectionStatus == "success") {
                window.axios
                    .post("/api/iptvdoku/search/stream", {
                        streamId: this.$route.params.id
                    })
                    .then(response => {
                        this.streamStatus = response.data.status;
                        if (response.data) {
                            this.multicast = response.data.channnelData;
                            this.h264 = response.data.h264;
                            (this.h264Device = response.data.h264Device),
                                (this.h264DeviceId =
                                    response.data.h264DeviceId),
                                (this.h265 = response.data.h265);
                            (this.h265Device = response.data.h265Device),
                                (this.h265DeviceId =
                                    response.data.h265DeviceId),
                                (this.multiplexer = response.data.multiplexer);
                            this.prijem = response.data.prijem;
                        }
                    });
            }
        }
    },

    computed: {
        iptvDokuConnectionStatus() {
            return this.$store.state.iptvDokuConnectionStatus;
        }
    },
    mounted() {
        if (this.iptvDokuConnectionStatus == "success") {
            this.getStreamDokuInfo();
        }
    },
    watch: {
        $route(to, from) {
            this.getStreamDokuInfo();
        }
        // iptvDokuConnectionStatus() {
        //     if(this.iptvDokuConnectionStatus == 'success') {
        //         this.getStreamDokuInfo();
        //     }
        // }
    }
};
</script>
