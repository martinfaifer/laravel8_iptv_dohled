<template>
    <v-main>
        <transition name="slide-fade" mode="out-in">
            <v-snackbar
                v-if="alert != null"
                :timeout="5000"
                :value="true"
                top
                right
                dark
                elevation-12
                border="left"
                class="pt-12"
                transition="slide-x-transition"
            >
                <v-row class="ml-1">
                    <v-icon
                        v-if="alert.status === 'success'"
                        :color="alert.status"
                    >
                        mdi-check-circle
                    </v-icon>

                    <v-icon
                        v-if="alert.status === 'error'"
                        :color="alert.status"
                    >
                        mdi-close
                    </v-icon>

                    <v-icon
                        v-if="
                            alert.status === 'warning' ||
                                alert.status === 'info'
                        "
                        :color="alert.status"
                    >
                        mdi-exclamation
                    </v-icon>
                    <span
                        class="text--secondary ml-12 body-1"
                        v-html="alert.msg"
                    ></span>
                </v-row>
            </v-snackbar>
        </transition>
    </v-main>
</template>

<script>
export default {
    computed: {
        alert() {
            return this.$store.state.alerts;
        }
    },
    data() {
        return {};
    },

    watch: {
        alert() {
            if (this.alert != null) {
                setTimeout(
                    function() {
                        this.$store.state.alerts = null;
                    }.bind(this),
                    4000
                );
            } else {
                return;
            }
        }
    }
};
</script>
