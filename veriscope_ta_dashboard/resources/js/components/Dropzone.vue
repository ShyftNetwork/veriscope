<template>
    <div>
        <form v-show=!successfulDrop :action=postUrl class="dropzone" :class="{ 'dropzone--compact': compact }" :id=targetElement>
            <div class="dz-message" data-dz-message>
                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                    width="59px" height="50px" viewBox="0 0 59 50" style="enable-background:new 0 0 59 50;" xml:space="preserve">
                <path fill="#D25F5F" d="M44.9,12.3c-0.1,0-0.2,0-0.2,0C42.9,5.5,36.8,0.6,29.4,0.6c-6.6,0-12.2,4-14.6,9.6
                    c-0.3,0-0.7-0.1-1-0.1c-4.9,0-8.8,4.1-8.3,9.5C2.2,21.2,0,24.5,0,28.3c0,5,3.8,9.1,8.8,9.7l15,0v-3.3L9,34.7
                    c-3.2-0.5-5.6-3.2-5.6-6.4c0-2.4,1.4-4.6,3.6-5.8l2-1l-0.2-2.2c-0.2-1.6,0.3-3.2,1.4-4.3c0.9-1,2.2-1.6,3.6-1.6c0.2,0,0.4,0,0.6,0
                    l2.5,0.3l1-2.3c1.9-4.6,6.4-7.5,11.5-7.5c5.7,0,10.6,3.8,12,9.2l0.7,2.6l2.7-0.1l0.1,0c0.5,0,3.3,0.1,5.8,1.4
                    c2.9,1.5,4.3,4.2,4.3,8.2c0,5.3-4.4,9.6-9.8,9.6l-10.6,0v3.3l10.6,0c7.3,0,13.2-5.8,13.2-12.9C58.3,12.7,46.6,12.3,44.9,12.3z"/>
                <path fill="#D25F5F" d="M42.7,26.2L30.1,15.6c0,0-0.1,0-0.1-0.1c0,0-0.1-0.1-0.1-0.1c-0.1,0-0.1-0.1-0.2-0.1
                    c0,0-0.1,0-0.1-0.1c-0.1,0-0.1,0-0.2-0.1c0,0-0.1,0-0.1,0c-0.1,0-0.2,0-0.3,0c0,0,0,0,0,0c0,0,0,0,0,0c-0.1,0-0.2,0-0.3,0
                    c0,0-0.1,0-0.1,0c-0.1,0-0.1,0-0.2,0.1c0,0-0.1,0-0.1,0.1c-0.1,0-0.1,0.1-0.2,0.1c0,0-0.1,0.1-0.1,0.1c0,0-0.1,0-0.1,0.1L15.6,26.2
                    c-0.7,0.6-0.8,1.7-0.1,2.4c0.6,0.7,1.7,0.8,2.4,0.1l9.4-8.2v27.3c0,0.9,0.8,1.7,1.7,1.7c0.9,0,1.7-0.7,1.7-1.7V20.5l9.8,8.3
                    c0.3,0.3,0.7,0.4,1.1,0.4c0.5,0,1-0.2,1.3-0.6C43.5,27.9,43.4,26.8,42.7,26.2z"/>
                </svg>
                <p><strong>Drop file here or <span class="dropzone__text-link">select file</span></strong></p>
            </div>
            <div class="fallback">
                <input type="file" name="photo_id" id="photo_id" />
            </div>
        </form>
        <div v-show=successfulDrop class="my-8">
            <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">Your photo was successfully attached.</strong> <button class="btn btn--plain" @click=resetDropzone>Attach a new photo</button></p>
        </div>
    </div>
</template>
<script>
    import {
        mapGetters,
        mapMutations,
    } from 'vuex';
    import Dropzone from 'dropzone';

    Dropzone.autoDiscover = false;

    export default {
        data() {
            return {
                dzone: null,
                dzoneInitialized: false,
                successfulDrop: false,
            }
        },
        computed: {
            ...mapGetters([
                'CSRF',
            ]),
            targetElementId: function() {
                return `#${this.targetElement}`;
            },
        },
        props: {
            targetElement: {
                type: String,
                required: false,
                default: 'dropzone'
            },
            postUrl: {
                type: String,
                required: true
            },
            onSuccess: {
                type: Function,
                required: true
            },
            onRemove: {
                type: Function,
                required: false,
            },
            addRemoveLinks: {
                type: Boolean,
                required: false,
                default: false,
            },
            compact: {
                type: Boolean,
                required: false,
                default: false
            }

        },
        methods: {
            loadExistingUserPhoto: function({ filename, filesize }) {
                const mockfile = { name: 'Filename', size: filesize, dataURL:filename };
                this.dzone.emit('addedfile', mockfile);
                this.dzone.emit('thumbnail', mockfile, filename);
                this.dzone.emit('complete', mockfile);
                this.dzone.options.maxFiles = this.dzone.options.maxFiles - 1;
                this.dzoneInitialized = true;
            },
            resetDropzone: function() {
                this.dzone.removeAllFiles();
                this.successfulDrop = false;
            },
            initializeDropzone() {
                const VM = this;
                this.dzone = new Dropzone(this.targetElementId, {
                    paramName: "photo_id", // The name that will be used to transfer the file
                    maxFilesize: 30,
                    acceptedFiles: "image/jpg, image/jpeg, image/bmp, image/png, image/gif, application/pdf",
                    maxFiles: 1,
                    addRemoveLinks: this.addRemoveLinks,
                    headers: {
                        'X-CSRF-TOKEN' : this.CSRF,
                        'X-Requested-With' : 'XMLHttpRequest',
                    },
                    init: function() {
                        // Fired when an image is removed from Dropzone
                        // This allows us to remove any additional images
                        // that are dropped into the Dropzone, since we only
                        // allow for 1. The original image will never have a
                        // status. Only newly dropped images do. Further, if we have
                        // removed the original in favor of a replacement, the new
                        // image would only be applicable with a Success status flag
                        // so we also check for that.
                        this.on('removedfile', file => {
                            if(!file.status || (file.status && file.status === 'success')) {
                                VM.onRemove();
                                this.options.maxFiles = 1;
                            }
                        });
                        // Fired when a file is added to Dropzone
                        this.on('addedfile', () => VM.$emit('added-file'));

                    },
                    success: (file, response) => {
                        // local state update
                        this.successfulDrop = true;
                        // success hook
                        this.onSuccess(response.photo_id)
                    },
                });
            }
        },
        mounted: function () {
            this.initializeDropzone();
        },
    }
</script>
