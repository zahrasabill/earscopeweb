<template lang="">
    <div class="container">
    <div class="d-flex vh-100 bg-light justify-content-center align-items-center">
        <div class="card p-4 shadow" style="width: 350px;">
            <h3 class="text-center">Login</h3>
            <div class="card-body">
            <form @submit.prevent>
                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" v-model="email" class="form-control" id="email" placeholder="Masukkan email" required>
                </div>
                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" v-model="password" class="form-control" id="password" placeholder="Masukkan password" required>
                </div>
                <button @click="login()" class="btn btn-primary btn-block form-control">Login</button>
            </form>
            </div>
        </div>
    </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            email: "",
            password: ""
        }
    },
    methods: {
        login(){
            console.log(this.email)
            console.log(this.password)
        }
    },
}
</script>
<style lang="">
    
</style>