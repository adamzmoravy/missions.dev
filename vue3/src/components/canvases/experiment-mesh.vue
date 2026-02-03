<template>

            <submission-preview-modal @close="ui.showSubmissionPreview = null" v-if="ui.showSubmissionPreview !== null" :submission-id="ui.showSubmissionPreview"  />

            <entities 
                @close="ui.addNewExperiment = false" 
                v-if="ui.addNewExperiment" 
                :multi-select="false" 
                :modal-only="true"                                     
                :show-search-bar="true" 
                @selected="newSubmissionPlaced" 
                @unselected="" 
                :buttons="[{label: $trans('Create new'), type: 'new', action: 'create-new'}]"
                @create-new="ui.createNewSubmission = true; ui.addNewExperiment = false"
                :multi-select-selected="[]" 
                :include-link="false" 
                :sub-title="$trans('Select existing submission to place on the map, or create a new one.')"
                :title="$trans('Place submission')" 
                :entities="allSubmissionsEntities" 
                :limit="5000" />


                <submission-create v-if="ui.createNewSubmission" @close="ui.createNewSubmission = false" @submission-created="placeNewSubmission" />                


<div>

                    <div class="tile-debug-panel front" style="z-index:100000;" v-if="debughelp">
                        <ui-button @click="createGridMesh(experiments)">Create gridMesh</ui-button>

                        <button v-on:click="setActiveBoard('east')">East</button>
                        <button v-on:click="setActiveBoard('index')">Index</button>
                        <button v-on:click="setActiveBoard('north')">North</button>
                        <button v-on:click="authenticateMe()">Action</button>
                        Tiles: {{tiles.length}} Mesh: {{gridMesh.length}}
                        <div v-for="tile in tiles">
                            {{tile.address}}
                            <span v-if="tile.experiment">{{tile.experiment.title}}</span>
                        </div>
                    </div>

                    <div class="mesh-container noselect" ref="meshContainer" v-on:zoom="scrollIntoView" :style="meshContainerStyle">
                        <div id="mesh-container-inner" @dblclick="meshDoubleClick" v-on:mouseleave="hoverOutOfMesh" v-on:mousemove="hoverOverMesh" ref="mesh" class="mesh-container-inner" v-on:mouseup="cursor = 'grab'" v-on:mousedown="cursor = 'grabbing'" :style="tileContainerStyle">
                            <div v-if="experimentBeingMoved" class="move-explainer" :style="{position:'absolute', top: mouseOverHexGridPixels.y + 'px', left: mouseOverHexGridPixels.x + 'px'}">Double click to place tile</div>

                            <div id="hex-tile" class="hex-tile" v-for="tile in tiles" :style="tile.style">

                                <div v-if="!tile.experiment" class="">
                                    <div v-if="editMode">
                                        <div class="hex-tile-inner">
                                            <a v-if="experimentBeingMoved == null" v-on:click="addNewExperiment(tile.address)" v-on:touchend="addNewExperiment(tile.address)" :title="'X: ' + tile.address.x + ' Y: ' + tile.address.y" style="z-index:10;" class="tile-title d-md-inline-block">
                                                <img class="tile-add" src="/img/add-icon.svg">
                                            </a>
                                        </div>
                                        <img src="/img/hex-add-noborder.svg" class="tile-image tile-image-add" style="width:100%;">

                                    </div>
                                </div>
                                <div v-if="tile.experiment">
                                    <div>
                                        <div v-if="tile.experiment.stage != 5">

                                            <div class="hex-tile-inner" v-if="tile.experiment.stage != 4">
                                                <ui-a class="tile-title"
                                                
                                                v-on:touchend="showPreview(tile.experiment)" 
                                                v-on:click="showPreview(tile.experiment)">{{ tile.experiment.title }}</ui-a>
                                            </div>
                                            <div class="hex-tile-inner" style="color:white;"  v-if="tile.experiment.stage == 4">
                                            {{tile.experiment.title}}
                                            </div>
                                            <img v-if="tile.experiment.stage == 3" src="/img/hex-observation.png" class="tile-image" style="width:100%;">
                                            <img v-if="tile.experiment.stage == 0" src="/img/hex-challenge.png" class="tile-image" style="width:100%;">
                                            <img v-if="tile.experiment.stage == 1" src="/img/hex-idea.png" class="tile-image" style="width:100%;">
                                            <img v-if="tile.experiment.stage == 2" src="/img/hex-project.png" class="tile-image" style="width:100%;">
                                            <img v-if="tile.experiment.stage == 4" src="/img/hex-dark.png" class="tile-image" style="width:100%;">
                                        </div>
                                        <div v-if="tile.experiment.stage == 5" class="hex-mission hex-tile text-center">
                                            <img :src="'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,w_400,e_pixelate:0/' + tile.experiment.image" draggable="false" class="shake noselect" style="width:50%;margin-bottom:1em;margin-top:3em;"><br>
                                            <div style="width:70%;margin:auto;">{{tile.experiment.short_description}}</div>
                                        </div>
                                        <span v-if="experimentBeingMoved === null && (tile.mouseProlongedOver || interactionMode === 'edit') && editMode && canEdit(tile.experiment.team)">
                                            <a v-bind:class="{ 'tile-move-mission': tile.experiment.stage == 5 || tile.experiment.stage == 4}" class="tile-move" v-on:click="moveExperiment(tile)"> {{ $trans('Move') }} </a>
                                            <a v-bind:class="{ 'tile-remove-mission': tile.experiment.stage == 5 || tile.experiment.stage == 4}" class="tile-remove" v-on:click="removeExperiment(tile.experiment)">{{ $trans('Remove') }}</a>
                                        </span>


                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


            
            
            
                    <div class="control-bar" v-if="showControlBar">

                        <vue-slider direction="ttb" style="position:relative;top:80px;" :min="minZoom" :height="150" :width="15" :max="maxZoom" :dot-size="30" :interval="0.01" v-model="zoom"></vue-slider>

                    </div>

                </div>
