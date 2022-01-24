<template>
    <div class='spin' v-if='loading'>  
      <div class='ring'>
        <div class='message'>{{message}}</div>
          <div class="lds-ripple">
            <div></div>
            <div></div>
          </div>
      </div>
    </div>
</template>

<script>
    export default {
      props: {
        message: {
            type: String, 
            default: 'check',
        },        
        loading: {
            type: Boolean,
            default: false,
        },
    },
    }
</script>

<style lang='scss' scoped>
    .message {
      margin-bottom: 10px;
      color: white;
      font-size: 26px;
    }

    .spin {
        z-index:10;
        top: 0;
        width:100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
        position: fixed;
    }

    .ring {
      display: flex;
      width: 100%;
      justify-content: center;
      align-items: center;
      height: 100%;
      flex-direction: column;
    }

    .lds-ripple {
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;
}
.lds-ripple div {
  position: absolute;
  border: 4px solid #fff;
  opacity: 1;
  border-radius: 50%;
  animation: lds-ripple 1s cubic-bezier(0, 0.2, 0.8, 1) infinite;
}
.lds-ripple div:nth-child(2) {
  animation-delay: -0.5s;
}
@keyframes lds-ripple {
  0% {
    top: 36px;
    left: 36px;
    width: 0;
    height: 0;
    opacity: 1;
  }
  100% {
    top: 0px;
    left: 0px;
    width: 72px;
    height: 72px;
    opacity: 0;
  }
}
</style>