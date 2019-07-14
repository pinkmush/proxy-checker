<template>
    <div>
        <v-layout>
            <v-flex
                    xs6
                    offset-xs3
            >
                <h1>Proxy Checker</h1>
            </v-flex>
        </v-layout>
        <v-layout>
            <v-flex
                    xs6
                    offset-xs3
                    v-if="loading"
            >
                <v-progress-linear
                        :indeterminate="true"
                        color="primary"
                ></v-progress-linear>
            </v-flex>

        </v-layout>
        <v-layout>
            <v-flex
                    xs6
                    offset-xs3
            >
        <span
                class="subheading"

        >
          In what format to add a proxy in a proxy-checker?<br><br> If you have public proxies (without login and password), then
          <span style="color: red">IP:PORT</span> <br><br> If you have private proxies (with authorization on login and the password), then
          <span style="color: red">IP:PORT:USER:PASS</span> <br><br>
          <span>Maximum 250 lines.</span> <br><br>
          </span>


                <v-textarea
                        v-model="proxies"
                        ref="export_tasks_json"
                        outline
                        label="Paste proxies"
                ></v-textarea>
                <v-btn
                        :disabled="loading || this.proxies.length === 0"
                        @click="checkProxy"
                >Check Proxy
                </v-btn>
                <v-btn
                        :disabled="loading"
                        @click="clear"
                >Clear
                </v-btn>
                <div>
                    <v-btn
                            :disabled="loading"
                            @click="exportOkModal = !exportOkModal"
                    >Export Success
                    </v-btn>
                    <v-btn
                            :disabled="loading"
                            @click="exportFailModal = !exportFailModal"
                    >Export Failed
                    </v-btn>
                </div>
            </v-flex>
        </v-layout>
        <v-layout v-if="proxiesResponse.length > 0">
            <v-flex
                    xs10
                    offset-xs1
            >
                <v-data-table
                        :headers="headers"
                        :items="proxiesResponse"
                        hide-actions
                        class="elevation-1"
                >
                    <template slot="items" slot-scope="props">
                        <td>
                            <v-icon class="proxyred" :class="{proxygreen: props.item.status === 'check_circle'}">{{
                                props.item.status }}
                            </v-icon>
                        </td>
                        <td>
                            <template v-if="props.item.status === 'block'">
                                {{ props.item.ip }}
                            </template>
                            <template v-if="props.item.status === 'check_circle'">
                                <a class="white-link" :href="'https://iphub.info/?ip=' + props.item.ip" target="_blank">{{
                                    props.item.ip }}</a>
                            </template>


                        </td>
                        <td>{{ props.item.country }}</td>
                        <td>{{ props.item.initialIp }}</td>
                        <td>{{ props.item.port }}</td>
                        <td>{{ props.item.user }}</td>
                        <td>{{ props.item.pass }}</td>
                        <td>{{ props.item.time }}</td>

                    </template>
                </v-data-table>
            </v-flex>
        </v-layout>
        <v-layout
                row
                justify-center
        >
            <v-dialog
                    v-model="exportOkModal"
                    persistent
                    max-width="800px"
            >
                <v-card>
                    <v-card-title>
                        <span class="headline">Export Success Proxies</span>
                    </v-card-title>
                    <v-card-text>
                        <v-textarea
                                v-model="exportOk"
                                outline
                        ></v-textarea>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                                color="blue darken-1"
                                flat
                                @click.native="exportOkModal = false"
                        >Close
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-layout>

        <v-layout
                row
                justify-center
        >
            <v-dialog
                    v-model="exportFailModal"
                    persistent
                    max-width="800px"
            >
                <v-card>
                    <v-card-title>
                        <span class="headline">Export Fail Proxies</span>
                    </v-card-title>
                    <v-card-text>
                        <v-textarea
                                v-model="exportFail"
                                outline
                        ></v-textarea>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                                color="blue darken-1"
                                flat
                                @click.native="exportFailModal = false"
                        >Close
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-layout>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                proxies: '',
                loading: false,
                exportOkModal: false,
                exportFailModal: false,
                message: '',
                headers: [
                    {
                        text: 'Status',
                        align: 'left',
                        value: 'status'
                    },
                    {text: 'Outgoing IP', value: 'ip'},
                    {text: 'Country', value: 'country'},
                    {text: 'IP', value: 'initialIp'},
                    {text: 'Port', value: 'port'},
                    {text: 'User', value: 'user'},
                    {text: 'Pass', value: 'pass'},
                    {text: 'Response time', value: 'time'},

                ],
                proxiesResponse: []
            }
        },
        computed: {
            exportOk() {
                let exportAccs = ''
                this.proxiesResponse.forEach(item => {
                    if (item.status === 'check_circle') {
                        exportAccs += item.full + '\n'
                    }
                })
                return exportAccs

            },
            exportFail() {
                let exportAccs = ''
                this.proxiesResponse.forEach(item => {
                    if (item.status === 'block') {
                        exportAccs += item.full + '\n'
                    }
                })
                return exportAccs

            },
        },
        methods: {
            clear() {
                this.proxies = ''
                this.proxiesResponse = []
            },
            checkProxy() {
                this.loading = true
                let proxyAll = []
                const proxiesLines = this.proxies.split('\n')
                // this.proxies = ''
                let proxies = []
                this.proxiesResponse = []

                function isBlank(str) {
                    return (!str || /^\s*$/.test(str));
                }

                proxiesLines.forEach((item, index) => {
                    if (index > 249) {
                        return
                    }
                    if (isBlank(item)) {
                        return
                    }
                    const split = item.split(':')
                    proxies.push({
                        full: item,
                        ip: split[0],
                        port: split[1],
                        user: split[2] || '-',
                        pass: split[3] || '-'
                    })
                })

                console.log(proxies)

                const makeFetch = proxy => {
                    return fetch(`/check.php?proxy=${proxy.full}&api_token=null`)
                        .then(response => response.json())
                        .then(response => {
                            if (response.message === 'Ok') {
                                this.proxiesResponse.push({
                                    status: 'check_circle',
                                    ip: response.data.ip,
                                    initialIp: proxy.ip,
                                    port: proxy.port,
                                    user: proxy.user,
                                    pass: proxy.pass,
                                    country: response.data.country,
                                    time: response.data.response_time / 1000 + 's',
                                    full: proxy.full
                                })
                            } else {
                                this.proxiesResponse.push({
                                    status: 'block',
                                    ip: 'Failed connect',
                                    country: response.country,
                                    initialIp: proxy.ip,
                                    port: proxy.port,
                                    user: proxy.user,
                                    pass: proxy.pass,
                                    time: response.data.response_time / 1000 + 's',
                                    country: '-',
                                    full: proxy.full
                                })
                            }

                            console.log(response)

                        })
                }

                proxies.forEach((proxy) => {
                    proxyAll.push(makeFetch(proxy))
                })


                const waitFor = (ms) => new Promise(r => setTimeout(r, ms))
                const asyncForEach = async (array, callback) => {
                    for (let index = 0; index < array.length; index++) {
                        await callback(array[index], index, array)
                    }
                };

                const start = async () => {
                    await asyncForEach(proxyAll, async (num) => {
                        await waitFor(50);
                        console.log(num)
                    });
                    console.log('Done')
                };

                start();
                // proxyAll.forEach(item => {
                //     return item()
                // })
                return Promise.all(proxyAll).then(() => {
                    console.log(this.proxiesResponse);
                    this.loading = false
                })
            },
        }
    }
</script>

<style>
    .proxyred {
        color: red !important;
    }

    .proxygreen {
        color: #36ff36 !important;
    }

    .application .theme--dark.v-table, .theme--dark .v-table {
        background-color: #2c343c !important

    }

    .application .theme--dark.v-table tbody tr:hover:not(.datatable__expand-row), .theme--dark .v-table tbody tr:hover:not(.datatable__expand-row) {
        background-color: #212121 !important;
    }
</style>