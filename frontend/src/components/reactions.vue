<template>
    <div>
        <div class="d-flex">
            <a class="react-link" @click="showReactionOptionsSet = !showReactionOptionsSet" v-if="mode=='subtle'">{{ $trans('React') }}</a>
            <div class="reacted-reactions" v-if="reactionsCount">
                <a @click="showReactionProfiles = true" class="reacted-link">
                    <reactions-icon :reaction="reacted" class="reacted-icon" :mode="'selected'" :key="reacted" v-for="reacted in reactedReactionTypes" />
                    <div class="reactions-count">{{ reactionsCount }} reactions</div>
                </a>
                </div>

        </div>
        <div class="reactions-container" v-if="showReactionOptionsSet || mode == 'full'">
            <div v-for="(type, name) in reactionTypes" class="reaction-button" @click="isReacted(name) ? unreact(name): react(name)">
                <reactions-icon :size="'25px'" :reaction="name" :mode="isReacted(name) ? 'selected' : 'unselected'"  style="margin-right:5px;"/>
                 <span class="reaction-name">{{ $trans('reaction_' + name)  }}</span>

            </div>
        </div>

     <entities :title="$trans('Reactions')" :entities="profileEntitiesWithReactions" :modal-only="true" @close="showReactionProfiles = false" v-if="showReactionProfiles" />

    </div>


</template>

<style scoped>
.react-link {
    font-size: 85%;
    font-weight:600;
    cursor: pointer;
    padding-bottom:7px;
    margin-right:1px;
}


.reactions-container {
    padding-top:5px;
    padding-bottom:3px;
    border-top: 1px solid #cccccc7b;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    width: 100%;

}

.reacted-reactions {
    padding-left:4px;
    font-size:90%;
    padding-bottom:7px;

}

.reactions-count {
    margin-left:5px;
    position:relative;
    top:-1px;


}

.reaction-name {
    position:relative;
    top:-1px;
}
.reacted-link {
    vertical-align: text-top;
    display: flex;
    align-items: center;
    margin-bottom:3px;
}

.reaction-button {
    font-size:85%;
    cursor:pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 20%;
    padding-left:5px;
    padding-right:5px;
    padding-top:5px;
    padding-bottom:5px;
}

.reaction-button:hover {
    background-color: rgb(238, 238, 238);
}


@media screen and (max-width: 768px) {
    .reaction-button {
        font-size:60%;
    }
}


@media screen and (max-width: 350px) {
    .reaction-name {
        display:none;
    }
}

/* first element of reacted-icon */

.reacted-icon:first-child {
    margin-left:0px;
}
.reacted-icon {
    margin-left:-7px;
}

.reaction-button:hover .reaction-icon {
    opacity: 1;
}


</style>


<script>

import reactionsIcon from './reactions-icon.vue'
import backend from '../backend.js'
export default {
    name: 'reactions',
    data() {
        return {
            showReactionOptionsSet: false,
            reactions: {},
            showReactionProfiles: false,
        }
    },
    components: {
        reactionsIcon
    },
    methods: {
        getReactionFromProfileId: function(profileId) {
            if (this.userReactions) {
                // find in this.userReactions where profileId is in the list
                let reaction = this.userReactions.find (reaction => {
                    return reaction.profileId == profileId
                })
                return reaction.reaction
            } else {
                return null
            }
        },
        getReactionNameOfProfileId (profileId) {
            // return the reaction name of the profileId in this.profileIdsWithReactions
            let reaction = this.profileIdsWithReactions.find (reaction => {
                return reaction.profileId == profileId
            })
            return reaction.reaction
        },
        unreact: function(reaction) {
            this.doUnReact()
            backend.delete('/comments/' + this.commentId + '/reaction/',
                {
                    data: {
                        reaction: reaction
                    }
                }
            ).then((response) => {

            })
        },
        react(reaction) {
            this.doUnReact()
            this.doReact(reaction)
            this.showReactionOptionsSet = false
            backend.post('/comments/' + this.commentId + '/reaction/',
                {
                    reaction: reaction
                }
            ).then((response) => {

            })

        },
        doUnReact() {
            for (let reaction in this.reactions) {
                let currentReactions = this.reactions[reaction]
                let index = currentReactions.indexOf(this.currentUserId.toString())
                if (index > -1) {
                    currentReactions.splice(index, 1)

                    if (currentReactions.length == 0) {
                        delete this.reactions[reaction]
                    } else {
                        this.reactions[reaction] = currentReactions
                    }

                }

            }
        },
        doReact(reaction) {
            if (!this.reactions[reaction]) {
//                this.$set(this.reactions, reaction, [])
                this.reactions[reaction] = []
            }
            let currentReactions = this.reactions[reaction]
            currentReactions.push(this.currentUserId.toString())
            this.reactions[reaction] = currentReactions

        },
        isReacted(reaction) {
            if (!this.currentUserId) {
                return false
            }
            if (this.reactions[reaction] && this.reactions[reaction].includes(this.currentUserId.toString())) {
                return true
            }
            return false
        }
    },
    computed: {
        profileEntities: function() {
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
                        value: this.reactionsProfileIds
                    }
                ])
        },
        reactionsProfileIds: function() {
            let ids = []
            for (let reaction in this.reactions) {
                ids = ids.concat(this.reactions[reaction])
            }
            return ids
        },
        profileEntitiesWithReactions: function() {
                let profileEntities = this.profileEntities
                let profilesEntitiesWithReactions = []

                // loop through profileEntities and add reactions property from ths.profileIdsWithReactions
                for (let i = 0; i < profileEntities.length; i++) {
                    let profileEntity = profileEntities[i]
                    let profileId = profileEntity.id
                    let reaction = this.getReactionNameOfProfileId(profileId)
                    profileEntity.reactions = [reaction]
                    profilesEntitiesWithReactions.push(profileEntity)
                }

                return profilesEntitiesWithReactions
        },
        profileIdsWithReactions: function () {
            // return this.reactionsProfileIds with this.getReactionNameOfProfileId() as value

            let ids = []
            for (let reaction in this.reactions) {
                let currentReactions = this.reactions[reaction]
                for (let i = 0; i < currentReactions.length; i++) {
                    let profileId = currentReactions[i]
                    ids.push({
                        profileId: profileId,
                        reaction: reaction
                    })
                }
            }
            return ids
        },
        reactedReactionTypes: function() {
            let reactions = Object.keys(this.reactions)
            // order reactions by reactions count
            reactions.sort((a, b) => {
                return this.reactions[b].length - this.reactions[a].length
            })
            // sort reactions alphabetically
            reactions.sort((a, b) => {
                if (this.reactions[b].length == this.reactions[a].length) {
                    return a.localeCompare(b)
                }
            })
            return reactions
        },
        reactionsCount: function() {
            // count all reactions
            let count = 0
            for (let reaction in this.reactions) {
                count += this.reactions[reaction].length
            }
            return count
        }
    },
    watch: {
        reactionsFromComment: {
            handler: function (val, oldVal) {
                // clone object val to this.reactions (this.reactions is a reactive object)
                this.reactions = JSON.parse(JSON.stringify(val))
            },
            immediate: true
        }
    },
    props: {
        mode: {
            type: String,
            required: false,
            default: 'full'
        },
        currentUserId: {

        },
        commentId: {
            type: String,
            required: true
        },
        reactionsFromComment: {            
        },
        reactionTypes: {
            required: true,
            type: Object
        },
    }
}

</script>