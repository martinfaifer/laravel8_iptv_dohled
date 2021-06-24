<template>
    <v-main>
        <v-card absolute height="100%" width="100%" color="transparent" flat>
            <v-container>
                <!-- loading -->

                <div v-if="loading === true && websocketAlert === null">
                    <!-- loading animace -->
                    <v-row align="center" justify="space-around">
                        <span class="mt-12">
                            <i
                                style="color:#EAF0F1"
                                class="fas fa-spinner fa-spin fa-5x"
                            ></i>
                        </span>
                    </v-row>
                </div>
                <div
                    v-else-if="loading === true && websocketAlert === 'alert'"
                    class="mt-12"
                >
                    <v-alert text type="error" outlined>
                        Stream se nepodařilo diagnostikovat
                    </v-alert>
                </div>

                <div v-else>
                    <!-- Servisní informace o streamu -->
                    <v-col
                        cols="12"
                        sm="12"
                        md="12"
                        lg="12"
                        v-if="stream.service.pcrpid != null"
                        class="text-start"
                    >
                        <span>
                            Servisní informace a metadata o streamu:
                        </span>
                        <br />
                        <v-card color="transparent" flat>
                            <v-row>
                                <v-col cols="12" sm="12" md="4" lg="4">
                                    <v-list-item>
                                        <small>
                                            PcrPid:
                                            <span class="ml-3">
                                                <strong>
                                                    {{ stream.service.pcrpid }}
                                                </strong>
                                            </span>
                                        </small></v-list-item
                                    >
                                </v-col>
                                <v-col cols="12" sm="12" md="4" lg="4">
                                    <v-list-item>
                                        <small>
                                            PmtPid:
                                            <span class="ml-3">
                                                <strong>
                                                    {{ stream.service.pmtpid }}
                                                </strong>
                                            </span>
                                        </small></v-list-item
                                    >
                                </v-col>
                                <v-col cols="12" sm="12" md="4" lg="4">
                                    <v-list-item>
                                        <small>
                                            TSid:
                                            <span class="ml-3">
                                                <strong>
                                                    {{ stream.service.tsid }}
                                                </strong>
                                            </span>
                                        </small></v-list-item
                                    >
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col
                                    cols="12"
                                    sm="12"
                                    md="4"
                                    lg="4"
                                    v-if="stream.ts.country != null"
                                >
                                    <v-list-item>
                                        <small>
                                            Země původu:
                                            <span class="ml-3">
                                                <strong>
                                                    {{ stream.ts.country }}
                                                </strong>
                                            </span>
                                        </small></v-list-item
                                    >
                                </v-col>

                                <v-col
                                    cols="12"
                                    sm="12"
                                    md="4"
                                    lg="4"
                                    v-if="stream.service.provider != null"
                                >
                                    <v-list-item>
                                        <small>
                                            Provider:
                                            <span class="ml-3">
                                                <strong>
                                                    {{
                                                        stream.service.provider
                                                    }}
                                                </strong>
                                            </span>
                                        </small></v-list-item
                                    >
                                </v-col>
                                <v-col
                                    cols="12"
                                    sm="12"
                                    md="4"
                                    lg="4"
                                    v-if="stream.service.name != null"
                                >
                                    <v-list-item>
                                        <small>
                                            Název
                                            <span class="ml-3">
                                                <strong>
                                                    {{ stream.service.name }}
                                                </strong>
                                            </span>
                                        </small></v-list-item
                                    >
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-col>
                    <!-- CA Modul-->
                    <v-col
                        cols="12"
                        sm="12"
                        md="12"
                        lg="12"
                        v-if="stream.ca.access != null"
                        class="text-start mt-6"
                    >
                        <span>
                            Informace o CA Modulu:
                        </span>
                        <br />
                        <v-card color="transparent" flat>
                            <v-row>
                                <v-col cols="12" sm="12" md="6" lg="6">
                                    <v-list-item>
                                        <small>
                                            Dekryptace:
                                            <span
                                                class="ml-3"
                                                :class="{
                                                    'green--text':
                                                        stream.ca.access ==
                                                        'success',
                                                    'red--text':
                                                        stream.ca.access !=
                                                        'success'
                                                }"
                                            >
                                                <strong
                                                    v-if="
                                                        stream.ca.access ==
                                                            'success'
                                                    "
                                                >
                                                    Dekryptuje
                                                </strong>
                                                <strong v-else>
                                                    Nedekryptuje
                                                </strong>
                                            </span>
                                        </small></v-list-item
                                    >
                                </v-col>
                                <v-col cols="12" sm="12" md="6" lg="6">
                                    <v-list-item>
                                        <small>
                                            Scrambled:
                                            <span
                                                class="ml-3"
                                                :class="{
                                                    'green--text':
                                                        stream.ca.scrambled ==
                                                        '0',
                                                    'red--text':
                                                        stream.ca.scrambled !=
                                                        '0'
                                                }"
                                            >
                                                <strong>
                                                    {{ stream.ca.scrambled }}
                                                </strong>
                                            </span>
                                        </small></v-list-item
                                    >
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col
                                    v-if="stream.ca.description != null"
                                    sm="12"
                                    md="12"
                                    lg="12"
                                >
                                    <v-list-item>
                                        <v-list-item>
                                            <small>
                                                Typ:
                                                <span class="ml-3">
                                                    <strong>
                                                        {{
                                                            stream.ca
                                                                .description
                                                        }}
                                                    </strong>
                                                </span>
                                            </small></v-list-item
                                        >
                                    </v-list-item>
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-col>
                    <v-divider></v-divider>
                    <v-row>
                        <v-col cols="12" sm="12" md="6" lg="6">
                            <!-- audio -->
                            <div
                                v-if="stream.audio.pid != null"
                                class="text-start mt-6"
                            >
                                <v-row>
                                    <v-col cols="12" sm="12" md="12" lg="12">
                                        <span class="font-weight-bold ml-3">
                                            Audio:
                                            <v-icon
                                                small
                                                class="ml-12"
                                                @click="loadAudioBitrate()"
                                            >
                                                mdi-chart-areaspline
                                            </v-icon>
                                        </span>
                                    </v-col>
                                </v-row>
                                <v-card color="transparent" flat>
                                    <v-row>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    Discontinuity Errory:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.audio
                                                                    .discontinuities ==
                                                                '0',
                                                            'red--text':
                                                                stream.audio
                                                                    .discontinuities !=
                                                                '0'
                                                        }"
                                                    >
                                                        <strong>
                                                            {{
                                                                stream.audio
                                                                    .discontinuities
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    Scrambled:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.audio
                                                                    .scrambled ==
                                                                '0',
                                                            'red--text':
                                                                stream.audio
                                                                    .scrambled !=
                                                                '0'
                                                        }"
                                                    >
                                                        <strong>
                                                            {{
                                                                stream.audio
                                                                    .scrambled
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    Packets:
                                                    <span
                                                        class="ml-3 green--text"
                                                    >
                                                        <strong>
                                                            {{
                                                                stream.audio
                                                                    .packets
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    Clear:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.audio
                                                                    .clear ==
                                                                stream.audio
                                                                    .packets,
                                                            'red--text':
                                                                stream.audio
                                                                    .scrambled !=
                                                                stream.audio
                                                                    .packets
                                                        }"
                                                    >
                                                        <strong>
                                                            {{
                                                                stream.audio
                                                                    .clear
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    PID:
                                                    <span class="ml-3">
                                                        <strong>
                                                            {{
                                                                stream.audio.pid
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    Jazyková stopa:
                                                    <span class="ml-3">
                                                        <strong>
                                                            {{
                                                                stream.audio
                                                                    .audioLanguage
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col
                                            cols="12"
                                            sm="12"
                                            md="12"
                                            lg="12"
                                        >
                                            <v-list-item
                                                v-if="
                                                    stream.audio.description !=
                                                        null
                                                "
                                            >
                                                <small>
                                                    Popis pidu:
                                                    <span class="ml-3">
                                                        <strong>
                                                            {{
                                                                stream.audio
                                                                    .description
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                    </v-row>
                                </v-card>
                                <div class="mt-12 ml-3 mr-3">
                                    <span class="ml-3">
                                        Bitrate
                                        <strong
                                            v-if="stream.audio.bitrate === 0"
                                            class="red--text"
                                        >
                                            {{ stream.audio.bitrate }}
                                        </strong>
                                        <strong v-else class="green--text">
                                            {{
                                                Math.round(
                                                    stream.audio.bitrate / 1024
                                                )
                                            }}
                                            kbps
                                        </strong></span
                                    >
                                    <v-progress-linear
                                        v-if="
                                            stream.audio.audioAccess ===
                                                'success'
                                        "
                                        :buffer-value="
                                            countBitrate(stream.audio.bitrate)
                                        "
                                        :value="
                                            countBitrate(stream.audio.bitrate)
                                        "
                                        color="success"
                                        stream
                                    >
                                    </v-progress-linear>
                                    <v-progress-linear
                                        v-else
                                        :value="
                                            countBitrate(stream.audio.bitrate)
                                        "
                                        :buffer-value="
                                            countBitrate(stream.audio.bitrate)
                                        "
                                        color="error"
                                        stream
                                    >
                                    </v-progress-linear>
                                </div>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="12" md="6" lg="6">
                            <!-- video -->
                            <div
                                v-if="stream.video.pid != null"
                                class="text-start mt-6"
                            >
                                <v-row>
                                    <v-col cols="12" sm="12" md="12" lg="12">
                                        <span class="font-weight-bold ml-3">
                                            Video:
                                            <v-icon
                                                small
                                                class="ml-12"
                                                @click="loadVideoBitrate()"
                                            >
                                                mdi-chart-areaspline
                                            </v-icon>
                                        </span>
                                    </v-col>
                                </v-row>
                                <v-card color="transparent" flat>
                                    <v-row>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    Discontinuity Errory:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.video
                                                                    .discontinuities ==
                                                                '0',
                                                            'red--text':
                                                                stream.video
                                                                    .discontinuities !=
                                                                '0'
                                                        }"
                                                    >
                                                        <strong>
                                                            {{
                                                                stream.video
                                                                    .discontinuities
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    Scrambled:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.video
                                                                    .scrambled ==
                                                                '0',
                                                            'red--text':
                                                                stream.video
                                                                    .scrambled !=
                                                                '0'
                                                        }"
                                                    >
                                                        <strong>
                                                            {{
                                                                stream.video
                                                                    .scrambled
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    Packets:
                                                    <span
                                                        class="ml-3 green--text"
                                                    >
                                                        <strong>
                                                            {{
                                                                stream.video
                                                                    .packets
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                        <v-col cols="12" sm="12" md="6" lg="6">
                                            <v-list-item>
                                                <small>
                                                    Clear:
                                                    <span
                                                        class="ml-3"
                                                        :class="{
                                                            'green--text':
                                                                stream.video
                                                                    .clear ==
                                                                stream.video
                                                                    .packets,
                                                            'red--text':
                                                                stream.video
                                                                    .scrambled !=
                                                                stream.video
                                                                    .packets
                                                        }"
                                                    >
                                                        <strong>
                                                            {{
                                                                stream.video
                                                                    .clear
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col
                                            cols="12"
                                            sm="12"
                                            md="12"
                                            lg="12"
                                        >
                                            <v-list-item>
                                                <small>
                                                    PID:
                                                    <span class="ml-3">
                                                        <strong>
                                                            {{
                                                                stream.video.pid
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col
                                            cols="12"
                                            sm="12"
                                            md="12"
                                            lg="126"
                                            v-if="
                                                stream.video.videoDescription !=
                                                    null
                                            "
                                        >
                                            <v-list-item>
                                                <small>
                                                    Popis pidu:
                                                    <span class="ml-3">
                                                        <strong>
                                                            {{
                                                                stream.video
                                                                    .videoDescription
                                                            }}
                                                        </strong>
                                                    </span>
                                                </small></v-list-item
                                            >
                                        </v-col>
                                    </v-row>
                                </v-card>
                                <div class="mt-12 ml-3 mr-3">
                                    <span class="ml-3">
                                        Bitrate
                                        <strong
                                            v-if="stream.video.bitrate === 0"
                                            class="red--text"
                                        >
                                            {{ stream.video.bitrate }}
                                        </strong>

                                        <strong class="green--text">
                                            {{
                                                Math.round(
                                                    (stream.video.bitrate /
                                                        1048576) *
                                                        100
                                                ) / 100
                                            }}
                                            Mbps
                                        </strong>
                                    </span>
                                    <v-progress-linear
                                        v-if="stream.video.access === 'success'"
                                        :value="
                                            countBitrate(stream.video.bitrate)
                                        "
                                        :buffer-value="
                                            countBitrate(stream.video.bitrate)
                                        "
                                        color="success"
                                        stream
                                    >
                                    </v-progress-linear>
                                    <v-progress-linear
                                        v-else
                                        :value="
                                            countBitrate(stream.video.bitrate)
                                        "
                                        :buffer-value="
                                            countBitrate(stream.video.bitrate)
                                        "
                                        color="error"
                                        stream
                                    >
                                    </v-progress-linear>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </div>
            </v-container>
        </v-card>

        <!-- dialog -->

        <v-row justify="center">
            <v-dialog v-model="chart_dialog" persistent max-width="800">
                <v-card flat color="#1E1E1E" light>
                    <v-card-title class="white--text">
                        {{ cardTitle }}
                    </v-card-title>

                    <v-container fluid v-if="audioChart === true">
                        <apexchart
                            v-if="this.series_audio[0].data.length > 0"
                            height="285"
                            type="area"
                            :options="chartOptions_Audio"
                            :series="series_audio"
                        ></apexchart>
                    </v-container>
                    <v-container fluid v-if="videoChart === true">
                        <apexchart
                            v-if="this.series_video[0].data.length > 0"
                            height="285"
                            type="area"
                            :options="chartOptions_Video"
                            :series="series_video"
                        ></apexchart>
                    </v-container>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="green"
                            text
                            outlined
                            @click="closeDialog()"
                        >
                            zavřít
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        chart_dialog: false,
        cardTitle: "",
        audioChart: false,
        videoChart: false,
        audioTab: null,
        videoTab: null,
        loading: true,
        stream: {
            video: {
                bitrate: null,
                pid: null,
                discontinuities: null,
                scrambled: null,
                access: null,
                videoDescription: null,
                clear: null,
                packets: null
            },
            audio: {
                bitrate: null,
                pid: null,
                discontinuities: null,
                scrambled: null,
                audioLanguage: null,
                audioAccess: null,
                access: null,
                description: null,
                clear: null,
                packets: null
            },
            ca: {
                access: null,
                scrambled: null,
                description: null
            },
            service: {
                pcrpid: null,
                pmtpid: null,
                tsid: null,
                name: null,
                provider: null
            },
            ts: {
                country: null
            }
        },
        interval: null,
        videoLabels: [],
        videoBitrateChartArray: [],
        audioBitrateChartArray: [],
        audioLabels: [],
        streamId: null,
        ccErorrs: [],

        drawDuration: 2000,
        labelSize: 4,
        lineWidth: 1,
        labels: true,
        routeId: null,
        websocketAlert: null,

        chartOptions_Audio: {
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            chart: {
                id: "Audio_bitrate",
                animations: {
                    enabled: true,
                    easing: "linear",
                    dynamicAnimation: {
                        speed: 1000
                    }
                },
                toolbar: {
                    show: false
                }
            },
            xaxis: {
                categories: null,
                labels: {
                    show: false
                }
            }
        },
        series_audio: [
            {
                name: "takový tok",
                data: [] // data
            }
        ],

        chartOptions_Video: {
            dataLabels: {
                enabled: false
            },
            chart: {
                id: "Audio bitrate"
            },
            xaxis: {
                categories: null,
                labels: {
                    show: false
                }
            }
        },
        series_video: [
            {
                name: "takový tok",
                data: [] // data
            }
        ]
    }),

    created() {
        this.loadCCError();
        this.bindRouteId();
        this.websocketData();
        this.checkIfSebsocketisConnectedAndReturnResponse();
    },
    methods: {
        bindRouteId() {
            this.routeId = this.$route.params.id;
        },
        closeDialog() {
            this.cardTitle = "";
            this.chart_dialog = false;
            this.audioChart = false;
            this.videoChart = false;
        },
        loadAudioBitrate() {
            axios
                .post("streamInfo/audio_bitrate", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    this.cardTitle = "Audio bitrate";
                    this.chart_dialog = true;
                    this.audioChart = true;
                    this.chartOptions_Audio.xaxis.categories =
                        response.data.xaxis;
                    this.series_audio[0].data = response.data.seriesData;
                })
                .catch(error => {
                    this.cardTitle = "Audio bitrate";
                    this.chart_dialog = true;
                    this.audioChart = "error";
                });
        },

        loadVideoBitrate() {
            axios
                .post("streamInfo/video_bitrate", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    this.cardTitle = "Video bitrate";
                    this.chart_dialog = true;
                    this.videoChart = true;
                    this.chartOptions_Video.xaxis.categories =
                        response.data.xaxis;
                    this.series_video[0].data = response.data.seriesData;
                })
                .catch(error => {
                    this.cardTitle = "Audio bitrate";
                    this.chart_dialog = true;
                    this.videoChart = "error";
                });
        },

        loadCCError() {
            try {
                axios
                    .post("streamInfo/ccError", {
                        streamId: this.$route.params.id
                    })
                    .then(response => {
                        if (response.data.status === "exist") {
                            this.ccErorrs.audio = response.data.audio[0];
                            this.ccErorrs.audioTime =
                                response.data.audioTime[0];
                            this.ccErorrs.video = response.data.video[0];
                            this.ccErorrs.videoTime =
                                response.data.videoTime[0];
                        }
                        this.ccErorrs = response.data;
                    });
            } catch (error) {}
        },
        currentTime() {
            var today = new Date();
            return (
                today.getHours() +
                ":" +
                today.getMinutes() +
                ":" +
                today.getSeconds()
            );
        },

        checkIfSebsocketisConnectedAndReturnResponse() {
            this.websocketAlert = null;
            setTimeout(
                function() {
                    if (this.stream.video.pid === null) {
                        this.websocketAlert = "alert";
                    }
                }.bind(this),
                5000
            );
        },

        async websocketData() {
            try {
                Echo.channel(
                    "streamInfoTsVideoBitrate" + this.$route.params.id
                ).listen("StreamInfoTsVideoBitrate", eVideo => {
                    this.stream.video.bitrate = eVideo["bitrate"];
                    this.videoBitrateChartArray.push(
                        parseInt(eVideo["bitrate"])
                    );
                    if (this.videoBitrateChartArray.length == 40) {
                        this.videoBitrateChartArray.shift();
                    }
                    this.stream.video.pid = eVideo["videoPid"];
                    this.stream.video.discontinuities =
                        eVideo["discontinuities"];
                    this.stream.video.scrambled = eVideo["scrambled"];

                    this.stream.video.access = eVideo["access"];
                    this.stream.video.clear = eVideo["clear"];
                    this.stream.video.packets = eVideo["packets"];
                    this.stream.video.videoDescription =
                        eVideo["videoDescription"];
                    this.loading = false;
                    this.websocketAlert = null;
                });

                Echo.channel(
                    "streamInfoTsAudioBitrate" + this.$route.params.id
                ).listen("StreamInfoAudioBitrate", eAudio => {
                    this.stream.audio.bitrate = eAudio["bitrate"];
                    this.audioBitrateChartArray.push(
                        parseInt(eAudio["bitrate"])
                    );
                    if (this.audioBitrateChartArray.length == 40) {
                        this.audioBitrateChartArray.shift();
                    }
                    this.stream.audio.pid = eAudio["audioPid"];
                    this.stream.audio.discontinuities =
                        eAudio["audioDiscontinuities"];
                    this.stream.audio.scrambled = eAudio["audioScrambled"];
                    this.stream.audio.audioLanguage = eAudio["audioLanguage"];
                    this.stream.audio.audioAccess = eAudio["audioAccess"];
                    this.stream.audio.clear = eAudio["clear"];
                    this.stream.audio.packets = eAudio["packets"];
                    this.stream.audio.description = eAudio["audioDescription"];
                });

                Echo.channel("streamInfoTsCa" + this.$route.params.id).listen(
                    "StreamInfoCa",
                    eCA => {
                        this.stream.ca.access = eCA["access"];
                        this.stream.ca.description = eCA["description"];
                        this.stream.ca.scrambled = eCA["scrambled"];
                    }
                );

                Echo.channel(
                    "streamInfoTsService" + this.$route.params.id
                ).listen("StreamInfoService", eService => {
                    this.stream.service.pcrpid = eService["pcrpid"];
                    this.stream.service.pmtpid = eService["pmtpid"];
                    this.stream.service.tsid = eService["tsid"];
                    this.stream.service.name = eService["name"];
                    this.stream.service.provider = eService["provider"];
                });

                Echo.channel("streamInfoTS" + this.$route.params.id).listen(
                    "StreamInfoTs",
                    eTS => {
                        this.stream.ts.country = eTS["country"];
                    }
                );
            } catch (error) {}
        },

        resetStreamDataToDefault() {
            this.stream.video.pid = null;
            this.stream.video.bitrate = null;
            this.stream.video.discontinuities = null;
            this.stream.video.scrambled = null;
            this.stream.video.access = null;
            this.stream.video.videoDescription = null;

            this.stream.audio.bitrate = null;
            this.stream.audio.pid = null;
            this.stream.audio.discontinuities = null;
            this.stream.audio.scrambled = null;
            this.stream.audio.audioLanguage = null;
            this.stream.audio.audioAccess = null;
            this.stream.audio.access = null;
            this.stream.audio.description = null;

            this.stream.ca.access = null;
            this.stream.ca.scrambled = null;
            this.stream.ca.description = null;

            this.stream.ca.pcrpid = null;
            this.stream.ca.pmtpid = null;
            this.stream.ca.tsid = null;
            this.stream.ca.name = null;
            this.stream.ca.provider = null;

            this.stream.ts.country = null;
        },

        countBitrate(bitrate) {
            return bitrate / "1048576";
        }
    },

    mounted() {
        this.websocketData();

        this.interval = setInterval(() => {
            try {
                this.loadCCError();
            } catch (error) {}
        }, 15000);
    },
    watch: {
        $route(to, from) {
            try {
                this.audioTab = null;
                this.videoTab = null;
                this.resetStreamDataToDefault();
                this.loading = true;
                this.bindRouteId();
                this.checkIfSebsocketisConnectedAndReturnResponse();
                Echo.leaveChannel("streamInfoTsVideoBitrate" + from.params.id);
                Echo.leaveChannel("streamInfoTsAudioBitrate" + from.params.id);
                Echo.leaveChannel("streamInfoTsCa" + from.params.id);
                Echo.leaveChannel("streamInfoTsService" + from.params.id);
                Echo.leaveChannel("streamInfoTS" + from.params.id);

                this.videoBitrateChartArray = [];
                this.audioBitrateChartArray = [];

                this.loadCCError();

                Echo.channel("streamInfoTsVideoBitrate" + to.params.id).listen(
                    "StreamInfoTsVideoBitrate",
                    eVideo => {
                        this.loading = false;
                        this.stream.video.bitrate = eVideo["bitrate"];
                        this.videoBitrateChartArray.push(
                            parseInt(eVideo["bitrate"])
                        );

                        if (this.videoBitrateChartArray.length == 10) {
                            this.videoBitrateChartArray.shift();
                        }

                        this.videoLabels.push(this.currentTime());
                        if (this.videoLabels.length == 10) {
                            this.videoLabels.shift();
                        }

                        this.stream.video.pid = eVideo["videoPid"];
                        this.stream.video.discontinuities =
                            eVideo["discontinuities"];
                        this.stream.video.scrambled = eVideo["scrambled"];
                        this.stream.video.access = eVideo["access"];
                        this.stream.video.videoDescription =
                            eVideo["videoDescription"];
                    }
                );

                Echo.channel("streamInfoTsAudioBitrate" + to.params.id).listen(
                    "StreamInfoAudioBitrate",
                    eAdudio => {
                        this.stream.audio.bitrate = eAdudio["bitrate"];
                        this.audioBitrateChartArray.push(
                            parseInt(eAdudio["bitrate"])
                        );
                        if (this.audioBitrateChartArray.length == 10) {
                            this.audioBitrateChartArray.shift();
                        }

                        this.audioLabels.push(this.currentTime());
                        if (this.audioLabels.length == 10) {
                            this.audioLabels.shift();
                        }
                        this.stream.audio.pid = eAdudio["audioPid"];
                        this.stream.audio.discontinuities =
                            eAdudio["audioDiscontinuities"];
                        this.stream.audio.scrambled = eAdudio["audioScrambled"];
                        this.stream.audio.audioLanguage =
                            eAdudio["audioLanguage"];
                        this.stream.audio.audioAccess = eAdudio["audioAccess"];
                    }
                );

                Echo.channel("streamInfoTsCa" + to.params.id).listen(
                    "StreamInfoCa",
                    eCA => {
                        this.stream.ca.access = eCA["access"];
                        this.stream.audio.description = eCA["description"];
                        this.stream.audio.scrambled = eCA["scrambled"];
                    }
                );

                Echo.channel("streamInfoTsService" + to.params.id).listen(
                    "StreamInfoService",
                    eService => {
                        this.stream.service.pcrpid = eService["pcrpid"];
                        this.stream.service.pmtpid = eService["pmtpid"];
                        this.stream.service.tsid = eService["tsid"];
                        this.stream.service.name = eService["name"];
                        this.stream.service.provider = eService["provider"];
                    }
                );

                Echo.channel("streamInfoTS" + to.params.id).listen(
                    "StreamInfoTs",
                    eTS => {
                        this.stream.ts.country = eTS["country"];
                    }
                );
            } catch (error) {}
        }
    },

    beforeDestroy: function() {
        clearInterval(this.interval);
        Echo.leaveChannel("streamInfoTsVideoBitrate" + this.routeId);
        Echo.leaveChannel("streamInfoTsAudioBitrate" + this.routeId);
        Echo.leaveChannel("streamInfoTsCa" + this.routeId);
        Echo.leaveChannel("streamInfoTsService" + this.routeId);
        Echo.leaveChannel("streamInfoTS" + this.routeId);
    }
};
</script>
