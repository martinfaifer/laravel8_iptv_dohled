<template>
    <v-main>
        <v-card
            height="100%"
            width="100%"
            class="text-center pl-10 pr-10"
            color="transparent"
            flat
        >
            <span>
                TS data
            </span>

            <v-container>
                <!-- audio -->
                <div v-if="stream.audio != null" class="text-start">
                    <span>
                        Audio bitrate:
                        <strong>
                            {{ Math.round(stream.audio.bitrate / 1024) }}
                            kbps
                        </strong>
                    </span>
                    <br />
                    <v-card color="transparent" flat>
                        <v-list-item>
                            <v-list-item>
                                <small>
                                    Discontinuity Errory:
                                    <span
                                        class="ml-3"
                                        :class="{
                                            'green--text':
                                                stream.audio.discontinuities ==
                                                '0',
                                            'red--text':
                                                stream.audio.discontinuities !=
                                                '0'
                                        }"
                                    >
                                        <strong>
                                            {{ stream.audio.discontinuities }}
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >

                            <v-list-item>
                                <small>
                                    Scrambled:
                                    <span
                                        class="ml-3"
                                        :class="{
                                            'green--text':
                                                stream.audio.scrambled == '0',
                                            'red--text':
                                                stream.audio.scrambled != '0'
                                        }"
                                    >
                                        <strong>
                                            {{ stream.audio.scrambled }}
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >
                        </v-list-item>

                        <v-list-item>
                            <v-list-item>
                                <small>
                                    PID:
                                    <span class="ml-3">
                                        <strong>
                                            {{ stream.audio.pid }}
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >

                            <v-list-item>
                                <small>
                                    Jazyková stopa:
                                    <span class="ml-3">
                                        <strong>
                                            {{ stream.audio.language }}
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >
                        </v-list-item>
                    </v-card>
                    <v-progress-linear
                        v-if="stream.audio.access == 'success'"
                        :buffer-value="stream.audio.bitrate / '1048576'"
                        v-model="stream.audio.bitrate / '1048576'"
                        color="success"
                        stream
                    >
                    </v-progress-linear>
                    <v-progress-linear
                        v-else
                        v-model="stream.audio.bitrate / '1048576'"
                        :buffer-value="stream.audio.bitrate / '1048576'"
                        color="error"
                        stream
                    >
                    </v-progress-linear>

                    <br />
                </div>

                <!-- video -->
                <div v-if="stream.video != null" class="text-start">
                    <span>
                        Video bitrate:
                        <strong>
                            {{ Math.round((stream.video.bitrate / 1048576)*100) /100 }}
                            Mbps
                        </strong>
                    </span>
                    <br />
                    <v-card color="transparent" flat>
                        <v-list-item>
                            <v-list-item>
                                <small>
                                    Discontinuity Errory:
                                    <span
                                        class="ml-3"
                                        :class="{
                                            'green--text':
                                                stream.video.discontinuities ==
                                                '0',
                                            'red--text':
                                                stream.video.discontinuities !=
                                                '0'
                                        }"
                                    >
                                        <strong>
                                            {{ stream.video.discontinuities }}
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >

                            <v-list-item>
                                <small>
                                    Scrambled:
                                    <span
                                        class="ml-3"
                                        :class="{
                                            'green--text':
                                                stream.video.scrambled == '0',
                                            'red--text':
                                                stream.video.scrambled != '0'
                                        }"
                                    >
                                        <strong>
                                            {{ stream.video.scrambled }}
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >
                        </v-list-item>

                        <v-list-item>
                            <v-list-item>
                                <small>
                                    PID:
                                    <span class="ml-3">
                                        <strong>
                                            {{ stream.video.pid }}
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >
                        </v-list-item>
                    </v-card>
                    <v-progress-linear
                        v-if="stream.video.access == 'success'"
                        v-model="stream.video.bitrate / '1048576'"
                        :buffer-value="stream.video.bitrate / '1048576'"
                        color="success"
                        stream
                    >
                    </v-progress-linear>

                    <v-progress-linear
                        v-else
                        v-model="stream.video.bitrate / '1048576'"
                        :buffer-value="stream.video.bitrate / '1048576'"
                        color="error"
                        stream
                    >
                    </v-progress-linear>

                    <br />
                </div>

                <!-- CA Modul-->
                <div v-if="stream.ca != null" class="text-start">
                    <span>
                        Informace o CA Modulu:
                    </span>
                    <br />
                    <v-card color="transparent" flat>
                        <v-list-item>
                            <v-list-item>
                                <small>
                                    Dekryptace:
                                    <span
                                        class="ml-3"
                                        :class="{
                                            'green--text':
                                                stream.ca.access == 'success',
                                            'red--text':
                                                stream.ca.access != 'success'
                                        }"
                                    >
                                        <strong
                                            v-if="stream.ca.access == 'success'"
                                        >
                                            Dekryptuje
                                        </strong>
                                        <strong v-else>
                                            Nedekryptuje
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >
                            <v-list-item>
                                <small>
                                    Scrambled:
                                    <span
                                        class="ml-3"
                                        :class="{
                                            'green--text':
                                                stream.ca.scrambled == '0',
                                            'red--text':
                                                stream.ca.scrambled != '0'
                                        }"
                                    >
                                        <strong>
                                            {{ stream.ca.scrambled }}
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >
                        </v-list-item>
                        <v-list-item v-if="stream.ca.description != null">
                            <v-list-item>
                                <small>
                                    Typ:
                                    <span class="ml-3">
                                        <strong>
                                            {{ stream.ca.description }}
                                        </strong>
                                    </span>
                                </small></v-list-item
                            >
                        </v-list-item>
                    </v-card>
                    <br />
                </div>

                <!-- Servisní informace o streamu -->
                <div v-if="stream.service != null" class="text-start">
                    <span>
                        Servisní informace o streamu:
                    </span>
                    <br />
                    <v-card color="transparent" flat>
                        <v-list-item>
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
                        </v-list-item>
                    </v-card>
                </div>
            </v-container>
        </v-card>
    </v-main>
</template>
<script>
export default {
    props: ["streamId"],
    data: () => ({
        stream: []
    }),

    created() {
        this.getStreamDetailInfo();
    },
    methods: {
        getStreamDetailInfo() {
            window.axios
                .post("streamInfo/detail", {
                    streamId: this.streamId
                })
                .then(response => {
                    this.stream = response.data;
                });
        }
    },

    mounted() {},
    watch: {}
};
</script>
