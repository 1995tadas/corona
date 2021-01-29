<template>
    <div class="slider">
        <a class="slider-arrow" @click.prevent="changeSlide('+')" href="#">
            <i class="fas fa-caret-square-left"></i>
        </a>
        <slot :name="currentSlide"></slot>
        <a class="slider-arrow" @click.prevent="changeSlide('-')" href="#">
            <i class="fas fa-caret-square-right"></i>
        </a>
    </div>
</template>
<script>
export default {
    props: {
        numberOfSlots: {
            type: Number,
            required: true
        },
    },
    data() {
        return {
            currentSlide: 1
        }
    },
    methods: {
        changeSlide() {
            if ('+') {
                if (this.currentSlide === this.numberOfSlots) {
                    this.currentSlide = 1
                } else {
                    this.currentSlide++;
                }
            } else if ('-') {
                if (this.currentSlide === 1) {
                    this.currentSlide = this.numberOfSlots
                } else {
                    this.currentSlide--;
                }
            }
        }
    }
}
</script>
