const EmployeeComponent = {
    data(){
        return {
            data : users,
            criterias : criterias
        }
    }
}

const app = Vue.createApp(EmployeeComponent)

app.component('button-counter', {
    data() {
      return {
        count: 0
      }
    },
    template: `
      <button @click="count++">
        Счётчик кликов — {{ count }}
      </button>`
});

app.component('table-header', {
    props: ['criterias'],
    template: `
    
    <thead class="table-light">
        <tr>
            <th class="fixed_header2 align-middle">#</th>
            <th class="fixed_header2 align-middle">F.I.O </th>
            <th id="sort_status" class="fixed_header2 align-middle">Status <div id="icon_s"> <i class="lni lni-arrow-up"></i> </div></th>
            <th scope="col" class="align-middle fixed_header2"  style=" width: 100px; word-wrap: break-word !important;" v-for='criteria of criterias'>{{ criteria.name }}</th>
        </tr>
    </thead>
    `
});

const vm = app.mount('#app');