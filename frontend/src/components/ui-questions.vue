<template>

    <div>
        <div v-for="(question, index) in questions" class="mb-3">
            <label class="mb-2">{{ question.q }}</label>

            <div v-if="question.type == 'kennitala'">
                <div style="max-width:150px;"><ui-input-kennitala v-model="answers[index].a" /></div>
                    
            </div>


            <div v-if="question.type == 'text'">
                <div><input type="text" value="" :size="question.size" v-model="answers[index].a"></div>
            </div>

            <div v-if="question.type == 'textarea'">
                <div style="max-width:100%;width:500px;position:relative;">
                    <span style="position:absolute;right:0px;top:-30px;">{{question.a.length}} / {{question.maxlength}}</span>
                    <ui-textarea :maxlength="question.maxlength" class="p-2 w-100" :rows="question.rows" value="" v-model="answers[index].a"></ui-textarea>

                </div>
            </div>

            <div v-if="question.type == 'multiple'">

                <div v-for="(choice, choiceIndex) in question.choices" class="mb-2">
                    <label class="form-check-label">
                        <input type="radio" :value="choice" :id="index + '-' + choiceIndex" v-model="answers[index].a"> {{choice}}
                    </label>
                </div>
            </div>
            
            <div v-if="question.type == 'checkbox'" class="mx-3">
                <div v-for="(choice, choiceIndex) in question.choices">
                    <label :for="index + '-' + choiceIndex">  <input class="mr-2" type="checkbox" :value="choice" :id="index + '-' + choiceIndex" v-model="answers[index].a"> {{choice}}</label>
                </div>
            </div>


        </div>
    </div>


</template>

<script>

export default {
    name: 'ui-questions',
    data() {
        return {
            answers: []
        }
    },
    watch: {
        answers: function(newValue) {
            this.$emit('update', newValue)
        },
        questions:  {
                immediate: true, 
                handler: function (q) {
                    this.answers = q
                }
            },
    },
    props: {
        questions: {
            required: true
        }
    },    

}


</script>