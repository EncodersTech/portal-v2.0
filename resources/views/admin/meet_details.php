<?php
/*
Template Name: Meet Details
*/
$MEETS_DEBUG = false;
define('MEETS_DEBUG', $MEETS_DEBUG);
define('WP_DEBUG', MEETS_DEBUG);
define('WP_DEBUG_DISPLAY', MEETS_DEBUG);
define('WP_DEBUG_LOG', MEETS_DEBUG);


define('MEETS_CONFIG', [
    'portal_base' => 'https://app.allgymnastics.com',
    'api_base' => 'https://app.allgymnastics.com/api/',
    'api_timeout' => 30000,
]);

if (MEETS_DEBUG) {

}

// function meets_custom_head() {
    echo '<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>';
    echo '<link rel="stylesheet" href="https://unpkg.com/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">';
    echo '<script src="https://unpkg.com/lodash@4.17.20/lodash.min.js"></script>';
    echo '<script src="https://unpkg.com/moment@2.29.1/moment.js"></script>';
    echo '<script src="https://unpkg.com/axios@0.21.0"></script>';
    echo '<script src="https://unpkg.com/js-cookie@3.0.0-rc.1/dist/js.cookie.min.js"></script>';
    echo '<script src="https://unpkg.com/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>';
    echo '<script src="https://kit.fontawesome.com/f510b948bf.js" crossorigin="anonymous"></script>';
    echo '<script src="https://unpkg.com/vue@2.6.12' . (MEETS_DEBUG ? '' : '/dist/vue.min.js') . '"></script>';
    echo '<script src="https://unpkg.com/vuejs-datepicker"></script>';
// }
// add_action('wp_head', 'meets_custom_head');

// get_header();

define('MEET_ID', filter_input(INPUT_GET, 'meet', FILTER_VALIDATE_INT, [
        'options' => [
            'default' => null,
            'min_range' => 0,
        ],
    ])
);
?>

<div id="main-content">
    <div class="container py-3">
        <div id="app">
            <div class="small" v-if="isLoading">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                </span> Loading, please wait ...
            </div>

            <div v-else-if="errorMessage !== null" class="alert alert-danger">
                <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
                <div class="mt-1" v-html="errorMessage"></div>
            </div>

            <div v-else-if="meets !== null" style="display: inline-flex;" >
                <div v-for="meet in meets" class="mr-2">
                    <div class="meet-header">
                        <div class="meet-logo">
                            <img alt="Meet Picture" :src="meet.profile_picture" title="Meet Picture">
                        </div>
                        <div class="meet-info d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="meet-name">{{ meet.name }}</h5>
                                <p class="meet-dates">
                                    {{ meet.start_date_display }} — {{ meet.end_date_display }}
                                </p>
                                <p class="meet-description">{{ meet.description }}</p>
                            </div>
                            <div>
                                <a :href="config.base + '/meet-details?meet=' + meet.id " class="btn btn-sm btn-info" target="_blank" style="width: fit-content">
                                    <span class="fas fa-external-link-alt"></span>
                                    Details
                                </a>
                                <a :href="config.base + '/meets/' + meet.id " class="btn btn-sm btn-success" target="_blank" style="width: fit-content">
                                    <span class="fas fa-external-link-alt"></span>
                                    Register
                                </a>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- #main-content -->

