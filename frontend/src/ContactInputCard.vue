<template>
  <div class="box">
    <div class="columns">
      <div class="column is-half">
        <div class="control has-icons-left has-icons-right">
          <input
            class="input"
            type="text"
            placeholder="First name"
            :disabled="c_contact.removed"
            v-model.trim="$v.c_contact.attributes.first_name.$model"
          />
          <span class="icon is-small is-left">
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
              class="feather feather-user"
            >
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </span>
        </div>
        <div
          class="error has-text-danger margin-top-2 is-size-7"
          v-if="!$v.c_contact.attributes.first_name.required && ! c_contact.removed"
        >First name is required</div>
      </div>

      <div class="column is-half">
        <div class="control has-icons-left has-icons-right">
          <input
            class="input"
            type="text"
            placeholder="Last name"
            :disabled="c_contact.removed"
            v-model.trim="$v.c_contact.attributes.last_name.$model"
          />
          <span class="icon is-small is-left">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="currentColor"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="feather feather-user"
            >
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </span>
        </div>
        <div
          class="error has-text-danger margin-top-2 is-size-7"
          v-if="!$v.c_contact.attributes.last_name.required && ! c_contact.removed"
        >Last name is required</div>
      </div>
    </div>

    <div class="control has-icons-left has-icons-right margin-bottom-3">
      <input
        class="input"
        type="email"
        placeholder="Email"
        :disabled="c_contact.removed"
        v-model="c_contact.attributes.email"
      />
      <span class="icon is-small is-left">
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
          class="feather feather-mail"
        >
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
          <polyline points="22,6 12,13 2,6" />
        </svg>
      </span>
    </div>
    <div
      class="error has-text-danger margin-bottom-2 is-size-7"
      v-if="!$v.c_contact.attributes.email.required && ! c_contact.removed"
    >Email is required</div>
    <div
      class="error has-text-danger margin-bottom-2 is-size-7"
      v-if="!$v.c_contact.attributes.email.email && ! c_contact.removed"
    >Email must be in correct format</div>
    <button
      :class="{'is-danger': !c_contact.removed}"
      class="button is-small is-light is-fullwidth"
      @click="remove"
    >
      <span v-if="!c_contact.removed">Remove</span>
      <span v-else>Undo Remove</span>
    </button>
  </div>
</template>

<script>
import { required, email } from "vuelidate/lib/validators";
export default {
  /***
   * TODO - Normalize the ContactCard and ContactInputCard components.
   * It was advised not to change the structure of the project
   * hence this component was split apart despite overarching similarities
   */
  data() {
    return {
      c_contact: null
    };
  },

  validations: {
    c_contact: {
      attributes: {
        first_name: {
          required
        },
        last_name: {
          required
        },
        email: {
          required,
          email
        }
      }
    }
  },

  created() {
    this.c_contact = this.contact;
  },

  props: {
    contact: {
      required: true
    }
  },

  methods: {
    remove: function() {
      /**
       * $forceUpdate due to the nature of deep changes
       */
      this.c_contact.removed = !this.c_contact.removed ? true : false;
      this.$forceUpdate();
    }
  },

  /**
   * Updates the contact object from the parent view.
   * Direct changes to props should be avoided
   */
  watch: {
    c_contact: {
      handler(contact) {
        if (!contact.removed) contact.invalid = this.$v.$invalid;
        else contact.invalid = false;
        this.$emit("input", contact);
      },
      deep: true
    }
  }
};
</script>
