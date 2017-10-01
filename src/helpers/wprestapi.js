import axios from 'axios';

export const WPRESTAPI = axios.create({
	baseURL: regenerateThumbnails.wpApiSettings.root,
	headers: {
		"X-WP-Nonce": regenerateThumbnails.wpApiSettings.nonce,
	}
})