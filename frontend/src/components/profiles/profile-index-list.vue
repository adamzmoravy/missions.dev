<template>

    <div class="h-100">

        <!--
            <virtual-list v-if="virtual" class="profile-list"
                :estimate-size="80"
                :data-key="'user_id'"
                :data-sources="visibleProfiles"
                :data-component="itemComponent"
            />


            <profile-index-list-item 
                v-if="!virtual && type == 'standard'"  
                v-for="(profile, index) in visibleProfiles" 
                :key="profile.user_id" 
                :source="profile" 
                :index="index"
                :reaction="getReactionFromProfileId(profile.user_id)"
             />
-->
            
             <template v-if="!virtual && type == 'simple'">
                <Item
                    v-for="(profile, index) in visibleProfiles" 
                    :key="profile.user_id" 
                    :source="profile" 
                    :index="index"
                 />
        

                <div @click="$emit('show-all-profiles')" class="list-container-simple clickable-container" v-if="hiddenProfiles > 0">
                    <ui-icon-control :size="30" class="profile-container-simple">
                        +{{ hiddenProfiles }}
                    </ui-icon-control>
                    <div class="profile-text">
                        <a v-if="!isUserEditor" class="small-2">Show all</a>
                        <a v-else class="small-2">Show and edit</a>
                    </div>
                </div>

                <div @click="$emit('show-all-profiles')" class="list-container-simple clickable-container" v-if="hiddenProfiles <= 0 && isUserEditor">
                    <ui-icon-control :size="30" class="profile-container-simple">
                        ⋮
                    </ui-icon-control>
                    <div class="profile-text">
                        <a class="small-2">Edit</a>
                    </div>
                </div>


             </template>

    </div>

</template>

<style scoped>
    .profile-container-simple {
        margin-right:0.4em;
        flex-grow: 0;
        position:relative;
    }

    .profile-text {
    display:flex;
    align-items:center;
    width:100%;
    flex-grow: 1;
    }

    .profile-list {
        max-height:500px;
        overflow-y: auto;
        width:100%;
        height:100%;
    }

    .list-container-simple {
        margin-bottom:0.6em;
        width:100%;
        display: flex;
        flex-direction: row;
    }

</style>


<script>

import Item from './profile-index-list-item-simple.vue'

export default {
    name: 'profile-index-list',
    data() {
        return {
            itemComponent: Item,
            localUi: {
            }
        }
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
        }
    },
    profiles: {
        Item
    },
    computed: {
        hiddenProfiles: function() {
            return this.allProfiles.length - this.visibleProfiles.length
        },
        newTabOrNot() {
            if (this.openProfileInNewTab) {
                return '_blank'
            } else {
                return '_self'
            }
        }
    },
    props: {
        isUserEditor: {
            required: false,
            type: Boolean,
            default: false
        },
        type: {
            required: false,
            default: 'standard',
            type: String
        },
        alreadyInSelection: {
            required: false,
            type: Array
        },        
        userReactions: {
            default: null
        },
        virtual: {
            type: Boolean,
            default: true
        },
        showName: {
            type: Boolean,
            default: false
        },
        profileSize: {
            type: Number,
            default: 50
        },
        marginRight: {
            type: String,
            default: '0px'
        },
        visibleProfiles: {
            type: Array,
            required: true
        },
        allProfiles: {
            type: Array,
            required: true
        },
        openProfileInNewTab: {
            type: Boolean,
            default: false
        }
    }

}

</script>