<!-- function meets_custom_footer() { -->
    <style>
        .clickable {
            cursor: pointer;
        }

        .meet-header {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray);
        }

        .meet-logo {
            margin-right: 15px;
        }

        .meet-logo>img {
            height: 250px;
        }

        .meet-name {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .meet-dates {
            color: red;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .meet-description {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .meet-tabs {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
	        justify-content: center;
            margin-top: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--gray);
            border-radius: 5px;
	        overflow: hidden;
        }

        .meet-tab {
            text-align: center;
            flex-grow: 1;
            padding: 15px 10px;
            background-color: var(--dark);
            color: var(--light);
            border-bottom: 1px solid var(--gray);
        }

        .meet-tab:hover {
            background-color: var(--gray);
        }

        .meet-tab.active {
            background-color: var(--danger);
        }

        .meet-detail {

        }

        .meet-detail-title {
            font-weight: bold;
        }

        .meet-detail-value {

        }

        .levels-body {
            margin-top: 15px;
            margin-bottom: 5px;
            padding: 5px;
            border-radius: 5px;
            background-color: var(--dark);
            color: var(--light);
            font-weight: bold;
        }

        .levels-category {
            margin-left: 15px;
            margin-bottom: 5px;
            padding: 5px;
            border-radius: 5px;
            background-color: var(--cyan);
            color: var(--light);
            font-weight: bold;
        }

        .levels-level {
            margin-bottom: 5px;
            margin-left: 30px;
        }
    </style>

    <script type='text/javascript'>
        window.Moment = moment;
        window.Utils = {
            toInt(val, radix) {
                radix = (radix === undefined ? 10 : radix);
                let result = parseInt(val, radix);
                return (isNaN(result) ? null : result);
            },
            toFloat(val) {
                let result = parseFloat(val);

                if (isNaN(result) || !isFinite(result))
                    return null;

                return result;
            },
            refresh() {
                return location.reload(true);
            },
            getRandomInt(max) {
                return Math.floor(Math.random() * Math.floor(max));
            },

            remove(array, element) {
                const index = array.indexOf(element);

                if (index !== -1)
                    array.splice(index, 1);
            },

            numberFormat(n, dp) {
                try {
                    dp = (dp !== undefined ? dp : 2);
                    let fee = Utils.toFloat(n);
                    return (fee === null ? n : fee.toFixed(dp));
                } catch (e) {
                    return n;
                }
            },

        };

        const App = new Vue({
            el: '#app',
            components: {
            },
            data() {
                return {
                    debug: '<?=(MEETS_DEBUG ? 'true' : 'false')?>',
                    isLoading: false,
                    errorMessage: null,
                    config: {
                        base: '<?=MEETS_CONFIG['portal_base']?>',
                        api: {
                            base: '<?=MEETS_CONFIG['api_base']?>',
                            timeout: '<?=MEETS_CONFIG['api_timeout']?>',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'key' : 'Vp0VJlQaSxUUku9kNAxLlz5eQqZqdMnVnKQmIsHBdPoGt0SJE3HGbK3pT2zbon5NUM0u9WawNEOGCMBX',
                                'Access-Control-Allow-Origin': 'http://127.0.0.1:8000',
                                'Vary': 'Origin'

                            },
                        },
                    },
                    axios: null,
                    api: {
                        meet: '/meets',
                    },
                    meet_id: <?=(173 ?? 'null' )?>,

                    meets: null,
                    bodies: {},
                    view: 1,
                }
            },
            watch: {
            },
            computed: {
                constants() {
                    return {
                        views: {
                            MEET_DETAILS: 1,
                            COMPETITION: 2,
                            LEVELS: 3,
                            ATTACH: 4,
                            CONTACT: 5,
                        },
                        status: {
                            CLOSED: 1,
                            OPEN: 2,
                            LATE: 3,
                            SOON: 4,
                        },
                        bodyNames: {
                            1: 'USAG',
                            2: 'USAIGC',
                            3: 'AAU',
                            4: 'NGA'
                        }
                    };
                },
            },
            methods: {
                number_format(n) {
                    return Utils.numberFormat(n, 2);
                },

                changeView(v) {
                    this.view = v;
                },

                showError(error) {
                    console.error(error);
                    var msg = '';
                    if (error.response) {
                        msg = error.response.data.message;
                    } else if (error.request) {
                        msg = 'No server response';
                    } else if (error.message){
                        msg = error.message;
                    } else {
                        msg = error;
                    }
                    this.errorMessage = msg + '<br/>Please reload this page', 'c.';
                },
            },
            beforeMount() {
                this.isLoading = true;

                this.axios = axios.create({
                    baseURL: this.config.api.base,
                    timeout: this.config.api.timeout,
                    headers: this.config.api.headers,
                });
            },
            async mounted() {
                try {
                    this.isLoading = true;

                    if (this.meet_id === null)
                        throw {message: 'Invalid meet id.'};
                    var query = '?featured=1';
                    result = await this.axios.get(this.api.meet + query);
                    if (result.data.meets.length == 0)
                        throw {message: 'No meet matches this criteria.'};

                    var date_format = 'MMM Do, YYYY';
                    var meets = [];
                    result.data.meets.forEach(function (meet) {
                        //console.log(meet.id);
                        meet.start_date = Moment(meet.start_date);
                        meet.start_date_display = meet.start_date.format(date_format);
                        meet.end_date = Moment(meet.end_date);
                        meet.end_date_display = meet.end_date.format(date_format);

                        meet.registration_start_date = Moment(meet.registration_start_date);
                        meet.registration_start_date_display = meet.registration_start_date.format(date_format);

                        meet.registration_end_date = Moment(meet.registration_end_date);
                        meet.registration_end_date_display = meet.registration_end_date.format(date_format);
                        meets.push(meet);
                    });
                    Vue.set(this, 'meets', meets);
                } catch (error) {
                    this.showError(error);
                } finally {
                    this.isLoading = false;
                }
            },
        });
    </script>
<!-- } -->