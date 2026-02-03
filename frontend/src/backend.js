import { useAuthStore } from './stores/auth'
import { usePlatformStore } from './stores/platform'

import axios from 'axios'
import miniToastr from 'mini-toastr'
miniToastr.init({types: {success: 'success',error: 'error',info: 'info',warn: 'warn'}})

function toast({title,message,type,timeout,cb}) {
    return miniToastr[type](message, title, timeout, cb)
}


let authHeader = getAuthHeader()
let apiUrl = function () {
    let platform = usePlatformStore()

    return platform.apiUrl
}


let errorHandler = (error) => {
    console.log(error)
    const authStore = useAuthStore()
    if (error.data.message !== "") {

        if (error.data.status === 'invalid_token') {
            window.localStorage.removeItem('token')
            window.localStorage.removeItem('userData')
            authStore.removeToken()
            window.location.href = '/'
        }

        toast({
            title: 'Error',
            message: error.data.message,
            type: 'error',
            timeout: 5000
        })
    }

}

let catchErrors = (error) => {
    if (error.response) {
        errorHandler(error.response)
      } else if (error.request) {
        console.log(error.request)
      } else {
        console.log('Error', error.message)
      }
}

export default {

    get: function (resource, token = null, params = {}, silentFail = true) {
        if (silentFail) {
            return axios.get(apiUrl() + resource, { headers: getAuthHeader(token).headers, params: params })
        } else {
            return axios.get(apiUrl() + resource, { headers: getAuthHeader(token).headers, params: params }).catch(catchErrors)
        }
    },
    patch: function (resource, body) {
        return axios.patch(apiUrl() + resource, body, getAuthHeader()).catch(catchErrors)
    },
    put: function (resource, body) {
        return axios.put(apiUrl() + resource, body, getAuthHeader()).catch(catchErrors)
    },
    post: function (resource, data) {
        return axios.post(apiUrl() + resource, data, getAuthHeader()).catch(catchErrors)
    },    
    delete: function (resource) {
        return axios.delete(apiUrl() + resource, getAuthHeader()).catch(catchErrors)
    },    
    postFile: function (resource, data) {
        let headers = getAuthHeader()
        headers['Content-Type'] = 'multipart/form-data'
        return axios.post(apiUrl() + resource, data, headers ).catch(catchErrors)
    }

}



function getAuthHeader (token = null) {


    let authHeader = {}

    if (token !== null) {
        let bearerToken = 'Bearer ' + token
        authHeader = {
            headers: {
                'Authorization': bearerToken
            }
        }
    }
    else if (window.localStorage.getItem('token') !== null) {
        let tokenObject = JSON.parse(window.localStorage.getItem('token'))
        // if tokenObject.string is undefined
        if (tokenObject.string != undefined) {
            let bearerToken = 'Bearer ' + tokenObject.string
            authHeader = {
                headers: {
                    'Authorization': bearerToken
                }
            }
        }
    }

    return authHeader
  }