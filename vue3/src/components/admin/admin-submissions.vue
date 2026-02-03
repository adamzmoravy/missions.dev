<template>
    <div class="container-fluid pt-4">

    
        <div :table-data="experiments">

            <div class="d-inline-block">

                <!--
                <div class="mb-0 mt-3 d-flex justify-content-end">
                    <img class="admin-search-img" src="/img/search.png">
                    <input @focus="$parent.fetchExperiments"
                        v-model="searchQuery" class="mb-4"
                        placeholder="Search for experiment" type="text">
                </div>
                -->
                <!--

                <div class="d-flex justify-content-between pb-3">

                    <div>
                        <export-excel
                        :data   = "tableData" name="submissions"
                        :fields="{
                            'Title': 'title',
                            'Description': 'short_description',
                            'id': 'experiment_id',
                            'Comments': 'comment_count',
                            'Care about this': 'like_count',
                            'Want updates': 'updates_count',
                            'Want to help': 'help_count'
                        }">
                        <a class="text-primary font-weight-normal">Download as excel</a>
                        </export-excel>                        
                    </div>

                    <div class="d-flex align-items-start">
                        <h6 class="mb-2 mr-2">{{$trans('Filter by type')}}:</h6>

                        <div v-for="(stageName, index) in $ui.stageHandles" v-if="stageName" class="form-group form-check mb-1 ml-2">
                            <input type="checkbox" class="form-check-input" :id="stageName" v-model="filterBy" :value="{stage: parseInt(index)}">
                            <label class="form-check-label" :for="stageName">{{ $trans(stageName)}}</label>
                        </div>
                        
                    </div>

                </div>
                                -->

                <table class="table-hover">
                    <thead>

                        <th class="admin-table-header sortable"
                            v-bind:class="{sortable: true, active: 'title' === activeField}"
                            @click="changeSortField({name: 'title'})">

                            <div>
                                 {{ $trans('Title') }} <span v-if="'title' === activeField">
                                            <span v-if="sortField.direction === 'asc'">
                                                ▼

                                            </span>
                                            <span v-else="sortField.direction === 'desc'">
                                                ▲
                                            </span>
                                    </span>
                            </div>
                        </th>


                        <th class="admin-table-header sortable"
                            v-bind:class="{sortable: true, active: 'short_description' === activeField}"
                            @click="changeSortField({name: 'short_description'})">

                            <div>
                                {{ $trans('Description') }} <span v-if="'short_description' === activeField">
                                                <span v-if="sortField.direction === 'asc'">
                                                    ▼

                                                </span>
                                                <span v-else="sortField.direction === 'desc'">
                                                    ▲
                                                </span>
                                            </span>
                            </div>
                        </th>

                        <th class="admin-table-header unsortable">

                            <div>
                                {{ $trans('Emails') }}
                            </div>
                        </th>



                        <th class="admin-table-header sortable"
                            v-bind:class="{sortable: true, active: 'stage' === activeField}"
                            @click="changeSortField({name: 'stage'})">

                            <div>
                                {{ $trans('Stage') }} <span v-if="'stage' === activeField">
                                            <span v-if="sortField.direction === 'asc'">
                                                ▼

                                            </span>
                                            <span v-else="sortField.direction === 'desc'">
                                                ▲
                                            </span>
                                    </span>
                            </div>
                        </th>

                        <th class="admin-table-header sortable"
                            v-bind:class="{sortable: true, active: 'like_count' === activeField}"
                            @click="changeSortField({name: 'like_count'})">

                            <div>
                                {{ $trans('Hearts') }} <span v-if="'like_count' === activeField">
                                            <span v-if="sortField.direction === 'asc'">
                                                ▼

                                            </span>
                                            <span v-else="sortField.direction === 'desc'">
                                                ▲
                                            </span>

                                    </span>
                            </div>
                        </th>

                        <th class="admin-table-header sortable"
                            v-bind:class="{sortable: true, active: 'updates_count' === activeField}"
                            @click="changeSortField({name: 'updates_count'})">

                            <div>
                               {{ $trans('Updates') }} <span v-if="'updates_count' === activeField">
                                            <span v-if="sortField.direction === 'asc'">
                                                ▼

                                            </span>
                                            <span v-else="sortField.direction === 'desc'">
                                                ▲
                                            </span>

                                    </span>
                            </div>
                        </th>


                        <th class="admin-table-header sortable"
                            v-bind:class="{sortable: true, active: 'help_count' === activeField}"
                            @click="changeSortField({name: 'help_count'})">

                            <div>
                                {{ $trans('Help') }} <span v-if="'help_count' === activeField">
                                            <span v-if="sortField.direction === 'asc'">
                                                ▼

                                            </span>
                                            <span v-else="sortField.direction === 'desc'">
                                                ▲
                                            </span>

                                    </span>
                            </div>
                        </th>


                        <th class="admin-table-header sortable"
                            v-bind:class="{sortable: true, active: 'comment_count' === activeField}"
                            @click="changeSortField({name: 'comment_count'})">

                            <div>
                                {{ $trans('Comments') }} <span v-if="'comment_count' === activeField">
                                            <span v-if="sortField.direction === 'asc'">
                                                ▼

                                            </span>
                                            <span v-else="sortField.direction === 'desc'">
                                                ▲
                                            </span>
                                        </span>
                            </div>
                        </th>

                        <th class="admin-table-header sortable"
                            v-bind:class="{sortable: true, active: 'interactions_total' === activeField}"
                            @click="changeSortField({name: 'interactions_total'})">

                            <div>
                                {{ $trans('Interactions') }} <span v-if="'interactions_total' === activeField">
                                            <span v-if="sortField.direction === 'asc'">
                                                ▼

                                            </span>
                                            <span v-else="sortField.direction === 'desc'">
                                                ▲
                                            </span>
                                        </span>
                            </div>
                        </th>

                        <th class="admin-table-header sortable"
                            v-bind:class="{sortable: true, active: 'experiment_id' === activeField}"
                            @click="changeSortField({name: 'experiment_id'})">

                            <div>
                                ID <span v-if="'experiment_id' === activeField">
                                        <span v-if="sortField.direction === 'asc'">
                                            ▼

                                        </span>
                                        <span v-else="sortField.direction === 'desc'">
                                            ▲
                                        </span>
                                </span>
                            </div>
                        </th>



                    </thead>
                    <tr v-for="experiment in displayedData">
                        <td class="border p-1"><ui-a class="text-primary font-weight-normal"
                            :href="'/submission/' + experiment.experiment_id + '/'">{{experiment.title}}</ui-a></td>
                        <td class="border p-1"><span class="elipsis">{{experiment.short_description}}</span></td>
                        <td class="border"> <input class="team-emails" readonly type="text"
                                                :value="experiment.team_emails.join(',\n')"></td>
                        <td class="border p-3">{{$ui.stageHandles[experiment.stage]}}</td>
                        <td class="border text-center p-3">{{experiment.like_count}}</td>
                        <td class="border text-center">{{experiment.updates_count}}</td>
                        <td class="border text-center">{{experiment.help_count}}</td>
                        <td class="border text-center">{{experiment.comment_count}}</td>
                        <td class="border text-center">{{experiment.interactions_total}}</td>
                        <td class="border text-center p-3">{{experiment.experiment_id}}</td>
                        <td class="pl-3">
                            <button v-if="experiment.show_in.index"
                                    @click="unpublishExperiment(experiment.experiment_id)"
                                    class="btn btn-danger btn-block" type="submit" name="button">
                                {{ $trans('Unpublish') }}
                            </button>

                            <button v-if="!experiment.show_in.index"
                                    @click="publishExperiment(experiment.experiment_id)"
                                    class="btn btn-success btn-block" type="submit" name="button">
                                {{ $trans('Publish') }}
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script>

import backend from '../../backend.js'
export default {
    props: {
    },
    mounted() {
        this.fetchExperiments()
    },
    methods: {
        fetchExperiments() {
            backend.get('/experiments/admin/en').then((response) => {
                this.experiments = response.data.previews
                this.currentData = this.experiments
                this.displayedData = this.currentData
                this.currentDisplay = 'experiments'
            })
        },
        unpublishExperiment(experimentId) {
            if (!confirm(this.$trans('Are you sure you want to remove this submission?'))) {
                return
            }
            backend.delete('/experiments/' + experimentId + '/').then((response) => {
                this.fetchExperiments()
            })
        }
    },
    data() {
        return {
            activeField: '',
            experiments: []
        }
    },
}
</script>

<style lang="css" scoped>
    .elipsis {
        display:block;
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
