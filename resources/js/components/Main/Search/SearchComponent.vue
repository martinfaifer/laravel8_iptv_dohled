<template>
    <v-main>
        <v-dialog v-model="searchDialogInput" width="500">
            <v-card class="pt-3">
                <v-card-text>
                    <v-text-field
                        :loading="loading"
                        autofocus
                        label="Vyhledávání"
                        v-model.lazy="model"
                    ></v-text-field>
                    <v-list v-if="results.length > 0">
                        <v-list-item
                            v-for="result in results"
                            :key="result.id"
                            link
                            :to="result.url"
                            @click="close_search()"
                        >
                            <v-list-item-avatar>
                                <v-img :src="result.logo"></v-img>
                            </v-list-item-avatar>
                            <v-list-item-content>
                                <v-list-item-title v-html="result.nazev">
                                </v-list-item-title>
                                <v-list-item-subtitle
                                    v-html="result.description"
                                ></v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- filter dialog -->

        <v-dialog v-model="filterDialog" width="1000">
            <v-card height="800">
                <v-card-text>
                    <v-row>
                        <v-col sm="12" md="4" lg="4">
                            <v-autocomplete
                                autofocus
                                :items="itemsForFilter"
                                v-model="filter"
                                hide-no-data
                                hide-selecte
                                placeholder="Vyberte filtr ... "
                                prepend-inner-icon="mdi-magnify"
                                @change="searchForCurrentFilter()"
                            >
                            </v-autocomplete>
                        </v-col>
                        <v-col sm="12" md="8" lg="8">
                            <v-autocomplete
                                :items="filterData"
                                v-model="filterByFilter"
                                hide-no-data
                                placeholder="Vyberte data ... "
                                multiple
                                @change="findByFilter()"
                            >
                            </v-autocomplete>
                        </v-col>
                    </v-row>

                    <v-row v-if="filteredResult != null">
                        <v-col cols="12">
                            <v-text-field
                                v-model="searchInTable"
                                prepend-inner-icon="mdi-magnify"
                                label="Vyhledat v tabulce ... "
                                single-line
                                dense
                                hide-details
                            ></v-text-field>
                        </v-col>
                    </v-row>
                    <v-row v-if="filteredResult != null">
                        <v-col cols="12">
                            <v-data-table
                                :headers="headers"
                                :items="filteredResult"
                                :search="searchInTable"
                            >
                                <template v-slot:item.img="{ item }">
                                    <div
                                        v-if="
                                            item.img != false ||
                                                item.img != false
                                        "
                                    >
                                        <v-img
                                            :lazy-src="item.img"
                                            max-height="24"
                                            max-width="24"
                                            :src="item.img"
                                        ></v-img>
                                    </div>
                                    <div v-if="item.icon != 'false'">
                                        <v-icon>
                                            {{ item.icon }}
                                        </v-icon>
                                    </div>
                                </template>

                                <template v-slot:item.akce="{ item }">
                                    <v-btn small icon link :to="item.url">
                                        <v-icon small>
                                            mdi-arrow-right
                                        </v-icon>
                                    </v-btn>
                                </template>
                            </v-data-table>
                        </v-col>
                    </v-row>
                    <v-row v-if="filteredResult == null" class="text-center">
                        <v-col cols="12">
                            <span class="title">
                                Zde se zobrazí výsledky
                            </span>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-main>
</template>

<script>
export default {
    data() {
        return {
            filterDialog: false,
            itemsForFilter: ["tag", "satelit", "zařízení"],
            filter: null,
            filterData: [],
            filterByFilter: null,
            filteredResult: null,
            results: [],
            searchInTable: "",
            headers: [
                {
                    text: "",
                    value: "img"
                },
                {
                    text: "",
                    value: "name"
                },
                {
                    text: "",
                    value: "akce"
                }
            ],
            searchDialogInput: false,
            descriptionLimit: 60,
            entries: [],
            isLoading: false,
            loading: false,
            model: null,
            search: null
        };
    },
    created() {},
    methods: {
        searchDialog() {
            this.model = null;
            this.searchDialogInput = true;
        },

        fn_search() {
            this.loading = true;
            axios
                .post("search", {
                    search: this.model
                })
                .then(response => {
                    this.loading = false;
                    this.results = response.data;
                });
        },
        searchForCurrentFilter() {
            axios
                .post("search/filterData", {
                    filter: this.filter
                })
                .then(response => {
                    this.filterData = response.data;
                });
        },
        findByFilter() {
            axios
                .get(
                    "search/filter/" +
                        this.filter +
                        "/" +
                        btoa(this.filterByFilter)
                )
                .then(response => {
                    this.filteredResult = response.data;
                });
        },
        close_search() {
            this.searchDialogInput = false;
        }
    },
    mounted() {
        var self = this;
        window.addEventListener("keyup", function(event) {
            // vyhledáváaní za pomocí stistknutí ¨
            if (event.keyCode == 220) {
                self.searchDialog();
            }
        });
    },
    watch: {
        model(after, before) {
            this.fn_search();
        }
    }
};
</script>
