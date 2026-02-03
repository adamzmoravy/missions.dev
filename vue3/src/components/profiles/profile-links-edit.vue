<template>

<div class="profile-links-list mb-4" v-if="profileLinks">
    <div 
        v-for="(link, index) in profileLinks" 
        :key="index" 
        class="d-flex align-items-center mb-2 link-item"
        draggable="true"
        @dragstart="dragStart(index, $event)"
        @dragover.prevent="dragOver(index, $event)"
        @drop="drop(index, $event)"
        @dragend="dragEnd"
        :class="{'dragging': draggedIndex === index, 'drag-over': dragOverIndex === index}"
    >
        <div class="drag-handle">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <circle cx="5" cy="4" r="1.5"/>
                <circle cx="5" cy="8" r="1.5"/>
                <circle cx="5" cy="12" r="1.5"/>
                <circle cx="11" cy="4" r="1.5"/>
                <circle cx="11" cy="8" r="1.5"/>
                <circle cx="11" cy="12" r="1.5"/>
            </svg>
        </div>
        <div class="favicon-container">

            <img v-if="link.metadata?.favicon" :src="'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb/' + link.metadata.favicon" alt="Link image" @error="$event.target.src='/img/external-link.svg'" />
            <img v-else src="/img/external-link.svg" alt="External link" />

        </div>
        <div class="flex-grow-1">
            <a :href="link.url" target="_blank" rel="noopener noreferrer">{{ link.title }}</a>
        </div>
        <div class="link-actions">
            <button class="btn-icon" :disabled="index === 0" @click="moveLink(index, -1)" title="Move up">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 3l-5 5h10z"/>
                </svg>
            </button>
            <button class="btn-icon" :disabled="index === profileLinks.length - 1" @click="moveLink(index, 1)" title="Move down">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 13l5-5H3z"/>
                </svg>
            </button>
            <ui-button size="sm" button-type="cancel" @click="profileLinks.splice(index, 1); $emit('update:links', profileLinks)">Remove</ui-button>
        </div>
    </div>
    <div v-if="profileLinks.length === 0" class="text-muted">No links added yet.</div>
</div>

<label>URL</label>


<div class="get-url-container" :class="{'disabled': newLinkMetadata !== null}">

    <ui-input-url
        @keydown.enter="fetchLinkMetadata"
        v-model="newLinkUrl"
        :placeholder="'https://example.com'"
        @valid-state="validStateChange"
        class="input-url"
        :disabled="newLinkMetadata !== null"
    />

    <ui-button size="sm" :working="fetching" :disabled="isButtonDisabled || newLinkMetadata !== null" @click="fetchLinkMetadata">Get url</ui-button>

</div>

<div v-if="newLinkMetadata">
<div class="d-flex mt-2">
    <div class="favicon-container">
        <img :src="'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb/' +newLinkMetadata.favicon" v-if="newLinkMetadata.favicon" alt="Link image" @error="$event.target.src='/img/external-link.svg'" />
        <img v-else src="/img/external-link.svg" alt="External link" />

    </div>
        <ui-input-text v-model="newLinkName" :placeholder="'Link title'" placeholder="Site name" />
    </div>
    <ui-button size="md" button-type="new" class="mt-3" @click="addLink">Add link</ui-button>
    <ui-button size="md" button-type="cancel" class="mt-3 ml-2" @click="newLinkMetadata = null">Cancel</ui-button>
</div>


</template>

<style scoped>
.disabled {
    pointer-events: none;
    opacity: 0.6;
}

.link-item {
    background: var(--background-secondary, #f8f9fa);
    padding: 8px;
    border-radius: 4px;
    transition: opacity 0.2s, background 0.2s;
}

.link-item.dragging {
    opacity: 0.5;
}

.link-item.drag-over {
    background: var(--background-hover, #e9ecef);
    border-top: 2px solid var(--primary, #007bff);
}

.drag-handle {
    cursor: grab;
    margin-right: 8px;
    color: #999;
    display: flex;
    align-items: center;
}

.drag-handle:active {
    cursor: grabbing;
}

.link-actions {
    display: flex;
    gap: 4px;
    align-items: center;
}

.btn-icon {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 3px;
}

.btn-icon:hover:not(:disabled) {
    background: rgba(0, 0, 0, 0.1);
    color: #333;
}

.btn-icon:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.favicon-container {
    margin-right: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 4px;
}
.favicon-container img {
    max-height: 50%;
}

.get-url-container {

    display:flex;
    gap: 8px;
}

.input-url {
    flex: 1;
}

</style>

<script>

import backend from '../../backend.js'

export default {
    name: "ProfileLinksEdit",
    data() {
        return {
            newLinkUrl: '',
            newLinkValid: false,
            newLinkMetadata: null,
            newLinkName: '',
            fetching: false,
            profileLinks: null,
            draggedIndex: null,
            dragOverIndex: null
        }
    },
    watch: {
        profileLinks: {
            handler(newLinks, oldLinks) {
                if (oldLinks != null) {
                    backend.post('/users/me/links/', {
                        links: newLinks
                    }).then(response => {
                        this.$emit('saved')
                    }).catch(error => {
                        // Handle error
                    })
                }            
            },
            deep: true
        }
    },
    methods: {
        dragStart(index, event) {
            this.draggedIndex = index
            event.dataTransfer.effectAllowed = 'move'
            event.dataTransfer.setData('text/html', event.target.innerHTML)
        },
        dragOver(index, event) {
            this.dragOverIndex = index
        },
        drop(index, event) {
            event.preventDefault()
            if (this.draggedIndex !== null && this.draggedIndex !== index) {
                const item = this.profileLinks.splice(this.draggedIndex, 1)[0]
                this.profileLinks.splice(index, 0, item)
                this.$emit('update:links', this.profileLinks)
            }
        },
        dragEnd() {
            this.draggedIndex = null
            this.dragOverIndex = null
        },
        moveLink(index, direction) {
            const newIndex = index + direction
            if (newIndex < 0 || newIndex >= this.profileLinks.length) {
                return
            }
            const item = this.profileLinks.splice(index, 1)[0]
            this.profileLinks.splice(newIndex, 0, item)
            this.$emit('update:links', this.profileLinks)
        },
        addLink() {
            const newLink = {
                url: this.newLinkUrl,
                title: this.newLinkName,
                metadata: this.newLinkMetadata
            }
            this.profileLinks.push(newLink)
            this.$emit('update:links', this.profileLinks)
            this.newLinkUrl = ''
            this.newLinkMetadata = null
            this.newLinkName = ''
        },
        fetchLinkMetadata() {
            this.fetching = true
            backend.get('/page/metadata/?url=' + encodeURIComponent(this.newLinkUrl)).then(response => {
                console.log(response.data)
                this.newLinkMetadata = response.data.metadata
                this.newLinkName = response.data.metadata.site_name ?? response.data.metadata.title ?? ''
                this.fetching = false
            }).catch(error => {
                this.newLinkMetadata = {
                    error: 'Failed to fetch metadata'
                }
                this.newLinkName = ''
                this.fetching = false
            })
        },
        validStateChange(isValid) {
            this.newLinkValid = isValid
        }
    },
    computed: {
        isButtonDisabled() {
            return !this.newLinkValid
        }
    },
    props: {
        profile: {
            type: Object,
            required: true
        },
        saveOnDestroy: {
            type: Boolean,
            default: false
        }
    },
    mounted() {
        this.profileLinks = this.profile.links ? JSON.parse(JSON.stringify(this.profile.links)) : []
    }

}

</script>