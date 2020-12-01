<template>
    <v-main>
        <v-row>
            <div v-if="history != null">
                <!-- tabulka s výpisem posledních 10 záznamů -->
                <v-card flat color="transparent elevation-0">
                    <div class="text-center">
                        <span class="body-2">
                            Posledních 10 záznamů z historie
                        </span>
                    </div>
                    <v-data-table
                        :headers="headers"
                        :items="history"
                        flat
                        dense
                        class="elevation-0 mt-2"
                    >
                        <template v-slot:item.data="{ item }">
                            <span
                                v-if="item.data == 'no_audio_bitrate'"
                                class="red--text"
                            >
                                Není datový tok na audiu
                            </span>
                            <span
                                v-if="item.data == 'no_video_bitrate'"
                                class="red--text"
                            >
                                Není datový tok na videu
                            </span>
                            <span
                                v-if="item.data == 'no_dekrypt'"
                                class="red--text"
                            >
                                Stream se nedekryptuje
                            </span>
                            <span v-if="item.data == 'issue'" class="red--text">
                                Ve streamu se vyskytl problém
                            </span>
                            <span
                                v-if="item.data == 'no_audio'"
                                class="red--text"
                            >
                                Nepodařilo se detekovat audio
                            </span>
                            <span
                                v-if="item.data == 'diagnostic_crash'"
                                class="red--text"
                            >
                                Problém s diagnostikou
                            </span>
                            <span
                                v-if="item.data == 'stream_error'"
                                class="red--text"
                            >
                                Stream nefunguje
                            </span>
                            <span
                                v-if="item.data == 'transporterrors_warning'"
                                class="red--text"
                            >
                                Objevily se TS chyby
                            </span>
                            <span
                                v-if="item.data == 'scrambledPids_warning'"
                                class="red--text"
                            >
                                Změněno pořadí Pidů
                            </span>
                            <span
                                v-if="item.data == 'stream_ok'"
                                class="green--text"
                            >
                                Stream je v pořádku
                            </span>
                        </template>
                    </v-data-table>
                </v-card>
            </div>
            <div v-else>
                <v-alert text type="info">
                    Neexistuje žádná historie
                </v-alert>
            </div>
        </v-row>
    </v-main>
</template>
<script>
export default {
    data() {
        return {
            history: null,
            headers: [
                {
                    text: "Stream",
                    align: "start",
                    value: "nazev"
                },
                {
                    text: "Data",
                    value: "data"
                },
                {
                    text: "vytvořeno",
                    value: "created_at"
                }
            ]
        };
    },

    created() {
        this.loadHistory();
    },
    methods: {
        loadHistory() {
            window.axios.get("history").then(response => {
                if (response.data.status == "empty") {
                    this.history = null;
                } else {
                    this.history = response.data;
                }
            });
        },
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadHistory();
            }.bind(this),
            60000
        );
    }
};
</script>
