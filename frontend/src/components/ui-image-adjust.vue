<template>
    <div class="image-adjust-container">
        <div class="preview-frame" :style="frameStyle" ref="frame">
            <div 
                class="image-wrapper"
                :style="imageWrapperStyle"
                @mousedown="startDrag"
                @touchstart="startDrag"
            >
                <img 
                    :src="previewUrl" 
                    :style="imageStyle"
                    draggable="false"
                    @load="onImageLoad"
                >
            </div>
        </div>

        <div class="controls">
            <div class="control-group">
                <label>Scale</label>
                <input 
                    type="range" 
                    v-model.number="scale" 
                    :min="minScale" 
                    :max="maxScale" 
                    step="0.01"
                    class="scale-slider"
                >
                <span class="scale-value">{{ Math.round(scale * 100) }}%</span>
            </div>

            <div class="control-buttons">
                <button @click="resetPosition" class="btn btn-secondary btn-sm">
                    Reset Position
                </button>
                <button @click="fitToFrame" class="btn btn-secondary btn-sm">
                    Fit to Frame
                </button>
            </div>
        </div>

        <div class="result-info" v-if="showDebug">
            <h6>Transform Parameters:</h6>
            <pre>{{ transformParams }}</pre>
            <h6>Cloudinary URL:</h6>
            <code class="cloudinary-url">{{ cloudinaryUrl }}</code>
        </div>
    </div>
</template>

<style scoped>
.image-adjust-container {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
}

.preview-frame {
    position: relative;
    overflow: hidden;
    background-color: #fff;
    border: 2px solid #ddd;
    border-radius: 4px;
    margin-bottom: 20px;
}

.image-wrapper {
    position: absolute;
    cursor: move;
    user-select: none;
    -webkit-user-select: none;
}

.image-wrapper img {
    display: block;
    pointer-events: none;
    width: 100%;
    height: auto;
}

.controls {
    background: #fff;
    padding: 20px;
    border-radius: 4px;
    border: 1px solid #ddd;
    margin-bottom: 20px;
}

.control-group {
    margin-bottom: 15px;
}

.control-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.scale-slider {
    width: calc(100% - 60px);
    margin-right: 10px;
    vertical-align: middle;
}

.scale-value {
    display: inline-block;
    min-width: 50px;
    text-align: right;
    font-weight: 600;
    color: #666;
}

.control-buttons {
    display: flex;
    gap: 10px;
}

.control-buttons button {
    flex: 1;
}

.result-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.result-info h6 {
    margin-top: 0;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 600;
}

.result-info pre {
    background: #fff;
    padding: 10px;
    border-radius: 4px;
    font-size: 12px;
    margin-bottom: 15px;
    overflow-x: auto;
}

.cloudinary-url {
    display: block;
    background: #fff;
    padding: 10px;
    border-radius: 4px;
    font-size: 11px;
    word-break: break-all;
    color: #0066cc;
}
</style>

