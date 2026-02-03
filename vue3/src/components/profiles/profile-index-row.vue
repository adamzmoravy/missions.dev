<template>

    <div :class="{noclicks: noclicks}">
        <div class="w-100 h-100 mt-2 d-flex position-relative" id="profilescontainerdiv" ref="profilescontainerdiv">
            <template v-for="profile in visibleProfiles" :key="profile.user_id">
                <div :style="{maxWidth: profileSize + 'px', marginRight: marginRight}"
                    style="display:inline-block;text-align:center;line-height:1;"
                    v-if="profile.user_id">

                                <ui-a :href="'/profile/'+ profile.user_id + '/'" 
                                :style="{fontSize: (profileSize/7) + 'px'}">

                                    <profile-image-simple :width="profileSize" :height="profileSize" :profile="profile">
                                        <template #icon>
                                            <reactions-icon class="reaction-icon-component" v-if="getReactionFromProfileId(profile.user_id)" :mode="'selected'" :reaction="getReactionFromProfileId(profile.user_id)" :size="profileSize / 2.1 + 'px'" />
                                        </template>
                                    </profile-image-simple>
                                </ui-a>

                        <ui-a v-if="showName"
                            class="elipsis"
                            :href="'/profile/'+ profile.user_id + '/'"
                            :style="{fontSize: (profileSize/4.5) + 'px'}">
                                {{$truncate(profile.first_name, 15)}}
                        </ui-a>
                </div>
            </template>
            <div 
                v-if="hiddenProfiles > 0"
                v-tooltip="$trans('Show all profiles')"
                :style="{width: profileSize + 'px', height: profileSize + 'px', marginRight: marginRight}"
                class="hidden-profiles-number small hidden-profiles-number-more"
                @click="$emit('show-all-profiles')">
                +{{ hiddenProfiles }}
                <ui-a v-if="showName" :style="{fontSize: (profileSize/6) + 'px', lineHeight: 1}">&nbsp;</ui-a>
            </div>
        </div>
    </div>

</template>

<style scoped>
        .hidden-profiles-number {
            display:flex;
            aspect-ratio: 1/1;
            text-align:center;
            color:rgb(156, 156, 156);
            background:white;
            border:1px solid black;
            border-radius: 50%;
            border-color:rgb(156, 156, 156);
            justify-content: center;
            align-items: center;
            cursor:pointer;
        }
    .elipsis {
        display:inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .reaction-icon-component {
        position: absolute;
        bottom: 0;
        right: 0;
        z-index: 1;
    }

    .noclicks {
        pointer-events: none;
    }

</style>

<script>
import reactionsIcon from '../reactions-icon.vue'
import { useResizeObserver } from '@vueuse/core'

export default {

    name: 'profile-index-row',
    data() {
        return {
            localUi: {
                userPopupId: null
            },
            containerSize: null,
        }
    },
    components: {
        reactionsIcon
    },
    computed: {
        allProfiles: function () {
            let profileIds = this.profiles.map(profile => profile.user_id)

            let allProfiles = this.$stores.profiles.getProfilePreviewsByIds(profileIds, false)
            return allProfiles

        },
        visibleProfiles: function () {
            return this.allProfiles.slice(0, this.showNumProfiles)
        },
        showNumProfiles: function () {
            return Math.min(this.maxShowNumProfiles, this.fittingProfilesInContainer)
        },
        fittingProfilesInContainer: function () {
            return Math.floor(this.containerSize / (this.profileSize *  1.2))
        },
        hiddenProfiles: function() {
            return this.allProfiles.length - this.visibleProfiles.length
        },
        newTabOrNot() {
            if (this.openProfileInNewTab) {
                return '_blank'
            } else {
                return '_self'
            }
        },
        marginRight: function () {
            if (this.stackProfiles) {
                return (-this.profileSize / 5) + 'px'
            } else {
                return (this.profileSize / 7) + 'px'
            }
        },
    },
    watch: {
        fittingProfilesInContainer: function (newCount) {
            this.$emit('profiles-fit', this.fittingProfilesInContainer)
        }
    },
    methods: {
            handleResize ({ width, height }) {
                this.containerSize = width
            },
            getReactionFromProfileId: function(profileId) {
                if (this.userReactions) {
                    // find in this.userReactions where profileId is in the list
                    let reaction = this.userReactions.find (reaction => {
                        return reaction.profileId == profileId
                    })
                    if (reaction) {
                        return reaction.reaction
                    } else {
                        return null
                    }

                } else {
                    return null
                }
            }
    },
    props: {
        maxShowNumProfiles: {
            type: Number,
            default: 8
        },
        userReactions: {
            default: null
        },
        stackProfiles: {
            type: Boolean,
            default: false
        },
        showName: {
            type: Boolean,
            default: false
        },
        profileSize: {
            type: Number,
            default: 50
        },
        profiles: {
            type: Array,
            required: true
        },
        noclicks: {
            type: Boolean,
            default: false
        },
    },
    mounted: function () {
        useResizeObserver(this.$refs.profilescontainerdiv, (entries) => {
            this.handleResize(entries[0].contentRect)
        })
    }

}

</script>