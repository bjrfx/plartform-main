import {defineStore} from 'pinia';
import router from "@/router";

export interface Bill {
    department_id: string;
    department_name: string;
    department_icon: string;
    sub_department_id?: string;
    sub_department_name?: string | null;
    account_reference: string;
    amount: number | string;
    form_payload: Record<string, any>;
}

export interface Fee {
    department_id: string;
    amount: number | string;
}

export interface Transaction {
    department_id: string;
    success: string;
    reference_number: string;
    status_code: string;
    status_message: string;
    batch_id: string;
    expiry: string;
    extra_data: string;
}

export const useShopStore = defineStore('shop', {
    state: () => ({
        cart: [] as Bill[],
        fees: [] as Fee[],
        transactions: [] as Transaction[],
        error: null as string | null,
        triggerItemAdded: false as boolean,
        triggerItemExists: false as boolean,
    }),
    actions: {
        addToCart(bill: Bill): void {
            if (this.cart.some(item => item.account_reference === bill.account_reference && item.department_id === bill.department_id)) {
                this.triggerExists();
                return;
            }

            bill.form_payload = this.filterFormNonEmptyValues(bill.form_payload);

            this.cart.push({...bill});
            this.updateCart();

            this.openOverviewPage().then((): void => {
                this.triggerAlert();
            });
        },
        async openOverviewPage(): Promise<void> {
            await router.push({
                name: 'DepartmentsAndCart',
                params: {},
            });
        },
        removeFromCart(bill: Bill): void {
            this.cart = this.cart.filter(
                (item) => item.account_reference !== bill.account_reference && item.department_id !== bill.department_id
            );
            this.updateCart();
        },
        updateCart(): void {
            localStorage.setItem('cart', JSON.stringify(this.cart));
        },
        loadCart(): void {
            const savedCart = localStorage.getItem('cart');
            if (savedCart) {
                this.cart = JSON.parse(savedCart);
            }
        },
        triggerAlert(): void {
            this.triggerItemAdded = true;
            setTimeout(() => {
                this.triggerItemAdded = false;
            }, 5000);
        },
        triggerExists(): void {
            this.triggerItemExists = true;
            setTimeout(() => {
                this.triggerItemExists = false;
            }, 5000);
        },
        setFees(fees: Fee[]): void {
            this.fees = fees;
        },
        getFee(departmentId: string): number | string | null {
            // Use `find` to get the object with the matching department_id
            const department = this.fees.find(item => item.department_id === departmentId);

            if (department) {
                // Return the fee or 0 if not found
                return Number(department.amount) > 0 ? department.amount : 0;
            }
            return null;
        },
        getFeesTotal(): number {
            return this.fees
                .reduce((sum, item) => {
                    const amount = Number(item.amount);
                    return sum + (isNaN(amount) ? 0 : amount); // Add only valid numbers
                }, 0);
        },
        getCartTotal(): number {
            return this.cart
                .reduce((sum, item) => {
                    const amount = Number(item.amount);
                    return sum + (isNaN(amount) ? 0 : amount); // Add only valid numbers
                }, 0);
        },
        getCartSubTotal(departmentId: string): number {
            return this.cart
                .filter((item) => item.department_id === departmentId)
                .reduce((sum, item) => {
                    const amount = Number(item.amount);
                    return sum + (isNaN(amount) ? 0 : amount); // Add only valid numbers
                }, 0);
        },
        setTransactions(transactions: Transaction[]): void {
            this.transactions = transactions;
            localStorage.setItem('transactions', JSON.stringify(this.transactions));
        },
        getTransaction(departmentId: string): Transaction | null {
            if (this.transactions.length === 0) {
                const savedTransactions: string | null = localStorage.getItem('transactions');
                if (savedTransactions) {
                    this.transactions = JSON.parse(savedTransactions);
                }
            }
            // Use `find` to get the object with the matching department_id
            const department = this.transactions.find(item => item.department_id === departmentId);

            if (department) {
                // Return the fee or 0 if not found
                return department;
            }
            return null;
        },
        filterFormNonEmptyValues: (formPayload: Record<string, any>): Record<string, any> => {
            //Collect only entries with value to lower the localStorage size
            return Object.fromEntries(
                Object.entries(formPayload).filter(([_, value]) => {
                    return (
                        value !== null &&
                        value !== undefined &&
                        String(value).trim().length > 0
                    );
                })
            );
        },
    },
});