<template>
  <div class="list-contacts">
    <pagination v-if="pagination !== null" :data="pagination" v-model="page" @input="fetchContacts" />

    <div v-if="contacts !== null">
      <table class="table is-fullwidth">
        <tbody>
          <tr v-for="contact in contacts" :key="contact.id">
            <td>{{contact.attributes.first_name}}</td>
            <td>{{contact.attributes.last_name}}</td>
            <td>{{contact.attributes.email}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style>
.list-contacts {
  padding: 1rem;
}
</style>

<script>
import {http} from './api.js';

import Pagination from './Pagination.vue';

export default {
  components: {
    Pagination,
  },

  data() {
    return {
      page: 1,
      pagination: null,
      contacts: null,
    };
  },

  mounted() {
    this.fetchContacts();
  },

  methods: {
    // TODO: this is bugged, contacts appear as they load up rather than all at once. -- Bugged no more
    async fetchContacts() {
      this.contacts = null;
      let contacts = [];
      let params = {page: this.page};
      let response = await http.get('/contacts', {params});
      this.pagination = response.data.meta;
      response.data.data.forEach(async (contact, index) => {
        let response = http.get('/contacts/' + contact.id);
        contacts.push(response);
      });

      /**
       * Promise based approach was the quickest and most efficient way
       * of tackling the problem without making changes to API or the call.
       * Promise function commits data when all contact requests has been fulfilled.
       * There were nanosecond difference between each contact causing the flowing effect.
       */
      Promise.all(contacts).then(results => { //Awaits all promises to complete and parses all responses in the 'results' variable
        /**
         * Map the responses to an array format that is more suitable to pass into the state contacts array.
         */
        let mapped_contacts = results.map(contact => contact.data.data);
        this.contacts = mapped_contacts;
      });
    },
  },
}
</script>
