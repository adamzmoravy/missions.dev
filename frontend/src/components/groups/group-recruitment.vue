<template>

    <ui-panel class="bg-white p-3" v-if="group">

        <h2 class="large">Monthly lunch subscription</h2>
        <p>
            <slot>{{ callout }}</slot>
        </p>

        <div class="participants">

            <template v-for="n in targetParticipants" :key="n">
                <profile-image-simple class="participant" v-if="group.registrations[n - 1]" :profile="$stores.profiles.getProfilePreviewById(group.registrations[n - 1])" />
                <div v-else class="participant"><span>{{ n }}</span></div>
            </template>
        </div>

        <p v-if="targetParticipants >= group.registrations.length" style="text-align:center;">We are missing <strong>{{ targetParticipants - group.registrations.length }} participants</strong> to reach the goal. Click "join now" below to take part.</p>

        <p class="already-registered" v-if="currentUserIsRegistered"><strong>{{$trans('You are already registered')}}</strong></p>

        <div class="call-to-action mb-2">

            <div>

                <ui-button v-if="!currentUserIsRegistered" :href="'/group/' + groupId + '/'" size="lg" button-type="action">Join now</ui-button>
                <ui-button v-else :href="'/group/' + groupId + '/'" size="lg">View</ui-button>
            </div>
        </div>

    </ui-panel>


</template>

<style scoped>
    .call-to-action {
        display:flex;
        justify-content: center;

    }

    .already-registered {
        text-align: center;
    }

    .participants {
        margin-bottom:20px;
        margin-left:15px;
        display: flex;
        flex-wrap: wrap;
        gap: 17px;
    }
    .participant {
        display: flex;
        justify-content: center;
        align-items: center;
        width:40px;
        height:40px;
        border-style:solid;
        border-width:1px;
        border-color:#dddddd;
        border-radius: 50%;
    }
</style>


<script>


export default {
    name: 'group-recruitment',
    props: {
        groupId: { 
            type: String,
            required: true
        },
        targetParticipants: {
            type: Number,
            default: 1
        },
        callout: {
            type: String,
            default: ''
        }
    },
    computed: {
        group() {
            return this.$stores.groups.getGroupPreview(this.groupId)
        },
        currentUserIsRegistered: function () {
            if (this.group && this.$currentUser) {
                return this.group.registrations.includes(this.$currentUser.user_id)
            }
            return false
        }        
    },
    data() {
        return {
        isRecruiting: false
        }
    },
    methods: {
        toggleRecruitment() {
        this.isRecruiting = !this.isRecruiting
        }
    }
}

</script>