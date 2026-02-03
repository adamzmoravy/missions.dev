import { ref } from 'vue'

import backend from '../../backend.js'

export function submissionEditor (submission) {

    const changeTitle = (title) => {
        submission.value.language.title.value = title

    }

    return { changeTitle }

} 