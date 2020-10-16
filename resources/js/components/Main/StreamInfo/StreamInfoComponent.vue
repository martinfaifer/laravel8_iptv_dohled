<template>
    <v-main class="mt-12">
        <v-container>
            <!-- náhled na kanál -> komponent img -->
            <v-row no-gutters>
                <v-col class="col-3">
                    <img-component :streamId="id"></img-component>
                    <doku-component :streamId="id"></doku-component>
                </v-col>

                <!-- konec náhledu na kanál -->
                <v-spacer></v-spacer>
                <v-col class="col-5">
                    <ts-component :streamId="id"></ts-component>
                </v-col>
                <v-spacer></v-spacer>
                <v-col class="col-4">
                    <history-component :streamId="id"></history-component
                ></v-col>
            </v-row>
        </v-container>
    </v-main>
</template>
<script>
import ImgComponent from "./StreamInfoImageComponent";
import TsComponent from "./StreamInfoTSComponent";
import HistoryComponent from "./StreamInfoHistoryComponent";
import DokuComponent from "./StreamInfoApiDokuComponent";
export default {
    data() {
        return {
            id: ""
        };
    },

    created() {
        this.loadId();
    },
    components: {
        "img-component": ImgComponent,
        "ts-component": TsComponent,
        "history-component": HistoryComponent,
        "doku-component": DokuComponent
    },
    methods: {
        loadId() {
            let currentObj = this;
            this.id = currentObj.$route.params.id
        }
    },

    mounted() {},
    watch: {
        $route(to, from) {
            this.loadId();
        }
    }
};
</script>
