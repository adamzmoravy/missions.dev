<template>

    <div style="width:100%;height:100%;position:relative;">
            <div class="x-big-tag skill-container">

                <div class="texter d-none">
                    <h1 class="title">All Members' Meeting</h1>
                </div>

                <span v-for="skill in skills" :key="skill.skill_id" v-if="skill" class="tag">{{ skill }}</span>

            </div>

    </div>

</template>

<style scoped>
.texter {
    position:absolute;
    top:50%;
    bottom:50%;
    text-align:center;
    font-size: 50px;
    font-weight: bold;
    color: brown;
    text-shadow: 2px 2px 4px #000000;
}

.text-overlay {
    position: absolute; 
    width: 100vw;
    height: 100vh;
    color:brown;
}
.skill-container {

    position:relative;
    width:120%;
    margin-left:-10%;
    margin-top:-10%;

}

</style>


<script>

import backend from '../backend.js'

export default {
    name: 'SkillsCloudPage',
    data() {
        return {
            skills: []
        }
    },
    props: {
        randomizePulse: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        shuffleRandom: function () {
            this.skills.sort(() => Math.random() - 0.5)
        },
        shuffleThirdRandomly: function () {
            let third = Math.floor(this.skills.length / 3)
            let start = Math.floor(Math.random() * (this.skills.length - third))
            let end = start + third
            let shuffled = this.skills.slice(start, end).sort(() => Math.random() - 0.5)
            this.skills.splice(start, third, ...shuffled)

        },
        shuffleFew: function () {
            let skills = this.skills
            let swapCount = 1
            for (let i = 0; i < swapCount; i++) {
                let index1 = Math.floor(Math.random() * skills.length)
                let index2 = Math.floor(Math.random() * skills.length)
                let temp = skills[index1]
                skills[index1] = skills[index2]
                skills[index2] = temp
            }
            this.skills = skills.slice(0, this.limit)
        },
        getSkills: function () {
            backend.get('/users/skills/')
            .then(response => {
                this.skills = response.data.skills
                this.shuffleRandom()
            })
        }
    },
    mounted() {
        this.getSkills()
        if (this.randomizePulse) {
            setInterval(this.shuffleRandom, 10000)
        }        
    },
}

</script>