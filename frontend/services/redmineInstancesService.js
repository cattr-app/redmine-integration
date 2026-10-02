import axios from '@/config/app';

export default class RedmineInstancesService {
    /**
     * API endpoint URL
     * @returns string
     */
    getItemRequestUri() {
        return `/integration/redmine/instances`;
    }

    /**
     * Fetch item list from api endpoint
     * @returns {data}
     */
    async list() {
        const { data } = await axios.get(this.getItemRequestUri(), { cancelIgnore: true });
        return data;
    }

    /**
     * Fetch item from api endpoint
     * @returns {data}
     */
    async show(id) {
        const { data } = await axios.get(`${this.getItemRequestUri()}/${id}`, { cancelIgnore: true });
        return data;
    }

    /**
     * Create item
     * @param payload
     * @returns {Promise<void>}
     */
    async create(payload) {
        const { data } = await axios.post(this.getItemRequestUri(), payload);
        return data;
    }

    /**
     * Update item
     * @param id
     * @param payload
     * @returns {Promise<void>}
     */
    async update(id, payload) {
        const { data } = await axios.put(`${this.getItemRequestUri()}/${id}`, payload);
        return data;
    }

    /**
     * Delete item
     * @param id
     * @returns {Promise<void>}
     */
    async delete(id) {
        const { data } = await axios.delete(`${this.getItemRequestUri()}/${id}`);
        return data;
    }

    /**
     * Fetch item list from api endpoint
     * @param instanceId
     * @returns {data}
     */
    async getPriorities(instanceId) {
        const { data } = await axios.get(`${this.getItemRequestUri()}/${instanceId}/priorities`, {
            cancelIgnore: true,
        });

        return data;
    }

    /**
     * Fetch item list from api endpoint
     * @param instanceId
     * @returns {data}
     */
    async getStatuses(instanceId) {
        const { data } = await axios.get(`${this.getItemRequestUri()}/${instanceId}/statuses`, {
            cancelIgnore: true,
        });

        return data;
    }

    /**
     * Update item
     * @param instanceId
     * @param id
     * @param payload
     * @returns {Promise<void>}
     */
    async updatePriority(instanceId, id, payload) {
        const { data } = await axios.put(`${this.getItemRequestUri()}/${instanceId}/priorities/${id}`, payload);
        return data;
    }

    /**
     * Update item
     * @param instanceId
     * @param payload
     * @returns {Promise<void>}
     */
    async bulkUpdatePriority(instanceId, payload) {
        const { data } = await axios.put(`${this.getItemRequestUri()}/${instanceId}/priorities`, payload);
        return data;
    }

    /**
     * Update item
     * @param instanceId
     * @param id
     * @param payload
     * @returns {Promise<void>}
     */
    async updateStatus(instanceId, id, payload) {
        const { data } = await axios.put(`${this.getItemRequestUri()}/${instanceId}/statuses/${id}`, payload);
        return data;
    }

    /**
     * Update item
     * @param instanceId
     * @param payload
     * @returns {Promise<void>}
     */
    async bulkUpdateStatus(instanceId, payload) {
        const { data } = await axios.put(`${this.getItemRequestUri()}/${instanceId}/statuses`, payload);
        return data;
    }

    /**
     * Fetch item list from api endpoint
     * @param instanceId
     * @returns {data}
     */
    async getPojects(instanceId) {
        const { data } = await axios.get(`${this.getItemRequestUri()}/${instanceId}/projects`, {
            cancelIgnore: true,
        });

        return data;
    }

    /**
     * Update item
     * @param instanceId
     * @param id
     * @param payload
     * @returns {Promise<void>}
     */
    async updateProject(instanceId, id, payload) {
        const { data } = await axios.put(`${this.getItemRequestUri()}/${instanceId}/projects/${id}`, payload);
        return data;
    }

    /**
     * Create item
     * @param instanceId
     * @param payload
     * @returns {Promise<void>}
     */
    async createUser(instanceId, payload) {
        const { data } = await axios.post(`${this.getItemRequestUri()}/${instanceId}/users`, payload);
        return data;
    }

    /**
     * Check auth
     * @param instanceId
     * @returns {Promise<void>}
     */
    async checkAuth(instanceId) {
        const { data } = await axios.post(`${this.getItemRequestUri()}/${instanceId}/check-auth`, {});
        return data;
    }
}
