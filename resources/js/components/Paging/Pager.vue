<template>
    <div class="text-center">
        <button class="btn btn-sm btn-outline-primary mx-1 mt-1" title="First"
            :class="{'d-none': !buttons.first }" @click="gotoFirst()">
            <span class="fas fa-fw fa-angle-double-left"></span>
        </button>

        <button class="btn btn-sm btn-outline-primary mx-1 mt-1" title="Previous"
            :class="{'d-none': !buttons.prev }" @click="gotoPrev()">
            <span class="fas fa-fw fa-angle-left"></span>
        </button>

        <span class="px-1 text-gray-600 mt-1" :class="{'d-none': !buttons.leftDiv }">
            ...
        </span>

        <button v-for="i in buttons.numbers" :key="i" @click="gotoPage(i)"
            class="btn btn-sm mx-1 mt-1 number-button" :title="'Page ' + i"
            :class="'btn-' + (current == i ? '' : 'outline-') + 'primary'">
            <span class="">{{ i }}</span>
        </button>

        <span class="px-1 text-gray-600 mt-1" :class="{'d-none': !buttons.rightDiv }">
            ...
        </span>

        <button class="btn btn-sm btn-outline-primary mx-1 mt-1" title="Next"
            :class="{'d-none': !buttons.next }" @click="gotoNext()">
            <span class="fas fa-fw fa-angle-right"></span>
        </button>

        <button class="btn btn-sm btn-outline-primary mx-1 mt-1" title="First"
            :class="{'d-none': !buttons.last }" @click="gotoLast()">
            <span class="fas fa-fw fa-angle-double-right"></span>
        </button>

    </div>
</template>

<style scoped>
    .number-button {
        min-width: 32px;
    }
</style>

    
<script>
    
    export default {
        name: 'Pager',
        props: {
            current: {
                default: 1,
                type: Number
            },
            total: {
                default: 1,
                type: Number
            }
        },
        watch: {
            current() {
                this.propsChanged();
            },

            total() {
                this.propsChanged();
            }
        },
        data() {
            return {
                paging: {
                    current: 1,
                    total: 1,
                },
                buttons: {
                    first: false,
                    prev: false,
                    leftDiv: false,
                    numbers: [1],
                    rightDiv: false,
                    next: false,
                    last: false
                },
                treshold: 5,
                show: 5,
            }
        },
        methods: {
            switchToCurrentPage() {
                let offset = Math.floor((this.show - 1) / 2);
                let collapse = (this.total > this.treshold);
                let hasMoreLeft = (this.paging.current > (offset + 1));
                let hasMoreRight = (this.paging.current < this.total - (this.show - (offset + 1)));

                this.buttons.first = collapse && hasMoreLeft;
                this.buttons.prev = collapse && (this.paging.current > 1);
                this.buttons.leftDiv = collapse && hasMoreLeft;

                let start = this.paging.current - offset;
                start = (start < 1 ? 1 : start);
                let end = start + this.show;

                if (end > (this.total + 1)) {
                    end = this.total + 1;
                    start = end - this.show;
                    start = (start < 1 ? 1 : start);
                }

                this.buttons.numbers = [];
                for (let i = start; i < end; i++)
                    this.buttons.numbers.push(i);
                    
                this.buttons.rightDiv = collapse && hasMoreRight;
                this.buttons.next = collapse && (this.paging.current < this.total);
                this.buttons.last = collapse && hasMoreRight;

                this.paging.current = this.current;
                this.paging.total = this.total;
                this.$emit('pager-page-changed', this.paging);
            },

            requestPageChange(page) {
                this.$emit('pager-request-page-change', page);
            },

            propsChanged() {
                this.paging.current = this.current;
                this.switchToCurrentPage();
            },

            gotoFirst() {
                this.requestPageChange(1);
            },

            gotoPrev() {
                let page = (this.paging.current < 2 ? 1 : this.paging.current - 1);
                this.requestPageChange(page);
            },

            gotoPage(page) {
                this.requestPageChange(page);
            },

            gotoNext() {
                let page = this.paging.current + 1;
                page = (page > this.total ? this.total : page);
                this.requestPageChange(page);
            },

            gotoLast() {
                this.requestPageChange(this.total);
            },
        },
        beforeMount() {
        },
        mounted() {
            this.switchToCurrentPage();
        }
    }
</script>
