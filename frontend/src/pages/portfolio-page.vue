<template>


        <div>

            <ui-page-header>

                <div>
                    <h1 class="x-large mt-2">{{ $trans('Portfolio') }}</h1>
                    <h2 class="large mt-2">
                        {{ $trans('portfolio_page_subheading') }}
                    </h2>

                </div>

                <ui-tabs class="mt-3" v-on:update:section="ui.showSection = $event" :tabs="[{display: 'Portfolio', value: 'portfolio'}]" :current-selection="'portfolio'" inline-template>
                    <div class="pl-0">
                        <div v-for="tab in tabs" class="tab-wrapper mr-3">
                            <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link mr-2" @click="$emit('update:section', tab.value)">

                                <span v-if="false">
                                <img v-if="currentSelection === tab.value" :src="'/img/checkbox-yellow.svg'">
                                    <img v-if="currentSelection !== tab.value" :src="'/img/checkbox.svg'">
                                </span>
                                {{tab.display}}</a>
                        </div>
                    </div>

                </ui-tabs>

            </ui-page-header>

            <ui-page-body>

                <div>
                    <ui-button button-type="new" size="md" @click="$stores.ui.setNewSubmission({stage: 9})">+ {{$trans('Add new')}}</ui-button>
                </div>
            
            </ui-page-body>
            <ui-cards-container>
                <div class="row">
                    <template v-for="submission in portfolio">
                        <submission-preview v-if="portfolio.length > 0" :key="submission.data.experiment_id" :experiment="submission.data" />
                    </template>
                    <div class="col-lg-12" v-if="portfolio.length == 0"></div>
                </div>
            </ui-cards-container>

        </div>

</template>

<script>

export default {
    name: "portfolio-page",
    data() {
        return {
            ui: {}
        };
    },
    computed: {
        portfolio() {
            let entities = this.$stores.submissions.getEntities([
                {
                    sorter: 'created',
                    direction: 'asc'
                }
            ],
            [{
                selection: 'stages',
                value: [9]
            }])

            return entities
        }
    },
    components: {  }
}

</script>