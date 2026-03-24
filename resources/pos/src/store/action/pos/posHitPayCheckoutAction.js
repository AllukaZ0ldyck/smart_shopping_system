import {
    apiBaseURL,
    posCashPaymentActionType,
    toastType,
} from "../../../constants";
import apiConfig from "../../../config/apiConfig";
import { addToast } from "../toastAction";
import { fetchBrandClickable } from "./posAllProductAction";
import { getFormattedMessage } from "../../../shared/sharedMethod";
import { setLoading } from "../loadingAction";
import { fetchHoldLists } from "./HoldListAction";

export const posHitPayCheckoutAction =
    (
        salePayload,
        setUpdateProducts,
        handleClosePaymentModal,
        filterData,
        isLoading = true
    ) =>
    async (dispatch) => {
        if (isLoading) {
            dispatch(setLoading(true));
        }

        try {
            const saleResponse = await apiConfig.post(apiBaseURL.CASH_PAYMENT, salePayload);
            const sale = saleResponse.data.data;

            dispatch({
                type: posCashPaymentActionType.POS_CASH_PAYMENT,
                payload: sale,
            });

            const checkoutResponse = await apiConfig.post(
                `${apiBaseURL.CASH_PAYMENT}/${sale.id}/hitpay/checkout`
            );

            dispatch(
                addToast({
                    text: getFormattedMessage("sale.success.create.message"),
                })
            );

            setUpdateProducts([]);
            handleClosePaymentModal();
            dispatch(
                fetchBrandClickable(
                    filterData.brandId,
                    filterData.categoryId,
                    filterData.selectedOption.value
                )
            );
            dispatch(fetchHoldLists());

            window.location.href = checkoutResponse.data.data.checkout_url;
        } catch ({ response }) {
            dispatch(
                addToast({
                    text: response?.data?.message || "Unable to create HitPay checkout.",
                    type: toastType.ERROR,
                })
            );
        } finally {
            if (isLoading) {
                dispatch(setLoading(false));
            }
        }
    };
