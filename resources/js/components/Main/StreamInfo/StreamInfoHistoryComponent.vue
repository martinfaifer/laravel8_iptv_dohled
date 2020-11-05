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
                Historie
            </span>
            <v-container v-if="streamHistory != 'none'">
                <v-timeline dense>
                    <v-timeline-item
                        v-for="stream in streamHistory"
                        :key="stream.id"
                        :color="stream.color"
                        small
                    >
                        <template v-slot:opposite>
                            <span
                                class="
                                                body-1 font-weight-bold
                                            "
                                v-text="stream.created_at"
                            ></span>
                        </template>
                        <div class="py-4">
                            <div v-if="stream.status == 'stream_ok'">
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Stream je v
                                        pořádku
                                    </strong>
                                </small>
                            </div>
                            <div
                                v-else-if="
                                    stream.status == 'scrambledPids_warning'
                                "
                            >
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Změněno
                                        pořadí Pidů
                                    </strong>
                                </small>
                            </div>
                            <div
                                v-else-if="
                                    stream.status == 'transporterrors_warning'
                                "
                            >
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Objevily se
                                        TS chyby
                                    </strong>
                                </small>
                            </div>

                            <div v-else-if="stream.status == 'stream_error'">
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Stream
                                        nefunguje
                                    </strong>
                                </small>
                            </div>
                            <div
                                v-else-if="stream.status == 'diagnostic_crash'"
                            >
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Problém s
                                        diagnostikou
                                    </strong>
                                </small>
                            </div>
                            <div v-else-if="stream.status == 'no_audio'">
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Nepodařilo se
                                        detekovat audio
                                    </strong>
                                </small>
                            </div>
                            <div v-else-if="stream.status == 'issue'">
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Ve streamu se
                                        vyskytl problém
                                    </strong>
                                </small>
                            </div>
                            <div v-else-if="stream.status == 'no_dekrypt'">
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Stream se nedekryptuje
                                    </strong>
                                </small>
                            </div>
                            <div v-else-if="stream.status == 'no_video_bitrate'">
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Video nemá datový tok
                                    </strong>
                                </small>
                            </div>
                            <div v-else-if="stream.status == 'no_audio_bitrate'">
                                <small :class="`${stream.color}--text`">
                                    <strong>
                                        {{ stream.created_at }} => Audio nemá datový tok
                                    </strong>
                                </small>
                            </div>
                            <div v-else-if="stream.status == 'sheduler_disable'">
                                <small class="green--text">
                                    <strong>
                                        {{ stream.created_at }} => Aplikováno automatická vypnutí alertů
                                    </strong>
                                </small>
                            </div>
                            <div v-else-if="stream.status == 'sheduler_enable'">
                                <small class="green--text">
                                    <strong>
                                        {{ stream.created_at }} => Aplikováno automatická zapnutí alertů
                                    </strong>
                                </small>
                            </div>
                        </div>
                    </v-timeline-item>
                </v-timeline>
            </v-container>
            <v-container v-else>
                <div>
                    <v-alert dense text type="info">
                        <strong>Není evidována žádná historie u streamu</strong>
                    </v-alert>
                </div>
            </v-container>
        </v-card>
    </v-main>
</template>
<script>
export default {
    data: () => ({
        streamHistory: [],
        streamId: null
    }),

    created() {
        this.getStreamHistory();
    },
    methods: {
        getStreamHistory() {
            window.axios
                .post("streamInfo/history/10", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    this.streamHistory = response.data;
                });
        }
    },

    mounted() {
        setInterval(
            function() {
                try {
                    this.getStreamHistory();
                } catch (error) {}
            }.bind(this),
            5000
        );
    },
    watch: {
        $route(to, from) {
            this.streamHistory = [];
            this.streamId = null;
            this.getStreamHistory();
        }
    },
};
</script>
