import { ref, reactive, computed, watch, onMounted } from 'vue'

import backend from '../../backend.js'
import  { submissionEditor } from './submissionEditor'

import { useEventsStore } from '../../stores/events.js'

export function useSubmission() {

    const data = reactive({
        value: null
    })
      
    var status = ref('start')

    const editor = submissionEditor(data)

    const fetch = (submissionId, language = 'en') => {
        status = 'fetching'
        return backend.get('/experiments/' + submissionId + '/' + language).then(response => {
            data.value = response.data.experiment
            status = 'fetched'
        })
    }

    const refetch = () => {
        return fetch(data.value.experiment_id)
    }
   
    const topImage = computed(() => {
        if (status == 'fetched') {
            if (data.value.image_collection.top_image) {
                return data.value.image_collection.top_image
            } else {
                return null
            }
        } else {
            return null
        }
    })

    const participate = (type) => {
        return backend.post('/experiments/' + data.value.experiment_id + '/' + type + '/', {
        })
    }
    const unparticipate = (type) => {
        return backend.delete('/experiments/' + data.value.experiment_id + '/' + type + '/', {
        })
    }

    const participants = computed(() => {
        if (status == 'fetched') {

            let participants = []
            let participantsWithReactions = []
            let participantsWithReactionsObject = {}

            for (let reaction in data.value.participants) {
                participants = participants.concat(data.value.participants[reaction].array)
            }

            participants = [...new Set(participants)]

            for (let i = 0; i < participants.length; i++) {
                let participant = participants[i]
                let participantWithReactions = {
                    id: participant,
                    reactions: []
                }
                for (let reaction in data.value.participants) {
                    if (data.value.participants[reaction].array.includes(participant)) {
                        participantWithReactions.reactions.push(reaction)
                    }
                }
                participantsWithReactions.push(participantWithReactions)
            }

            // set participantsWithReactionsObject so that id is key and reactions is value
            for (let i = 0; i < participantsWithReactions.length; i++) {
                participantsWithReactionsObject[participantsWithReactions[i].id] = participantsWithReactions[i].reactions
            }
             return participantsWithReactionsObject

        } else {
            return null
        }
    })    

    const addToTeam = (user) => {

        let userId 
        // if user is string
        if (typeof user == 'string') {
            userId = user
        } else {
            userId = user.user_id
        }

        return backend.post('/experiments/' + data.value.experiment_id + '/team/' + userId + '/', {}
        ).then(response => {
            data.value.team = response.data.team
            refetch()
        })
    }

    const removeFromTeam = (user) => {

        let userId 
        // if user is string
        if (typeof user == 'string') {
            userId = user
        } else {
            userId = user.user_id
        }


        return backend.delete('/experiments/' + data.value.experiment_id + '/team/' + userId + '/', {})
        .then(response => {
            refetch()
        })
    }
    
    const userParticipations = (userId) => {
        let reactions = []
        if (status == 'fetched') {
            // loop throught each property of data.value.participants, if property contains property array which contains userId, add property name to reactions array
            for (let reaction in data.value.participants) {
                if (data.value.participants[reaction].array.includes(userId)) {
                    reactions.push(reaction)
                }
            }
        }

        return reactions
    }

    const addRelatedSubmission = (relatedSubmission) => {

        let relatedSubmissionId
        // if user is string
        if (typeof relatedSubmission == 'string') {
            relatedSubmissionId = relatedSubmission
        } else {
            relatedSubmissionId = relatedSubmission.experiment_id
        }


        let newRelatedExperiments = [relatedSubmissionId]
        return backend.post('/experiments/' + data.value.experiment_id + '/related_experiments/', {
            related_experiments: newRelatedExperiments
        }).then(response => {
            refetch()
        })
    }

    const removeRelatedSubmission = (relatedSubmission) => {

        let relatedSubmissionId
        // if user is string
        if (typeof user == 'string') {
            relatedSubmissionId = relatedSubmission
        } else {
            relatedSubmissionId = relatedSubmission.experiment_id
        }


        return backend.delete('/experiments/' + data.value.experiment_id + '/related_experiments/' + relatedSubmissionId + '/', {
        }).then(response => {
            refetch()
        })
        
    }

    const saveTopImage = (filename) => {
        return backend.patch('/experiments/' + data.value.experiment_id + '/image_collection/', {
            image_collection: {
                top_image: {
                    filename: filename
                }
            }
        })        
    }

    
    return { 
        data, 
        status, 
        fetch, 
        editor, 
        topImage, 
        addRelatedSubmission, 
        removeRelatedSubmission, 
        userParticipations, 
        participate, 
        unparticipate,
        addToTeam,
        removeFromTeam,
        participants,
        saveTopImage        
    }

} 