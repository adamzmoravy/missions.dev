<template>

    <div class="icon-circle icon-reacted" :class="mode" :style="{backgroundColor:color, width: size, height: size}">
        <img :src="mode == 'selected' ? reactionIcon.iconSelected : reactionIcon.icon" class="reaction-icon" />
    </div>

</template>


<style scoped>
.icon-circle {
    border: 1px solid #ccccccb7;
    border-radius: 90%;
    display: flex;
    justify-content: center;
    align-items: center;
    aspect-ratio:1;

}


.selected .reaction-icon {
    opacity: 1;
}

.selected:hover .reaction-icon {
    opacity: 0.7;
}

.reaction-icon {
    opacity: 0.7;
    height:60%;
    max-width:60%;    
}


.reaction-icon {
}

</style>

<script>

export default {
    name: 'reactions-icon',
    computed: {
        reactionIcon: function() {
            return this.reactionTypes[this.reaction]
        },
        color: function() {
            if (this.mode == 'selected') {
                return this.reactionIcon.color
            }
        },
        reactionTypes: function () {
            let commentReactions = this.$ui.commentReactions
            let submissionReactions = this.$ui.submissionReactions
            let genericReactions = this.$ui.genericReactions
            return Object.assign({}, commentReactions, submissionReactions, genericReactions)
    }
    },
    props: {
        mode: {
            required: false,
            default: 'unselected'
        },
        size: {
            required: false,
            default: '18px'
        },
        reaction: {
            required: true,
            type: String
        }
    }
}

</script>