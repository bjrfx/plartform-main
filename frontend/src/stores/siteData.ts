import {defineStore} from "pinia";
import {get} from '@/services/api';
import {User} from '@/stores/auth';

// Define the type for siteData
interface SiteData {
    platform_domain: string | null;
    platform_subdomain: string | null;
    id: string | null;
    name: string;
    address: string | null;
    city: string | null;
    state: string | null;
    zip: string | null;
    phone: string | null;
    fax: string | null;
    logo: string | null;
    time_zone: string | null;
    is_bulk_notifications_enabled: string | null;
    user: User | null;
}

export const useSiteDataStore = defineStore("siteData", {
    state: () => ({
        siteData: {
            platform_domain: null,
            platform_subdomain: null,
            id: null,
            name: '',
            address: null,
            city: null,
            state: null,
            zip: null,
            phone: null,
            fax: null,
            logo: null,
            is_bulk_notifications_enabled: null,
            time_zone: null,
            user: null,
        } as SiteData,
        appFullWidth: false,
    }),

    actions: {
        setUser(user: User) {
            this.siteData.user = user;
        },

        isMerchantSite(): boolean {
            return Boolean(this.siteData.id);
        },

        async fetchSiteData(forceRefresh = false): Promise<void> {
            // Skip if data is already loaded and no force refresh
            if (!forceRefresh && this.siteData.id) return;

            try {
                const response = await get("/site-data");

                // Shallow merge: Preserve existing reactive references
                this.siteData = {
                    ...this.siteData,
                    ...response.data
                };
            } catch (err) {
                console.error("Error fetching site data:", err);
            }
        },
        async setFullWidthClass() {
            this.appFullWidth = true;
        },
        async unsetFullWidthClass() {
            this.appFullWidth = false;
        },
    },
});