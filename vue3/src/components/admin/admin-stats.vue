<template>
    <div class="container pt-4">

        <div class="admin-stats-controls">
            <details>
                <summary class="date-summary">
                    <span v-if="dateStart || dateEnd">
                        {{ dateStart || 'Start' }} — {{ dateEnd || 'End' }}
                    </span>
                    <span v-else>
                        Select date range
                    </span>
                </summary>

                <div class="date-selector" style="padding:8px 0;">
                    <div style="display:flex;gap:12px;align-items:center;">
                        <label style="display:flex;flex-direction:column;">
                            Start date
                            <input type="date" v-model="dateStart" />
                        </label>

                        <label style="display:flex;flex-direction:column;">
                            End date
                            <input type="date" v-model="dateEnd" />
                        </label>
                    </div>

                    <div style="margin-top:8px;display:flex;gap:8px;">
                        <ui-button button-type="action" size="sm" type="button" @click="applyDates">
                            Apply
                        </ui-button>
                        <ui-button size="sm" type="button" @click="dateStart = null; dateEnd = null">
                            Clear
                        </ui-button>
                    </div>
                </div>
            </details>
        </div>

        <div class="mt-4 admin-stats-results">
            <div v-for="stat in stats" class="admin-stat-entry mb-3">
                <h3>{{ stat.day}}: {{ getProfilePreviewIds(stat.ping.users).length }}</h3>

                <!--
                        <profile-index-row
                        
                            :profile-size="30"
                            :max-show-num-profiles="20" :profiles="getProfilePreviews(stat.ping.users)"></profile-index-row>
-->
                        <entities :title="$trans('Members')" :entities="getProfileEntities(getProfilePreviewIds(stat.ping.users))" :limit="8" />

            </div>
        </div>
</div>


</template>

<script>

import backend from '../../backend.js'
export default {
    name: 'admin-stats',
    data() {
        return {
            stats: [],
            dateStart: null,
            dateEnd: null
        }
    },
    computed: {
    },
    methods: {
        getProfileEntities(profileIds) {
            return this.$stores.profiles.getEntities([
                {
                    sorter: 'random'
                },
                {
                    sorter: 'profile_pic'
                }
            ],
        [
            {
                selection: 'ids',
                value: profileIds
            }
        ])
        },
        getProfilePreviewIds(users) {
            if (!users) return []
            if (Array.isArray(users)) return users
            if (users && typeof users === 'object') return Object.keys(users)
            return []
        },
        getProfilePreviews(users) {
            const ids = this.getProfilePreviewIds(users)
            return this.$stores.profiles.getProfilePreviewsByIds(ids)
        },
        getStatsForDate(date) {
            backend.get('/platform/stats/' + date + '/').then((response) => {

                if (response?.data?.stats) {
                    if (Array.isArray(response.data.stats)) {
                        this.stats.push(...response.data.stats)
                    } else {
                        this.stats.push(response.data.stats)
                    }
                }
                
            })
        },
        applyDates() {
            this.stats = []
            let start = this.dateStart
            let end = this.dateEnd

            if (!start && !end) return
            if (!start) start = end
            if (!end) end = start

            let s = new Date(start)
            let e = new Date(end)
            if (s > e) [s, e] = [e, s]

            for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
                const dateStr = d.toISOString().slice(0, 10)
                this.getStatsForDate(dateStr)
            }

        }

    },
    components: {
        
    },
    mounted() {
        const today = new Date().toISOString().slice(0, 10)
        this.getStatsForDate(today)
    }
}

</script>