
import { ref, reactive, computed } from 'vue'
import backend from '../../backend'

export function useGroup() {
    const data = ref({
        value: {
            registrations: [],
            uuid: null,
            schedule: [],
            custom_language: {},
            config: {
                access: 'private',
                survey: []
            }
        }
    })

    var status = ref('start')

    const fetch = (groupId, language = 'en') => {
        if (status.value === 'start') {
            status.value = 'fetching'
        }
        return backend.get('/happenings/' + groupId + '/').then(response => {
            data.value = response.data.happening
            status.value = 'fetched'
        })
    }

    const join = (userId, registrationInfo = null) => {

        return backend.post('/happenings/' + data.value.happening_id + '/registrations/',
        {
            'user_id': userId,
            'registration_info': registrationInfo

        }).then(response => {
            fetch(data.value.happening_id)
        })
    }


    const isUserRegistered = function (userId) {
        return data.value.registrations.some(registration => registration.user_id === userId)
    }

    const registrations = computed(() => {
        return data.value.registrations.map(registration => registration.user_id)
    })


    return {
        data,
        status,
        fetch,
        join,
        registrations,
        isUserRegistered
    }
}
