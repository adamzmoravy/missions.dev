<template>


    <div>

        <ui-page-header>

            <div>
                <h1 class="x-large mt-2">{{ $trans('Guide') }}</h1>
                <h2 class="large mt-2">
                    {{ $trans('guide_page_subheading') }}
                </h2>
            </div>
            <ui-tabs class="mt-3" v-on:update:section="ui.showSection = $event" :tabs="[{display: 'Knowledge', value: 'knowledge'}]" :current-selection="'knowledge'" inline-template>
                    <div v-for="tab in tabs" class="tab-wrapper mr-3">
                        <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link mr-2" @click="$emit('update:section', tab.value)">

                            <span v-if="false">
                            <img v-if="currentSelection === tab.value" :src="'/img/checkbox-yellow.svg'">
                                <img v-if="currentSelection !== tab.value" :src="'/img/checkbox.svg'">
                            </span>
                            {{tab.display}}</a>
                    </div>

            </ui-tabs>

        </ui-page-header>


        <ui-page-body>

            <div>
                <ui-button button-type="new" size="md" @click="$stores.ui.setNewSubmission({stage: 6})">+ {{$trans('Add new')}}</ui-button>
            </div>

        </ui-page-body>


        <ui-cards-container>
                <div class="row">
                    <template v-for="submission in guides">
                        <submission-preview v-if="guides.length > 0" :key="submission.data.experiment_id" :experiment="submission.data" />
                    </template>
                    <div class="col-lg-12" v-if="guides.length == 0"></div>
                </div>
        </ui-cards-container>



    </div>

</template>

<script>


export default {
name: "guide",
data() {
    return {
        ui: {},
        showAddIssue: false
    }
},
computed: {
    guides() {
        let entities = this.$stores.submissions.getEntities([
            {
                sorter: 'created',
                direction: 'asc'
            }
        ],
        [{
            selection: 'stages',
            value: [6]
        }])

        return entities
    }
},
methods: {
    setup: function() {
//        this.$refs.submissions.setup()
    },
},
}

</script>