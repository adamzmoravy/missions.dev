<template>


        <ui-panel v-if="contactLoaded" class="bg-white contact-information">
        <div v-if="contact.email_address" class="contact-item">
            <div class="contact-label">{{ $trans('Email') }}</div>
            <a :href="`mailto:${contact.email_address}`" class="contact-value email-link">
                {{ contact.email_address }}
            </a>
        </div>

        <div v-if="contact.phone_number" class="contact-item">
            <div class="contact-label">{{ $trans('Phone') }}</div>
            <div class="contact-value">{{ contact.phone_number }}</div>
        </div>

        <div v-if="contact.services && contact.services.length > 0 && contact.phone_number" class="contact-item">
            <div class="contact-label">{{ $trans('Messaging') }}</div>
            <div class="services-buttons">
                <a 
                    v-if="contact.services.includes('WhatsApp')" 
                    :href="getWhatsAppLink()" 
                    target="_blank"
                    class="service-button whatsapp"
                    style="display: flex; align-items: center;"
                >

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="red" width="24" height="24" style="display: none;">
                        <defs>
                            <symbol id="whatsapp-icon" viewBox="0 0 24 24">
                                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.32a8.188 8.188 0 0 1-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.86-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.16-.48-.27-.25-.14-1.47-.74-1.69-.82-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14-.01-.3-.01-.47-.01z"/>
                            </symbol>
                        </defs>
                    </svg>
                    <span class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">

                            <use href="#whatsapp-icon"></use>

                        </svg>
                    </span>

                WhatsApp

                </a>
                <a 
                    v-if="contact.services.includes('Telegram')" 
                    :href="getTelegramLink()" 
                    target="_blank"
                    class="service-button telegram"
                    style="display: flex; align-items: center;"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="red" width="24" height="24" style="display: none;">
                        <defs>
                            <symbol id="telegram-icon" viewBox="0 0 24 24">
                                <path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>
                            </symbol>
                        </defs>
                    </svg>
                    <span class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                            <use href="#telegram-icon"></use>
                        </svg>
                    </span>
                    Telegram
                </a>

                <a 
                    v-if="contact.services.includes('Signal')" 
                    :href="getSignalLink()" 
                    target="_blank"
                    class="service-button signal"
                    style="display: flex; align-items: center;"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 1024 1024" style="display:none;">
                        <defs>
                            <symbol id="signal-icon" viewBox="0 0 1024 1024">
                                <path fill="#ffffff" d="M427.5,170.3l7.9,32A319.6,319.6,0,0,0,347,238.9l-16.9-28.3A347.6,347.6,0,0,1,427.5,170.3Zm169,0-7.9,32A319.6,319.6,0,0,1,677,238.9l17.1-28.3A350.1,350.1,0,0,0,596.5,170.3ZM210.6,330a349.5,349.5,0,0,0-40.3,97.5l32,7.9A319.6,319.6,0,0,1,238.9,347ZM193,512a318.5,318.5,0,0,1,3.6-47.8l-32.6-5a352,352,0,0,0,0,105.5l32.6-4.9A319.5,319.5,0,0,1,193,512ZM693.9,813.3,677,785.1a317.8,317.8,0,0,1-88.3,36.6l7.9,32A350.3,350.3,0,0,0,693.9,813.3ZM831,512a319.5,319.5,0,0,1-3.6,47.8l32.6,4.9a352,352,0,0,0,0-105.5l-32.6,5A318.5,318.5,0,0,1,831,512Zm22.7,84.4-32-7.9A319,319,0,0,1,785.1,677l28.3,17A348.9,348.9,0,0,0,853.7,596.4Zm-293.9,231a319.1,319.1,0,0,1-95.6,0L459.3,860a351.3,351.3,0,0,0,105.4,0Zm209-126.2a318.1,318.1,0,0,1-67.6,67.5l19.6,26.6A355.1,355.1,0,0,0,795.4,721Zm-67.6-446a318.6,318.6,0,0,1,67.6,67.6L795.4,303A354.6,354.6,0,0,0,721,228.6Zm-446,67.6a318.6,318.6,0,0,1,67.6-67.6L303,228.6A354.6,354.6,0,0,0,228.6,303ZM813.4,330l-28.3,17a317.8,317.8,0,0,1,36.6,88.3l32-7.9A348.9,348.9,0,0,0,813.4,330ZM464.2,196.6a319.1,319.1,0,0,1,95.6,0l4.9-32.6a351.3,351.3,0,0,0-105.4,0ZM272.1,804.1,204,819.9l15.9-68.1-32.1-7.5-15.9,68.1a33,33,0,0,0,24.6,39.7,34.5,34.5,0,0,0,15,0l68.1-15.7Zm-77.5-89.2,32.2,7.4,11-47.2a316.2,316.2,0,0,1-35.5-86.6l-32,7.9a353.3,353.3,0,0,0,32.4,83.7Zm154,71.4-47.2,11,7.5,32.2,34.7-8.1a349,349,0,0,0,83.7,32.4l7.9-32a316.7,316.7,0,0,1-86.3-35.7ZM512,226c-158,.1-285.9,128.2-285.9,286.1a286.7,286.7,0,0,0,43.9,152L242.5,781.5,359.8,754c133.7,84.1,310.3,44,394.4-89.6S798.3,354.2,664.7,270A286.7,286.7,0,0,0,512,226s"/>
                            </symbol>
                        </defs>
                    </svg>
                    <span class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                            <use href="#signal-icon"></use>
                        </svg>
                    </span>
                    Signal
                </a>

            </div>
        </div>

        <div v-if="!hasAnyContactInfo" class="no-contact">
            {{ $trans('No contact information available for this user.') }}
        </div>
    </ui-panel>
    <div v-else class="loading">

    </div>