</template>

<script>
    import backend from '../../backend'

    import { defineGrid, extendHex } from 'honeycomb-grid'

    import submissionPreviewModal from '../submissions/submission-preview-modal.vue'
    import submissionCreate from '../submissions/submission-create.vue'

    import Panzoom from 'panzoom'
    import VueSlider from 'vue-slider-component'
    import 'vue-slider-component/theme/default.css'

    export default {
        name: 'experiment-mesh',
        data () {
            return {
                interactionMode: 'explore',
                experimentBeingMoved: null,
                debughelp: false,
                gridMesh: false,
                ui: {
                    touchemove: false,
                    addNewExperiment: false,
                    showSubmissionPreview: null,
                    createNewSubmission: false
                },
                meshMargin: 3000,
                meshOffset: 0,
                meshSizeMultiplier: 1,
                hexHeightRatio: 0.866,
                zoom: 0.3,
                minZoom: 0.2,
                maxZoom: 5,
                stageNames: {
                    3: 'observation',
                    0: 'challenge',
                    1: 'idea',
                    2: 'project',
                    5: 'mission'
                },
                cursor: 'grab',
                preview: null,
                mouseOverCords: {},
                mouseProlongedOverHexGrid: {
                    x: null,
                    y: null
                },
                mouseOverHexGrid: {
                    x: null,
                    y: null
                },
                mouseOverHexGridPixels: {
                    x: null,
                    y: null
                },
                addNewExperimentAddress: null
            }
        },
        components: {VueSlider, submissionPreviewModal, submissionCreate},
        props: {
            activeBoard: {
                required: false,
                default: 'index'
            },
            experiments: {},
            apiUrl: {},
            meshStartZoom: {},
            meshStartMargin: {},
            showControlBar: {
                required: false,
                default: true
            }
        },
        watch: {
            activeBoard: function (newBoard, oldBoard) {
                this.setActiveBoard(newBoard)
            },
            zoom: function (newZoom, oldZoom) {
                this.doZoom(newZoom)
            },
            mouseOverHexGrid: function (newCords, oldCords) {
                if (newCords.x !== oldCords.x || newCords.y !== oldCords.y) {
                    // console.log('cords changed')
                    this.removeTilesContaining('type', 'empty')
                    var self = this
                    if (this.interactionMode === 'edit') {
                        this.prolongedHover = setTimeout(function() {
                            self.mouseProlongedOverHexGrid = newCords
                        }, 200)
                    } else {
                        this.prolongedHover = setTimeout(function() {
                            self.mouseProlongedOverHexGrid = newCords
                        }, 800)
                    }
                }
            },
            mouseProlongedOverHexGrid: function (newCords, oldCords) {
                // Has it changed?
                if (newCords.x !== oldCords.x || newCords.y !== oldCords.y) {
                    // Are we still hover over?
                    if (newCords.x === this.mouseOverHexGrid.x && newCords.y === this.mouseOverHexGrid.y) {
                        // Update tile
                        let gridIndex = this.getGridIndex(newCords.x, newCords.y)
                        if (gridIndex !== -1) {
                            this.gridMesh[gridIndex].contains.mouseProlongedOver = true
                        } else {
                            this.gridMesh.push(this.Hex(this.mouseOverHexGrid.x, this.mouseOverHexGrid.y, {contains: { mouseover: true, type: 'empty' }}))
                        }

/*                        let gridIndexOld = this.getGridIndex(oldCords.x, oldCords.y)
                        if (typeof this.gridMesh[gridIndexOld] !== 'undefined') {
                            console.log(this.gridMesh[gridIndexOld].contains.type)
                            this.gridMesh[gridIndexOld].contains.mouseProlongedOver = false
                            if (this.gridMesh[gridIndexOld].contains.type === 'empty') {
                                this.gridMesh.splice(gridIndexOld, 1)
                            }
                        }
*/
                    }
                }
            },
            experiments:  {
                immediate: true,
                handler: function (experiments, oldExperiments) {
                    let gridMesh = this.createGridMesh(experiments)
                    this.gridMesh = gridMesh
                }
            }
        },
        computed: {
            allSubmissionsEntities: function () {
                return this.$stores.submissions.getEntities()
            },
            userType: function () {
                if (this.$currentUser) {
                    return this.$currentUser.user_type
                }
                return ''
            },
            userIdNumber: function () {
                if (this.$currentUser) {
                    return this.$currentUser.user_id
                }
                return null
            },
            language: function () {
                return this.$lang
            },
            previewSubmission: function () {
                if (this.ui.showSubmissionPreview !== null) {
                    return this.$stores.submissions.getSubmissionPreview(this.ui.showSubmissionPreview)
                }
                return null
            },
            editMode: function () {
//                if (this.userType === 'admin') {
                if (this.isUserAuthenticated) {
                    return true
                } else {
                    return false
                }
            },
            meshContainerStyle: function () {
                return {
                }
            },
            tileContainerStyle: function () {
                if (this.gridMesh !== false) {
                    return {
                        width: 1000 + (this.meshMargin * 2) + 'px',
                        height: 1000 + (this.meshMargin * 2) + 'px',
                        backgroundSize: this.hexSize * 3 + 'px',
                        backgroundPosition: (this.hexSize + this.meshMargin) + 'px ' + ((this.hexSize * this.hexHeightRatio) + this.meshMargin) + 'px',
                        cursor: this.cursor
                    }
                } else {
                }
            },
            hexSize: function () {
                return 100 * this.meshSizeMultiplier
            },
            Hex: function () {
                return extendHex({
                    size: this.hexSize,
                    orientation: 'flat'
                })
            },
            Grid: function () {
                let Grid = defineGrid(this.Hex())
                return Grid
            },
            tiles: function () {
                let tiles = []
                this.gridMesh.forEach(hex => {
                    let tile = {
                        points: hex.toPoint(),
                        address: {
                            x: hex.coordinates().x,
                            y: hex.coordinates().y
                        },
                        style: {
                            left: (hex.toPoint().x + this.meshMargin) + 'px',
                            top: (hex.toPoint().y + this.meshMargin) + 'px',
                            width: hex.size.yRadius * 2 + 'px',
                            height: hex.size.xRadius * 2 * 0.865 + 'px',
                        }
                    }
                    if (hex.contains.moving) {
                        tile.style.left = (this.mouseOverHexGridPixels.x - hex.size.yRadius) + 'px'
                        tile.style.top = (this.mouseOverHexGridPixels.y - hex.size.yRadius) + 'px'
                    }
                    tile = Object.assign(tile, hex.contains)
                    if (tile.experiment) {
                        tile.class = 'hex-' + this.stageNames[tile.experiment.stage]
                    }
                    tiles.push(tile)
                })
                return tiles
            }
        },
        methods: {
            moveTile: function (address) { // get items in this.gridMesh where contains.moving = true
                let moving = this.gridMesh.findIndex(hex => hex.contains.moving)
                this.gridMesh[moving].x = address.x
                this.gridMesh[moving].y = address.y            
                this.gridMesh[moving].contains.moving = false

            },
            meshDoubleClick: function (event) {
                if (this.experimentBeingMoved !== null) {
                    event.stopPropagation()
                    let address = {
                        x: this.mouseOverHexGrid.x,
                        y: this.mouseOverHexGrid.y
                    }
                    this.updateExperiment(this.experimentBeingMoved, address)
                    this.moveTile(address)
                    this.experimentBeingMoved = null
                }
            },
            navigateTo (url) {
                window.location.href = url
            },
            doZoom (newZoom) {
                let element
                if (Array.isArray(this.$refs.meshContainer)) {
                    element = this.$refs.meshContainer[0]
                } else {
                    element = this.$refs.meshContainer
                }
                this.myZoom.zoomAbs(element.offsetWidth / 2, element.offsetHeight / 2, newZoom)
            },
            setTileContains (cords, property, value) {
                let index = this.getGridIndex(cords.x, cords.y)
                this.gridMesh[index].contains[property] = value
                this.gridMesh = this.gridMesh
            },
            removeTilesContaining (property, value) {
                for (var i = 0; i < this.gridMesh.length; i += 1) {
                    if (this.gridMesh[i].contains[property] === value) {
                        this.gridMesh.splice(i, 1)
                    }
                }
            },
            getGridIndex(x, y) {
                for (var i = 0; i < this.gridMesh.length; i += 1) {
                    if (this.gridMesh[i]['x'] === x && this.gridMesh[i]['y'] === y) {
                        return i
                    }
                }
                return -1
            },
            modeExplore () {
                this.cursor = 'grab'
                this.interactionMode = 'explore'
            },
            modeEdit () {
                this.cursor = 'default'
                this.interactionMode = 'edit'
            },
            getUserType () {
                if (this.userType === '') {
//                    this.authenticate()
                }
                return this.userType
            },
            meshTouchEnd (experiment) {
                if (this.ui.touchemove === false) {
//                    location.href = this.rootUrl + this.language + '/experiment/' + experiment.experiment_id
                    this.showPreview(experiment)
                }
            },
            canEdit (team) {
                var result = false
                if (this.getUserType() === 'admin') {
                    return true
                }
                team.forEach(member => {
                    if (String(member.profile_id) === String(this.userIdNumber)) {
                        // console.log(String(member.profile_id))
                        // console.log(String(self.userIdNumber))
                        result = true
                    }
                })
                return result
            },
            whatTilesToShow () {
                if (this.userType === 'admin') {
                    return 'all'
                } else {
                    return this.userIdNumber
                }
            },
            setActiveBoard (board) {
                this.userSelectedActiveBoard = board
                this.createGridMesh(this.experiments)
                let gridMesh = this.createGridMesh(this.experiments)
                this.gridMesh = gridMesh
            },
            createGridMesh (experiments) {
                let grid = this.Grid()
                let hexes = []
                experiments.forEach(experiment => {
                    if (experiment.show_in_placement[this.activeBoard] !== undefined) {

                        let cords = this.addressToCords(experiment.show_in_placement[this.activeBoard])
                        let contains = {
                            type: 'used',
                            moving: false,
                            experiment: experiment,
                            mouseOver: false,
                            mouseProlongedOver: false
                        }
                        hexes.push(this.Hex(this.localCords(cords.x), this.localCords(cords.y), { contains: contains }))
                    }
                })
                hexes.forEach(hex => {
                    if (!grid.includes(hex)) {
                        grid.push(hex)
                    }
                })
                return grid
            },
            newSubmissionPlaced (submission) {
                this.placeNewSubmission(submission.experiment_id)
            },
            placeNewSubmission  (submissionId) {
                let submission = {
                    experiment_id: submissionId,
                }
                this.updateExperiment(submission, this.addNewExperimentAddress)
                this.ui.addNewExperiment = false
            },
            updateExperiment (experiment, address) {
                let experimentBody = {
                    settings: {
                        show_in_placement: {
                            [this.activeBoard]: address
                        }
                    }
                }

                backend.patch('/experiments/' + experiment.experiment_id + '/settings/', experimentBody).then((response) => {

                }, (error) => {
                    console.log(error)
                })
/*                var authHeader = this.getAuthorizationHeaderMixin()
                this.updateExperimentSettingsMixin(this.apiUrl, experiment.experiment_id, experimentBody, authHeader).then((response) => {
                    this.$modal.hide('new-experiment')
                }, (error) => {
                    console.log(error)
                })
                    */
            },
            moveExperiment (tile) {
                this.interactionMode = 'edit'
                this.setTileContains(tile.address, 'moving', true)
                this.experimentBeingMoved = tile.experiment
            },
            removeExperiment (experiment) {
                this.updateExperiment(experiment, null)
            },
            createNewSubmission () {
                this.ui.addNewExperiment = false
                this.$stores.ui.setAction('new-submission')
            },
            submissionSelected (submission) {
                this.ui.addNewExperiment = false
                this.updateExperiment(submission, this.addNewExperimentAddress)
            },
            addNewExperiment (address) {
                this.interactionMode = 'edit'
                if (this.experimentBeingMoved !== null) {
                    this.updateExperiment(this.experimentBeingMoved, address)
                    this.experimentBeingMoved = null
                } else {
                    this.addNewExperimentAddress = address
                    this.ui.addNewExperiment = true
                }
            },
            hoverOutOfMesh (e) {
                this.mouseOverHexGrid = {x: null, y: null}
            },
            hoverOverMesh (e) {
                let rect
                if (Array.isArray(this.$refs.mesh)) {
                    rect = this.$refs.mesh[0].getBoundingClientRect()
                } else {
                    rect = this.$refs.mesh.getBoundingClientRect()
                }
                let x = ((e.clientX - rect.left) / this.zoom) - this.meshMargin
                let y = ((e.clientY - rect.top) / this.zoom) - this.meshMargin
                this.mouseOverHexGridPixels.x = (e.clientX - rect.left) / this.zoom
                this.mouseOverHexGridPixels.y = (e.clientY - rect.top) / this.zoom
                const Grid = defineGrid(this.Hex)
                const hexCoordinates = Grid.pointToHex(x, y)
                this.mouseOverHexGrid = hexCoordinates
            },
            dblClickOnMesh (e) {
                console.log(e)
                let touch = e.changedTouches[0]
                let rect
                if (Array.isArray(this.$refs.mesh)) {
                    rect = this.$refs.mesh[0].getBoundingClientRect()
                } else {
                    rect = this.$refs.mesh.getBoundingClientRect()
                }
                let x = ((touch.clientX - rect.left) / this.zoom) - this.meshMargin
                let y = ((touch.clientY - rect.top) / this.zoom) - this.meshMargin
                this.mouseOverHexGridPixels.x = (touch.clientX - rect.left) / this.zoom
                this.mouseOverHexGridPixels.y = (touch.clientY - rect.top) / this.zoom
                const Grid = defineGrid(this.Hex)
                const hexCoordinates = Grid.pointToHex(x, y)
                this.mouseOverHexGrid = hexCoordinates
            },
            localCords: function (cord) {
                return cord - this.meshOffset
            },
            scrollIntoView: function (event) {
                console.log(event)
            },
            startScroll: function (event) {
                let scrollPos = {
                    // The current scroll
                    left: event.target.scrollLeft,
                    top: event.target.scrollTop,
                    // Get the current mouse position
                    x: event.clientX,
                    y: event.clientY
                }
                let mousemoveHandler = function (event) {
                    const dx = event.clientX - scrollPos.x
                    const dy = event.clientY - scrollPos.y
                    // Scroll the element
                    event.target.scrollTop = scrollPos.top - dy
                    event.target.scrollLeft = scrollPos.left - dx
                }
                let mouseupHandler = function (event) {
                    event.target.removeEventListener('mousemove', mousemoveHandler)
                    event.target.removeEventListener('mouseup', mouseupHandler)
                }
                event.target.addEventListener('mousemove', mousemoveHandler)
                event.target.addEventListener('mouseup', mouseupHandler)
            },
            addressToCords: function (address) {
                if (typeof (address) === 'string') {
                    let split = address.split('-')
                    let cords = {
                        x: parseInt(split[1]),
                        y: parseInt(split[0])
                    }
                    return cords
                } else {
                    return address
                }
            },
            meshCursor: function (cursor) {
                this.cursor = cursor
            },
            showPreview: function (experiment) {
                this.ui.showSubmissionPreview = experiment.experiment_id
//                this.preview = experiment
//                this.$modal.show('preview')
            },
            authenticateMe: function () {
//                console.log('returned user data: ')
//                console.log(this.returnUserData())
//                this.animalType = 'turtle'
//                this.userType = this.userType
//                this.loggedInUser =
//
//                this.getUserData()
/*
                if (this.getUserType() === 'admin') {
                    this.editMode = true
                }

                */
            }
        },
        created: function () {
        },
        mounted: function() {
            if (localStorage.board) {
                this.userSelectedActiveBoard = localStorage.board
            }
            let element
            if (Array.isArray(this.$refs.mesh)) {
                element = this.$refs.mesh[0]
            } else {
                element = this.$refs.mesh
            }
            this.myZoom = Panzoom(element, {
                maxZoom: this.maxZoom,
                minZoom: this.minZoom
            })
            var self = this
            this.myZoom.on('zoom', function(event) {
                let transform = event.getTransform()
                self.zoom = transform.scale
            })
            this.myZoom.moveTo(-(element.clientWidth / 2), -(element.clientHeight / 2))

            /*

            this.$root.eventBus.$on('submission-created', function (experiment) {
                self.updateExperiment(experiment, self.addNewExperimentAddress)
            })
            this.authenticateMe()
            this.$root.eventBus.$on('profile', function () {
                self.authenticateMe()
            })
            this.$root.eventBus.$on('logout', function () {
                self.authenticateMe()
            })
            */
            this.zoom = this.meshStartZoom
            this.meshMargin = this.meshStartMargin
        }

    }

</script>


<style scoped>
.move-explainer {
    background-color:rgb(76, 76, 76);
    z-index:999999999;
    color:white;
    width:190px;
    border-radius: 5px;
    padding:3px;
    padding-left:4px;
    padding-right:4px;
    margin-left:-95px;
    margin-top:20px;

}


.stage-accenter span {
    filter: brightness(90%);
}

.print .stage-accenter span {
    filter: brightness(100%);
}

.stage-icon {
    margin-right:0.6rem;
}


.stage-accenter {
    width:6rem;
    margin-left:1rem;
    padding-bottom:0.5rem;
    border-top-style:solid;
    border-top-width:3px;
    display:inline-block;
    font-weight:200;
    text-transform: capitalize;
}

.stage-0-accenter {
    color: #F7BCB6;
}
.stage-1-accenter {
    color: #f8dd6c;
}
.stage-2-accenter {
    color: #73BF98;
}

.stage-3-accenter {
    color: #95C1E2;
}


</style>
