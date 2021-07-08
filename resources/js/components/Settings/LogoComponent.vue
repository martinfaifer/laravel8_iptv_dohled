<template>
    <v-main>
        <v-container fluid>
            <v-col cols="12" sm="12" md="12" lg="12">
                <v-alert type="info" outlined text>
                    Zde je možnost nahrát logo společnosti
                </v-alert>
            </v-col>

            <v-col cols="12" sm="12" md="12" lg="12">
                <v-card class="mr-15" flat>
                    <v-card-text color="white">
                        <v-col cols="12">
                            <v-file-input
                                label="Logo společnosti"
                                @change="selectFile"
                            ></v-file-input>
                        </v-col>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn text color="success" @click="upload()">
                                nahrát
                            </v-btn>
                        </v-card-actions>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="12" lg="12" md="12" v-if="logos != null">
                <v-card flat class="mr-15">
                    <v-data-table
                        v-if="logos != null"
                        :headers="headers"
                        :items="logos"
                    >
                        <template v-slot:item.logo_path="{ item }">
                            <v-img
                                :lazy-src="item.logo_path"
                                max-height="120"
                                max-width="120"
                                :src="item.logo_path"
                            ></v-img>
                        </template>
                        <template v-slot:item.action="{ item }">
                            <v-btn @click="deleteThis()" icon>
                                <v-icon small color="red">mdi-delete</v-icon>
                            </v-btn>
                        </template>
                    </v-data-table>
                </v-card>
            </v-col>
        </v-container>
    </v-main>
</template>
<script>
export default {
    data() {
        return {
            file: "",
            logos: null,
            snackbar: false,
            headers: [
                {
                    text: "Logo",
                    value: "logo_path"
                },
                { text: "Akce", value: "action" }
            ]
        };
    },

    created() {
        this.logo();
    },
    methods: {
        selectFile(event) {
            this.file = event;
        },
        upload() {
            const formData = new FormData();
            formData.append("logo", this.file, this.file.name);
            axios.post("system/logo", formData).then(response => {
                this.$store.state.alerts = response.data.alert;
                this.file = "";
                this.logo();
            });
        },
        logo() {
            axios.get("system/logo").then(response => {
                this.logos = response.data;
            });
        },
        deleteThis() {
            axios.delete("system/logo").then(response => {
                this.$store.state.alerts = response.data.alert;
                this.$store.state.logo = null;
                this.logos = null;
            });
        }
    },
    watch: {
        $route(to, from) {}
    }
};
</script>