</template>

<style scoped>



    .service-icon img {
        width: 16px;
        height: 16px;
    }
    .contact-information {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .contact-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }



    .services-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .service-button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
        cursor: pointer;
    }

    .service-icon {
        font-size: 16px;
    }

    .service-button.whatsapp {
        background-color: #25D366;
        color: white;
    }

    .service-button.whatsapp:hover {
        background-color: #22c55e;
    }

    .service-button.telegram {
        background-color: #0088cc;
        color: white;
    }

    .service-button.telegram:hover {
        background-color: #0077b5;
    }

    .service-button.signal {
        background-color: #3a76f0;
        color: white;
    }

    .service-button.signal:hover {
        background-color: #2563eb;
    }

 
</style>

<script>
import backend from '../../backend.js'
import ProfileContactEdit from './profile-contact-edit.vue'

export default {
    name: 'profile-contact-information',
    data() {
        return {
            contactLoaded: false,
            contact: {
                phone_number: null,
                services: [],
                email_address: null
            }
        }
    },
    components: {
        ProfileContactEdit
    },
    props: {
        profile: {
            type: Object,
            required: true
        }
    },
    computed: {
        hasAnyContactInfo() {
            return this.contact.email_address || 
                   this.contact.phone_number 
        },
        phoneNumberDigits() {
            if (!this.contact.phone_number) return ''
            return this.contact.phone_number.replace(' ', '')
//            return this.contact.phone_number.replace(/\D/g, '')
        }
    },
    methods: {
        getWhatsAppLink() {
            // WhatsApp uses wa.me/{phone_number}
            return `https://wa.me/${this.phoneNumberDigits}`
        },
        getTelegramLink() {
            // Telegram uses t.me/{phone_number}
            return `https://t.me/${this.phoneNumberDigits}`
        },
        getSignalLink() {
            // Signal uses signal.me/#p/{phone_number} or sgnl://signal.me/#p/{phone_number}
            return `https://signal.me/#p/${this.phoneNumberDigits}`
        },
        refreshContactInfo() {
            backend.get('/users/' + this.profile.user_id + '/profile/contact_information').then(response => {
                this.contact = response.data.contact_information
            }).finally(() => {
                this.contactLoaded = true
            })
        }
    },
    mounted() {
        backend.get('/users/' + this.profile.user_id + '/profile/contact_information').then(response => {
            if (response.data.contact_information)
            this.contact = response.data.contact_information
        }).finally(() => {
            this.contactLoaded = true
        })
    }
}
</script>