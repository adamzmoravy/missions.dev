<template>
    <div class="nav-header-search-div d-flex align-items-center w-100 h-100">
        <OnClickOutside @trigger="hideDropdown">
            
            <input type="text"
                autocomplete="off"
                tabindex="0"
                name="nav-header-search"
                class="nav-header-search-input"
                value=""
                :placeholder="$trans('type_to_search')"
                v-model="searchString"
                ref="searchInput"
                @focus="showSearchDropdown=true;$parent.searchFocus=true"
                >

                
                <div class="navbar-search-dropdown"
                    v-show="showSearchDropdown">
                    <entities class="entities" :selected-result="selectedResult" :title="$trans('Entities')" :entities="entities" :limit="999" /> 
                </div>
        </OnClickOutside>

    </div>

</template>

<style scoped>
    .navbar-search-dropdown {
        z-index:99999999;
        width:350px;
        top:70px;
        background: white;
        position: absolute;
        max-height: 80vh;
        overflow-y: scroll;
        border-radius: 0.2em;
        left: 0;
        overflow-x: hidden;
        filter:drop-shadow(0px 1px 4px rgba(0, 0, 0, 0.07));
    }

    @media only screen and (max-width: 800px) {
        .navbar-search-dropdown {
            width: 100%;
            height:100vh;
            max-height: 100vh;
        }
    }


.nav-header-search-input {
    background-color:#f0f2f5;
    border-style:none;
    border-radius:1.5em;
    box-sizing: border-box;
    height:40px;
    width:100%;
    max-width:300px;
    background-image:url('./img/search.png');
    background-repeat: no-repeat;
    background-size: auto 80%;
    padding-left:3.3em;
    background-position: 0.4em center;
    margin-left:0em;
    transition: width 0.2s ease-in-out;

}

.nav-header-search-input:focus {
    background-image:none;
    padding-left:1em;
    border-style:none;
    outline-width: 0;
    margin-left:1em;
    max-width:500px;
}

@media only screen and (max-width: 1000px) {

    .nav-header-search-div {
        width:auto;

    }

    .logo-search-container {
        width:auto;
    }

    .nav-header-search-input {
        width:40px;
        height:40px;
        padding:0em;
        padding-left:38px;
        border-style:solid;
        border-width:1px;
        background-size:75%;
        margin-left:0.5em;
        transition: width 0.2s ease-in-out;
     }


    .nav-header-search-input:focus {
        width:100%;
        background-image:none;
        border-style:none;
        outline-width: 0;
        margin-left:1em;
        max-width:500px;
    }
}



.nav-header-search-div {
}

</style>

<script>

import { OnClickOutside } from '@vueuse/components'
import headerMenuItem from './header-menu-item.vue'

import { useProfilesStore } from '../../stores/profiles'
import { useAuthStore } from '../../stores/auth'
import { mapStores } from 'pinia'

export default {
    props: ['common'],
    components: {
        headerMenuItem
    },
    watch: {
        $latestEvent: function() {
            if (this.$latestEvent.eventName === 'new-page-navigation') {
                this.hideDropdown()
                this.$refs.searchInput.blur()
            }
        }
    },
    components: {
        OnClickOutside
    },
    mounted() {

        window.addEventListener('keydown', (e) => {
            if (!this.showSearchDropdown) {
                return false
            }

            if (e.key === 'ArrowDown') {
                if (this.selectedResult < this.entities.length - 1) {
                    this.selectedResult++
                } else {
                    this.selectedResult = 0
                }
            } else if (e.key === 'ArrowUp') {
                if (this.selectedResult > 0) {
                    this.selectedResult--
                }
            } else if (e.key === 'Enter') {
                if (this.entities[this.selectedResult]) {
                    this.$router.push('/' + this.$lang + this.entities[this.selectedResult].link)
                    this.$parent.searchFocus=false
                    this.selectedResult = 0

                }
            } else
            if (e.key === 'Escape') {
                this.hideDropdown()
            }
        })
    },
    data() {
        return {
            searchString: '',
            searchResults: {},
            showSearchDropdown: false,
            selectedResult: 0
        }
    },
    methods: {
        hideDropdown() {
            this.selectedResult = 0
            this.$parent.searchFocus=false
            this.showSearchDropdown = false
            this.searchString = ''
        },
        saveSearchToLocalStorage(toSave, type) {
            var localStorageData = {users: [], experiments: []}
            localStorageData = JSON.parse(localStorage.getItem('recentSearches')) || {users: [], experiments: []}
            if (type === 'user') {
                if (!localStorageData.users.includes(toSave.user_id)) {
                    localStorageData.users.push(toSave.user_id)
                }
            } else if (type === 'experiment') {
                if (!localStorageData.experiments.includes(toSave.experiment_id)) {
                    localStorageData.experiments.push(toSave.experiment_id)
                }
            }
            localStorage.setItem('recentSearches', JSON.stringify(localStorageData))
        }
    },
    computed: {

        ...mapStores(useProfilesStore, useAuthStore),
        entities: function () {
            // getSortedEntities
            return this.$stores.entities.getSortedEntities(
                [{
                sorter: 'random',
                direction: 'asc'
            }],    
            [{
                selection: 'search',
                value: this.searchString
            }])
        },
        recentSearches() {
            if (this.searchString === '') {
                return JSON.parse(localStorage.getItem('recentSearches'))
            }
        }
    }
}
</script>

<style lang="css">
</style>
