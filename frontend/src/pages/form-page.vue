<template>
    <div>

         <div class="container pt-3 col-md-6">
            <h1>Austanátt</h1>

            <!-- Your form content goes here -->
    
           <survey :form="form" :name="formName" />

       </div>
 
    </div>
</template>

<script>
export default {
    name: 'FormPage',
    data() {
        return {
            form: {
                steps: [
                    {
                        title: 'Umsóknarform í vaxtarrými 2024',
                        order: 1,
                        questions: [
                            {
                                type: 'text',
                                label: 'Nafn hugmyndar eða verkefnis',
                                name: 'project_name',
                                required: true
                            },
                            {
                                type: 'text',
                                label: 'Þitt nafn',
                                name: 'applicant_name',
                                required: true
                            },
                            {
                                type: 'email',
                                label: 'Netfang',
                                name: 'email',
                                required: true
                            },
                            {
                                type: 'phone',
                                label: 'Símanúmer',
                                name: 'phone',
                                required: true
                            },
                            {
                                type: 'textarea',
                                label: 'Út á hvað gengur verkefnið eða hugmyndin í hnotskurn?',
                                name: 'project_description',
                                required: true,
                                rows: 5
                            },
                            {
                                type: 'textarea',
                                label: 'Hvar er hugmyndin eða verkefnið statt núna?',
                                name: 'project_status',
                                required: true,
                                rows: 5
                            },
                            {
                                type: 'textarea',
                                label: 'Hver eða hverjir eru aðstandendur verkefnisins?',
                                name: 'project_participants',
                                required: true,
                                rows: 5
                            },
                            {
                                type: 'textarea',
                                label: 'Hvernig vonastu til að vaxtarýmið geti stutt við verkefnið?',
                                name: 'project_benefit',
                                required: true,
                                rows: 5
                            }
                        ]
                    }     
                ]
            }
        }
    },
    computed: {
        formName() {
            return this.$route.params.formName;
        }
    },
    mounted() {
    }
}
</script>