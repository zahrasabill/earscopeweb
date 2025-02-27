<template>
    <div class="container-fluid vh-100 d-flex align-items-center bg-dark-blue">
        <div class="row w-100">
            <!-- Left Side -->
            <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center bg-light-blue">
                <h1 class="text-white">Welcome Back!</h1>
            </div>
            
            <!-- Right Side - Login Card -->
            <div class="col-md-6 d-flex justify-content-center align-items-center">
                <div class="card p-4 shadow" style="width: 350px; background-color: white;">
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

<style scoped>
.bg-dark-blue {
    background-color: #001F3F; /* Biru Tua */
}
.bg-light-blue {
    background-color: #0074D9; /* Biru Muda */
}
</style>
