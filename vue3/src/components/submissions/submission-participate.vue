<template>
    <div>


        <div class="d-flex mb-4 participants-container">

            <profile-index-row 
                class="w-100"
                :profile-size="40"
                :max-show-num-profiles="8"
                :show-name="false"
                :profiles="profiles"
                :user-reactions="participantIdsWithReactions"
                @show-all-profiles="showAllParticipants = true"
        
            />

            <entities 
                :title="$trans('Reactions')" 
                :entities="profileEntitiesWithReactions" 
                :modal-only="true" 
                @close="showAllParticipants = false" 
                v-if="showAllParticipants" />


        </div>
        <hr class="mt-0">
        <div class="participate-container medium">

            <template v-for="(reaction, key) in reactionTypes">
                <div class="reactions-field" @click="toggle(key)">
                    <div class="icon-container pr-2">
                        <reactions-icon class="reaction-icon-component" :mode="localUserParticipations.includes(key) ? 'selected' : 'unselected'" :reaction="key" :size="'30px'" />
                    </div>
                    <div class="participate-label">
                        {{ $trans(reaction.label) }}
                    </div>
                </div>

            </template>

        </div>

    </div>
</template>


<style scoped>


.reactions-field {
    cursor:pointer;
    display:flex;
}

.reactions-field:hover .participate-label {
    text-decoration: underline;
}

.participate-container {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-top: 1rem;
    margin-bottom: 1rem;
}


.icon-circle {
    border: 1px solid #ccccccb7;
    border-radius: 90%;
    display: flex;
    justify-content: center;
    align-items: center;
    aspect-ratio: 1;
}

.participants-container {
    gap:0.4rem;    
}
.participant {
    position:relative;
    margin-bottom:0rem;
}
.participate-reactions-icon {
    width:1.4rem;
    height:1.4rem;
    position:absolute;
    z-index:1;
    text-align: center;
    margin-left: auto;
    margin-right: auto;
    left: 0;
    right: 0;
    bottom:-0.5rem;
}
.reactions-profile-image {
    width:40px;
    height:40px;
}

.icon-container {
    display: inline-flex;
    align-items: center;
    height:1.6em;
}


@media screen and (max-width: 1100px) {
    .participate-container {
        flex-direction: column;
        gap:1rem;
        align-items: start;

    }
}

.icon {
    opacity:0.6;
}
.hands {
    opacity: 1!important;
}
.reaction-button {
    margin-top:0.5rem;
    padding:0.5rem;
    padding-top:0.8rem;
    border-style:solid;
    border-width:1px;
    border-color:white;
    border-radius:10px;
}
.reaction-button:hover {
    border-color:lightgrey;
}
</style>

<script>

    import reactionsIcon from '../reactions-icon.vue'
    export default {
        name: 'submission-participate',
        data() {
            return {
                iconSize: 2.5,
                responseStatus: {},
                localUserParticipations: [],
                showAllParticipants: false
            }
        },
        components: {
            reactionsIcon
        },
        props: {

            experiment: {
                required: true,
                type: Object
            },
            submissionInteractor: {
                required: true
            },
            myRelationships: {
                required: true,
                type: Array
            }
        },
        mixins: [
        ],
        methods: {
            toggle: function(type) {
                let action
                if (this.userParticipations.includes(type)) {
                    // remove type from localUserParticipations
                    this.localUserParticipations = this.localUserParticipations.filter((item) => {
                        return item !== type
                    })
                    action = this.submissionInteractor.unparticipate(type)
                } else {
                    // add type to localUserParticipations
                    this.localUserParticipations.push(type)
                    action = this.submissionInteractor.participate(type)
                }
                action.then(() => {
                    this.$emit('participation-updated')
                })
            }
        },
        computed: {
            reactionTypes: function () {
                return this.$ui.submissionReactions
            },
            participants: function () {
                return this.submissionInteractor.participants.value
            },
            profiles: function () {
                return this.$stores.profiles.getProfilePreviewsByIds(this.participantsIds)
            },
            participantsIds: function () {
                return Object.keys(this.participants)
            },
            profileEntities: function() {
                return this.$stores.profiles.getEntities([
                    ],
                    [
                        {
                            selection: 'ids',
                            value: this.participantsIds
                        }
                    ])
            },
            profileEntitiesWithReactions: function() {
                let profileEntities = this.profileEntities
                let profilesEntitiesWithReactions = []

                // loop through profileEntities and add reactions property
                for (let i = 0; i < profileEntities.length; i++) {
                    let profileEntity = profileEntities[i]
                    let profileId = profileEntity.id
                    let reactions = this.participants[profileId]
                    profileEntity.reactions = reactions
                    profilesEntitiesWithReactions.push(profileEntity)
                }
                return profilesEntitiesWithReactions
            },
            participantIdsWithReactions: function () {
                
                // convert participants object to array
                let participants = []
                for (let participant in this.participants) {
                    participants.push({
                        profileId: participant,
                        reactions: this.participants[participant],
                        reaction: this.participants[participant][0]
                    })
                }
                return participants

            },
            userParticipations: function () {
                if (this.$currentUser) {
                    return this.submissionInteractor.userParticipations(this.$currentUser.user_id)
                } else {
                    return []
                }
            }
        },
        mounted: function() {
            this.localUserParticipations = this.userParticipations
        }
    }
</script>
