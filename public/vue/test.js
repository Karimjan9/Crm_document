const EmployeeComponent = {
    data(){
        return {
            name : 'vadim',
            login : 'redgus'
        }
    }
}
const app = Vue.createApp(EmployeeComponent)

app.component('main-component', {
    props: ['name', 'login'],
    template: `
        <div v-if="1 == 1 ">
            {{ name }}
        </div>

        <div>
            {{ login }}
        </div>
      `
});

const vm = app.mount('#test');