<script>
export default {
    name: 'ui-image-adjust',
    data() {
        return {
            scale: 1,
            offsetX: 0,
            offsetY: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            originalImageWidth: 0,
            originalImageHeight: 0,
            imageLoaded: false,
            naturalImageWidth: 0,
            naturalImageHeight: 0,
            previewImageWidth: 0,
            previewImageHeight: 0,
            previousScale: 1
        }
    },
    props: {
        src: {
            type: String,
            required: true
        },
        frameWidth: {
            type: Number,
            default: 800
        },
        frameHeight: {
            type: Number,
            default: 450
        },
        modelValue: {
            type: Object,
            default: () => ({
                scale: 1,
                offsetX: 0,
                offsetY: 0
            })
        },
        showDebug: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        minScale() {
            return 0.1
        },
        maxScale() {
            if (!this.imageLoaded || !this.naturalImageWidth) {
                return 3
            }
            
            const maxScaleX = this.naturalImageWidth / this.originalImageWidth
            const maxScaleY = this.naturalImageHeight / this.originalImageHeight
            
            const calculatedMax = Math.min(maxScaleX, maxScaleY)
            
            return Math.max(1, calculatedMax)
        },
        frameStyle() {
            return {
                width: '100%',
                paddingTop: (this.frameHeight / this.frameWidth * 100) + '%'
            }
        },
        imageWrapperStyle() {
            return {
                width: this.scaledImageWidth + 'px',
                height: this.scaledImageHeight + 'px',
                left: this.offsetX + 'px',
                top: this.offsetY + 'px',
                transform: 'translate3d(0, 0, 0)'
            }
        },
        imageStyle() {
            return {
                width: '100%',
                height: 'auto'
            }
        },
        scaledImageWidth() {
            if (!this.imageLoaded) return 0
            return this.originalImageWidth * this.scale
        },
        scaledImageHeight() {
            if (!this.imageLoaded) return 0
            return this.originalImageHeight * this.scale
        },
        frameActualWidth() {
            return this.$refs.frame ? this.$refs.frame.offsetWidth : this.frameWidth
        },
        frameActualHeight() {
            return this.$refs.frame ? this.$refs.frame.offsetHeight : this.frameHeight
        },
        previewUrl() {
            const previewWidth = Math.min(this.frameWidth * 2, 1600)
            
            if (this.src.startsWith('https://')) {
                return this.src
            }
            
            const source = this.$config.platform.img_storage_url + '/' + this.src
            return `https://d34m5sods6fnic.cloudfront.net/image/fetch/w_${previewWidth}/${source}`
        },
        
        originalImageUrl() {
            if (this.src.startsWith('https://')) {
                return this.src
            }
            return this.$config.platform.img_storage_url + '/' + this.src
        },
        
        cloudinaryParams() {
            if (!this.imageLoaded || !this.naturalImageWidth) {
                return { c: 'fill', w: this.frameWidth, h: this.frameHeight }
            }

            const needsPadding = this.scaledImageWidth < this.frameActualWidth || this.scaledImageHeight < this.frameActualHeight
            
            if (needsPadding) {
                // Calculate image dimensions as proportion of frame
                const scaleRatioX = this.scaledImageWidth / this.frameActualWidth
                const scaleRatioY = this.scaledImageHeight / this.frameActualHeight
                
                const targetWidth = Math.round(this.frameWidth * scaleRatioX)
                const targetHeight = Math.round(this.frameHeight * scaleRatioY)
                
                // Calculate gravity/offset for positioning within padded frame
                // Offset is position of image within frame, convert to percentage or position
                const offsetXPercent = this.offsetX / this.frameActualWidth
                const offsetYPercent = this.offsetY / this.frameActualHeight
                
                // Calculate where image should be in output frame
                const outputOffsetX = Math.round(this.frameWidth * offsetXPercent)
                const outputOffsetY = Math.round(this.frameHeight * offsetYPercent)
                
                return {
                    mode: 'pad',
                    w: targetWidth,
                    h: targetHeight,
                    offsetX: outputOffsetX,
                    offsetY: outputOffsetY,
                    usePad: true
                }
            }
            
            // Image is larger than frame - calculate crop
            const displayToNaturalRatio = this.naturalImageWidth / this.originalImageWidth
            
            let visibleStartX = 0
            let visibleStartY = 0
            let visibleWidth = this.scaledImageWidth
            let visibleHeight = this.scaledImageHeight
            
            if (this.offsetX < 0) {
                visibleStartX = -this.offsetX
                visibleWidth = Math.min(this.scaledImageWidth + this.offsetX, this.frameActualWidth)
            } else {
                visibleWidth = Math.min(this.scaledImageWidth, this.frameActualWidth - this.offsetX)
            }
            
            if (this.offsetY < 0) {
                visibleStartY = -this.offsetY
                visibleHeight = Math.min(this.scaledImageHeight + this.offsetY, this.frameActualHeight)
            } else {
                visibleHeight = Math.min(this.scaledImageHeight, this.frameActualHeight - this.offsetY)
            }
            
            const unscaledStartX = visibleStartX / this.scale
            const unscaledStartY = visibleStartY / this.scale
            const unscaledWidth = visibleWidth / this.scale
            const unscaledHeight = visibleHeight / this.scale
            
            const cropX = Math.round(unscaledStartX * displayToNaturalRatio)
            const cropY = Math.round(unscaledStartY * displayToNaturalRatio)
            const cropWidth = Math.round(unscaledWidth * displayToNaturalRatio)
            const cropHeight = Math.round(unscaledHeight * displayToNaturalRatio)
            
            return {
                mode: 'crop',
                x: Math.max(0, Math.min(cropX, this.naturalImageWidth)),
                y: Math.max(0, Math.min(cropY, this.naturalImageHeight)),
                w: Math.min(cropWidth, this.naturalImageWidth - Math.max(0, cropX)),
                h: Math.min(cropHeight, this.naturalImageHeight - Math.max(0, cropY)),
                usePad: false
            }
        },
        
        cloudinaryUrl() {
            const params = this.cloudinaryParams
            const source = this.src.startsWith('https://') 
                ? this.src 
                : this.$config.platform.img_storage_url + '/' + this.src
            
            if (params.usePad) {
                // For scaled-down images, just center them with padding
                // Custom positioning would require server-side image processing
                return `https://d34m5sods6fnic.cloudfront.net/image/fetch/c_limit,w_${params.w},h_${params.h}/c_lpad,w_${this.frameWidth},h_${this.frameHeight},b_white/${source}`
            }
            
            return `https://d34m5sods6fnic.cloudfront.net/image/fetch/c_crop,x_${params.x},y_${params.y},w_${params.w},h_${params.h},g_north_west/c_fill,w_${this.frameWidth},h_${this.frameHeight}/${source}`
        },
        
        transformParams() {
            const params = this.cloudinaryParams
            return {
                scale: this.scale,
                offsetX: this.offsetX,
                offsetY: this.offsetY,
                frameWidth: this.frameActualWidth,
                frameHeight: this.frameActualHeight,
                naturalWidth: this.naturalImageWidth,
                naturalHeight: this.naturalImageHeight,
                scaledWidth: this.scaledImageWidth,
                scaledHeight: this.scaledImageHeight,
                maxScale: this.maxScale,
                cloudinaryParams: params
            }
        }
    },
    watch: {
        scale(newScale, oldScale) {
            if (oldScale !== newScale && this.imageLoaded) {
                const scaleDiff = newScale / oldScale
                
                const centerX = this.frameActualWidth / 2
                const centerY = this.frameActualHeight / 2
                
                const imagePointX = centerX - this.offsetX
                const imagePointY = centerY - this.offsetY
                
                this.offsetX = centerX - (imagePointX * scaleDiff)
                this.offsetY = centerY - (imagePointY * scaleDiff)
            }
            
            this.constrainPosition()
            this.emitValue()
        },
        offsetX() {
            this.emitValue()
        },
        offsetY() {
            this.emitValue()
        },
        modelValue: {
            handler(val) {
                if (val) {
                    this.scale = val.scale || 1
                    this.offsetX = val.offsetX || 0
                    this.offsetY = val.offsetY || 0
                }
            },
            immediate: true
        },
        maxScale(newMax) {
            if (this.scale > newMax) {
                this.scale = newMax
            }
        }
    },
    methods: {
        onImageLoad(e) {
            const img = e.target
            this.previewImageWidth = img.naturalWidth
            this.previewImageHeight = img.naturalHeight
            
            const fullImg = new Image()
            fullImg.onload = () => {
                this.naturalImageWidth = fullImg.width
                this.naturalImageHeight = fullImg.height
                
                this.originalImageWidth = this.frameActualWidth
                this.originalImageHeight = (this.naturalImageHeight / this.naturalImageWidth) * this.frameActualWidth
                this.imageLoaded = true
                this.fitToFrame()
            }
            fullImg.onerror = () => {
                console.warn('Failed to load full image, using preview dimensions')
                this.naturalImageWidth = this.previewImageWidth
                this.naturalImageHeight = this.previewImageHeight
                this.originalImageWidth = this.frameActualWidth
                this.originalImageHeight = (this.naturalImageHeight / this.naturalImageWidth) * this.frameActualWidth
                this.imageLoaded = true
                this.fitToFrame()
            }
            fullImg.src = this.originalImageUrl
        },
        startDrag(e) {
            if (e.type === 'mousedown') {
                e.preventDefault()
            }
            
            this.isDragging = true
            const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX
            const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY
            
            this.dragStartX = clientX - this.offsetX
            this.dragStartY = clientY - this.offsetY
            
            document.addEventListener('mousemove', this.onDrag)
            document.addEventListener('touchmove', this.onDrag)
            document.addEventListener('mouseup', this.stopDrag)
            document.addEventListener('touchend', this.stopDrag)
        },
        onDrag(e) {
            if (!this.isDragging) return
            
            const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX
            const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY
            
            this.offsetX = clientX - this.dragStartX
            this.offsetY = clientY - this.dragStartY
            
            this.constrainPosition()
        },
        stopDrag() {
            this.isDragging = false
            document.removeEventListener('mousemove', this.onDrag)
            document.removeEventListener('touchmove', this.onDrag)
            document.removeEventListener('mouseup', this.stopDrag)
            document.removeEventListener('touchend', this.stopDrag)
        },
        constrainPosition() {
            if (this.scaledImageWidth < this.frameActualWidth) {
                this.offsetX = Math.max(0, Math.min(this.frameActualWidth - this.scaledImageWidth, this.offsetX))
            } else {
                const maxOffsetX = 0
                const minOffsetX = this.frameActualWidth - this.scaledImageWidth
                this.offsetX = Math.max(minOffsetX, Math.min(maxOffsetX, this.offsetX))
            }
            
            if (this.scaledImageHeight < this.frameActualHeight) {
                this.offsetY = Math.max(0, Math.min(this.frameActualHeight - this.scaledImageHeight, this.offsetY))
            } else {
                const maxOffsetY = 0
                const minOffsetY = this.frameActualHeight - this.scaledImageHeight
                this.offsetY = Math.max(minOffsetY, Math.min(maxOffsetY, this.offsetY))
            }
        },
        resetPosition() {
            this.offsetX = (this.frameActualWidth - this.scaledImageWidth) / 2
            this.offsetY = (this.frameActualHeight - this.scaledImageHeight) / 2
            this.constrainPosition()
        },
        fitToFrame() {
            if (!this.imageLoaded) return
            
            const scaleX = this.frameActualWidth / this.originalImageWidth
            const scaleY = this.frameActualHeight / this.originalImageHeight
            
            this.scale = Math.min(Math.max(scaleX, scaleY), this.maxScale)
            
            this.$nextTick(() => {
                this.offsetX = (this.frameActualWidth - this.scaledImageWidth) / 2
                this.offsetY = (this.frameActualHeight - this.scaledImageHeight) / 2
                this.constrainPosition()
            })
        },
        emitValue() {
            this.$emit('update:modelValue', {
                scale: this.scale,
                offsetX: this.offsetX,
                offsetY: this.offsetY,
                cloudinaryParams: this.cloudinaryParams,
                cloudinaryUrl: this.cloudinaryUrl
            })
        }
    },
    mounted() {
        if (this.modelValue) {
            this.scale = this.modelValue.scale || 1
            this.offsetX = this.modelValue.offsetX || 0
            this.offsetY = this.modelValue.offsetY || 0
        }
    },
    beforeUnmount() {
        this.stopDrag()
    }
}
</script>