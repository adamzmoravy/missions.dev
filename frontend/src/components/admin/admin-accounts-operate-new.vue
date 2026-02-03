<template>


    <div class="row mt-5">

        <div class="col-md-12">
        <h2 class="mb-3">New account</h2>



            <label for="account-name" class="mt-3">Account name (organisation name, group or person)</label>
            <ui-input-text id="account-name" style="max-width:300px;" v-model="accountName"></ui-input-text>

            <label for="company-identification" class="mt-3">Kennitala</label>

            <ui-input-kennitala id="company-identification" style="max-width:150px;" v-model="newAccountCompanyIdentification"></ui-input-kennitala>


            <label for="billing-emails" class="mt-3">Billing emails (separated by commas)</label><br> 

            <ui-input-email-list :max="10" id="billing-emails" style="max-width:300px;" v-model="billingEmails"></ui-input-email-list>

            <label for="subscription-offering" class="mt-3">Initial subscription</label>


            <div class="form-check" v-for="(offering, i) in offerings" :key="offering.name">
                <input class="form-check-input" type="radio" name="new-account-type" :id="'account-type-' + i" :value="offering.name" v-model="newAccountType">
                <label class="form-check-label" :for="'account-type-' + i">
                    {{ offering.label }}
                </label>
            </div>

            <label for="spaces" class="mt-3">Spaces</label>
            <ui-input-text id="spaces" style="max-width:200px;" v-model="spaces"></ui-input-text>

            <label for="start-date" class="mt-3">Start date</label>

            <ui-input-text class="mb-2 w-25" type="date" v-model="startDate" :placeholder="'Start date'"></ui-input-text>

            <ui-button button-type="new" class="mt-4" @click="createAccount">Create account</ui-button>

        </div>


    </div>

</template>


<script>
export default {
    name: 'admin-accounts-operate-new',
    data: function() {
        return {
            newAccountCompanyIdentification: '',
            newAccountType: null,
            billingEmails: '',
            accountName: '',
            spaces: '',
            spaceSetAutomatically: '',
            startDate: null
        }
    },
    props: {
        platformConfig: {
            type: Array,
            required: true
        }
    },
    watch: {
        newAccountType: function(val) {
            const offering = this.offerings.find(o => o.name == val)
            if (offering && offering.space && (this.spaces == '' || this.spaceSetAutomatically == this.spaces)) {
                this.spaces = offering.space
                this.spaceSetAutomatically = offering.space
            }
        }
    },
    computed: {
        offerings: function() {
            return this.platformConfig.account_subscription_offerings || []
        }
    },
    components: {
    },
    methods: {
        createAccount() {
            this.$emit('create-account', {
                company_identification: this.newAccountCompanyIdentification,
                account_type: this.newAccountType,
                billing_emails: this.billingEmails,
                name: this.accountName,
                spaces: this.spaces,
                start_date: this.startDate
            })
        }
    }
}
</script>