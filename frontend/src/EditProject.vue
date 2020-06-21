<template>
  <div class="view-project">
    <header>
      <div class="level is-mobile margin-bottom-3">
        <router-link to="/projects" class="level-left">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="feather feather-arrow-left-circle"
          >
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 8 8 12 12 16" />
            <line x1="16" y1="12" x2="8" y2="12" />
          </svg>
          <span class="margin-left-2">All projects</span>
        </router-link>
        <div class="buttons level-right" v-if="project">
          <router-link :to="redirectPath" class="button is-small">Cancel</router-link>
          <button
            class="button is-info is-small"
            :class="{'is-loading': saving}"
            :disabled="saving"
            @click="saveProject"
          >Save project</button>
        </div>
      </div>
    </header>
    <article v-if="project" class="columns">
      <div class="column">
        <label class="is-size-7 has-text-weight-bold">Project</label>
        <div class="control mb-3">
          <input
            class="input"
            type="text"
            placeholder="e.g James Smith's House"
            v-model="project.attributes.title"
          />
        </div>
        <div
          class="error has-text-danger margin-top-2 is-size-7"
          v-if="!$v.project.attributes.title.required"
        >Title is required</div>

        <label class="is-size-7 has-text-weight-bold">System size</label>
        <div class="control mb-3  has-icons-right">
          <input
            class="input"
            type="text"
            placeholder="Value in KW"
            v-model="project.attributes.system_size"
          />
           <span class="icon is-small is-right">
               kW
               </span>
        </div>
        <div
          class="error has-text-danger margin-top-2 is-size-7"
          v-if="!$v.project.attributes.system_size.decimal"
        >System size must be numeric or decimal</div>

        <label class="is-size-7 has-text-weight-bold">System details</label>
        <div class="control mb-3">
          <input
            class="input"
            type="text"
            placeholder="Value in KW"
            v-model="project.attributes.system_details"
          />
        </div>

         <div v-if="errors" class="has-text-danger margin-top-4">Please resolve errors</div>
      </div>

      <div class="column">
        <contact-input-card
          v-for="(contact, index) in contacts"
          :key="index"
          :contact="contact"
          class="view-project__contact"
          @input="handleInputEvent"
        />
        <button class="button is-fullwidth is-light" @click="addContact">Add contact</button>
      </div>
    </article>
  </div>
</template>

<script>

/**
 * TODO 
 * Large similarities between the EditProject and ViewProject Component. Able to be normalized
 * by taking similar methods and attributes and creating a root component. 
 * Was not done as the task instructions insisted not changing the layout of the project and to keep the base files as 
 * it was.
 * 
 * Same can be said for the ContactCard and ContactInputCard component.
 */

//Use of vuelidate. A lightweight model-based validation for Vue projects
import { http } from "./api.js";
import { required, numeric, decimal } from "vuelidate/lib/validators";
import ContactInputCard from "./ContactInputCard.vue";

export default {
  components: {
    ContactInputCard
  },

  data() {
    return {
      saving: false,
      errors: false,
      project: null,
      contacts: []
    };
  },

  validations: {
    project: {
      attributes: {
        title: {
          required
        },
        system_size: {
          decimal
        }
      }
    }
  },

  mounted() {
    /**
     * Duplicate of previous function. Could have passed data as props but data changes can happen concurrently.
     * Better to make use of
     */
    this.fetchProjectDetails();
    this.fetchProjectContacts();
  },

  computed: {

      //Computed attribute for redirect path. 
      redirectPath() {
          return '/projects/' + this.project.id
      }

  },

  methods: {
      /**
       * Methods as seen in the ViewProject component. Explanation for non normaliztion in the beginning of
       * the script.
       */
    async fetchProjectDetails() {
      let response = await http.get(
        "/solar_projects/" + this.$route.params.project_id
      );
      this.project = response.data.data;
    },

    async fetchProjectContacts() {
      this.contacts = [];
      let response = await http.get(
        "/solar_projects/" + this.$route.params.project_id + "/contacts"
      );
      response.data.data.forEach(async contact => {
        let response = await http.get("/contacts/" + contact.id);
        this.contacts.push(response.data.data);
      });
    },

    /**
     *  This function handles the update changes of the project based on the new inputs
     * as well as the Contact store, updates and deletes. It first checks the values for validation and 
     * proceeds to create Promise based requests on each item to ensure all requests gets handled prior
     * the conclusion of the task.
     * 
     * @return null
     */
    async saveProject() {
      if (this.validate()) {
        this.errors = false;
        this.saving = true;
        let contacts = [];
        let project = await http.patch(
          "/solar_projects/" + this.$route.params.project_id,
          this.project.attributes
        );

        /**
         * Note: Use of for...of loop is better than foreach for asynchronous events. Async functions within a
         * foreach loop will simply run concurrently but not await any results. This will lead to undesirable results with the Promise
         * not awaiting all http requests to complete.
         */
        for (const contact of this.contacts.filter(x => !x.removed)) {
          let response = null;
          if (!contact.id) {
            contacts.push(await http.post("/contacts/", contact.attributes));
          } else {
            contacts.push(
              await http.patch("/contacts/" + contact.id, contact.attributes)
            );
          }
        }

        for (const contact of this.contacts.filter(x => x.removed)) {
          /**
           * Contacts without a generated uuid does not need to be deleted
           * since these would have no existing record on the DB . Simply removed
           * from the list of contacts
           */
          if (contact.id) {
            let response = await http.delete("/contacts/" + contact.id);
            contacts.push(response);
          } else
            this.contacts.splice(
              this.contacts.findIndex(x => (x.id = contact.id)),
              1
            );
        }

        /***
         * Once everything is done creating / updating, synchronize the ifnromation with new contacts and etc....
         */

        Promise.all([project, ...contacts]).then(results => {
          results.shift();
          results = results
            .filter(x => x.data.data != null)
            .map(x => x.data.data);
          http
            .put(
              "/solar_projects/" + this.$route.params.project_id + "/contacts",
              {
                data: results
              }
            )
            .then(response => {
              this.fetchProjectContacts();
              this.saving = false;
              this.errors = false;
              /**
               * Redirects to the view page. Unnecessary but gives users context that their updates were successful
               */
              window.location.replace(this.redirectPath);
            }).catch(error => {
                // console.log(error.response.data)
                this.saving = false;
                this.errors = true
            });
        });
      } else this.errors = true;
    },

    /**
     * New and existing contacts are merged into an array for easier management
     * and UI rendering
     * 
     * @return null
     */
    addContact() {
      this.contacts.push({
        attributes: {
          first_name: null,
          last_name: null,
          email: null
        },
        id: null
      });
    },

    /**
     * Handles input changes for each Contact in the 'contacts' array
     * when changed through the ContactInputCard
     */
    handleInputEvent(contact) {
      let index = this.contacts.findIndex(x => x.id == contact.id);
      if (index > -1) this.contacts.splice(index, 1, contact);
    },

    /**
     * Validates the Project and Contacts input.
     * 
     * @return bool
     */
    validate() {
      return !this.$v.$invalid && !this.contacts.find(x => x.invalid);
    }
  }
};
</script>

