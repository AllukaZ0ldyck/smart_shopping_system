import React, { useEffect, useRef, useState } from "react";
import { Card, Badge } from "react-bootstrap-v5";
import { connect, useDispatch } from "react-redux";
import { useIntl } from "react-intl";
import { posFetchProduct } from "../../../store/action/pos/posfetchProductAction";
import { posAllProduct } from "../../../store/action/pos/posAllProductAction";
import productImage from "../../../assets/images/brand_logo.png";
import { addToast } from "../../../store/action/toastAction";
import {
    currencySymbolHandling,
    getFormattedMessage,
} from "../../../shared/sharedMethod";
import { toastType } from "../../../constants";
import Skelten from "../../../shared/components/loaders/Skelten";

const getAvailableQuantity = (product) => {
    const q = product?.attributes?.stock?.quantity;
    if (q === undefined || q === null) {
        return 0;
    }
    const n = parseFloat(q);
    return Number.isFinite(n) ? n : 0;
};

const getStockAlertLevel = (product) => {
    const a = product?.attributes?.stock_alert;
    if (a === undefined || a === null || a === "null" || a === "") {
        return 0;
    }
    const n = parseFloat(a);
    return Number.isFinite(n) ? n : 0;
};

const Product = (props) => {
    const {
        posAllProducts,
        cartProducts,
        updateCart,
        customCart,
        cartProductIds,
        setCartProductIds,
        settings,
        productMsg,
        newCost,
        selectedOption,
        allConfigData,
        isLoading,
    } = props;
    const [updateProducts, setUpdateProducts] = useState([]);
    const clickAudioRef = useRef(null);
    const dispatch = useDispatch();
    const intl = useIntl();

    useEffect(() => {
        // update cart while cart is updated
        cartProducts && setUpdateProducts(cartProducts);
        const ids = updateProducts.map((item) => {
            return item.id;
        });
        setCartProductIds(ids);
    }, [updateProducts, cartProducts]);

    const addToCart = (product) => {
        const available = getAvailableQuantity(product);
        if (available > 0) {
            if (settings?.attributes?.enable_pos_click_audio === 'true' && clickAudioRef.current) {
                clickAudioRef.current.play().catch((e) => {
                    console.warn("Audio play failed:", e);
                });
            }
            addProductToCart(product);
        } else {
            dispatch(
                addToast({
                    text: intl.formatMessage({
                        id: "pos.this.product.out.of.stock.message",
                        defaultMessage: "This product is out of stock",
                    }),
                    type: toastType.ERROR,
                })
            );
        }
    };

    const addProductToCart = (product) => {
        const newId = posAllProducts
            .filter((item) => item.id === product.id)
            .map((item) => item.id);
        const finalIdArrays = customCart.map((id) => id.product_id);
        const finalId = finalIdArrays.filter(
            (finalIdArray) => finalIdArray === newId[0]
        );
        const pushArray = [...customCart];
        const newProduct = pushArray.find(
            (element) => element.id === finalId[0]
        );
        const filterQty = updateProducts
            .filter((item) => item.id === product.id)
            .map((qty) => qty.quantity)[0];
        const stockQty = getAvailableQuantity(product);
        if (
            updateProducts.filter((item) => item.id === product.id).length > 0
        ) {
            if (filterQty >= stockQty) {
                dispatch(
                    addToast({
                        text: intl.formatMessage({
                            id: "pos.quantity.exceeds.quantity.available.in.stock.message",
                            defaultMessage:
                                "Quantity exceeds quantity available in stock",
                        }),
                        type: toastType.ERROR,
                    })
                );
            } else if (product.attributes.quantity_limit && filterQty >= product.attributes.quantity_limit) {
                dispatch(
                    addToast({
                        text: getFormattedMessage(
                            "sale.product-qty.limit.validate.message"
                        ),
                        type: toastType.ERROR,
                    })
                );
            } else {
                setUpdateProducts((updateProducts) =>
                    updateProducts.map((item) =>
                        item.id === product.id
                            ? {
                                  ...item,
                                  quantity:
                                      stockQty > item.quantity
                                          ? item.quantity++ + 1
                                          : null,
                              }
                            : { ...item, id: item.id }
                    )
                );
                updateCart(updateProducts, {...product,warehouse_id: selectedOption.value, image: product.attributes.images.imageUrls ? product.attributes.images.imageUrls[0] : productImage});
            }
        } else {
            setUpdateProducts((prevSelected) => [...prevSelected, {...product,warehouse_id: selectedOption.value}]);
            updateCart((prevSelected) => [...prevSelected, {...newProduct,warehouse_id: selectedOption.value, image: product.attributes.images.imageUrls ? product.attributes.images.imageUrls[0] : productImage}]);
            const alertLevel = getStockAlertLevel(product);
            if (alertLevel > 0 && stockQty > 0 && stockQty <= alertLevel) {
                dispatch(
                    addToast({
                        text: intl.formatMessage({
                            id: "pos.product.low.stock.notification",
                            defaultMessage:
                                "This item is below its stock alert level. Restock soon.",
                        }),
                        type: toastType.WARNING,
                    })
                );
            }
        }
    };

    const isProductExistInCart = (productId) => {
        return cartProductIds.includes(productId);
    };

    const posFilterProduct = posAllProducts &&
        settings?.attributes?.show_pos_stock_product === 'true'
        ? posAllProducts :
        posAllProducts.filter((product) => getAvailableQuantity(product) > 0);
    //Cart Item Array
    const loadAllProduct = (product, index) => {

        const availableQty = getAvailableQuantity(product);
        const showOutOfStock =
            settings?.attributes?.show_pos_stock_product === "true" &&
            availableQty <= 0;

        return (
            <div
                className="product-custom-card"
                key={index}
                onClick={() => addToCart(product)}
            >
                <Card
                    className={`position-relative h-100 ${
                        isProductExistInCart(product.id) ? "product-active" : ""
                    } ${showOutOfStock ? "opacity-75" : ""}`}
                >
                    <Card.Img
                        variant="top"
                        src={
                            product.attributes.images.imageUrls
                                ? product.attributes.images.imageUrls[0]
                                : productImage
                        }
                    />
                    <Card.Body className="px-2 pt-2 pb-1 custom-card-body d-flex flex-column justify-content-evenly">
                        <h6 className="product-title mb-0 text-gray-900">
                            {product.attributes?.name}
                            {product.attributes?.code !==
                            product.attributes?.product_code
                                ? ` (${product.attributes?.code}, ${product.attributes?.product_code})`
                                : null}
                        </h6>
                        <div className="d-flex justify-content-between"><span className="fs-small text-gray-700">
                            {product.attributes.code}
                        </span>
                        {product.attributes?.variation_product ? <span className="badge bg-light-info fs-small text-gray-700">
                           {product.attributes?.variation_product?.variation_type_name}
                        </span> : ''}</div>
                        <p className="m-0 item-badges">
                            {showOutOfStock ? (
                                <Badge
                                    bg="danger"
                                    text="white"
                                    className="product-custom-card__card-badge"
                                >
                                    {intl.formatMessage({
                                        id: "pos.out.of.stock.badge.label",
                                        defaultMessage: "Out of stock",
                                    })}
                                </Badge>
                            ) : (
                                <Badge
                                    bg="info"
                                    text="white"
                                    className="product-custom-card__card-badge"
                                >
                                    {availableQty}{" "}
                                    {product?.attributes?.sale_unit_name?.short_name}
                                </Badge>
                            )}
                        </p>
                        <p className="m-0 item-badge">
                            <Badge
                                bg="primary"
                                text="white"
                                className="product-custom-card__card-badge"
                            >
                                {currencySymbolHandling(
                                    allConfigData,
                                    settings.attributes &&
                                        settings.attributes.currency_symbol,
                                    newCost
                                        ? newCost
                                        : product.attributes.product_price
                                )}
                            </Badge>
                        </p>
                    </Card.Body>
                </Card>
            </div>
        )
    };

    return (
        <div
            className={`${
                posFilterProduct && posFilterProduct.length === 0
                    ? "d-flex align-items-center justify-content-center"
                    : ""
            } product-list-block pt-1`}
        >
            <audio ref={clickAudioRef} src={settings?.attributes?.click_audio} preload="auto" />
            <div className="d-flex flex-wrap product-list-block__product-block w-100">
                {posFilterProduct && posFilterProduct.length === 0 ? (
                    isLoading ? (
                        <Skelten />
                    ) : (
                        <h4 className="m-auto">
                            {getFormattedMessage(
                                "pos-no-product-available.label"
                            )}
                        </h4>
                    )
                ) : (
                    ""
                )}
                {productMsg && productMsg === 1 ? (
                    <h4 className="m-auto">
                        {getFormattedMessage("pos-no-product-available.label")}
                    </h4>
                ) : (
                    posFilterProduct &&
                    posFilterProduct.map((product, index) => {
                        return loadAllProduct(product, index);
                    })
                )}
            </div>
        </div>
    );
};

const mapStateToProps = (state) => {
    const { posAllProducts, allConfigData, isLoading } = state;
    return { posAllProducts, allConfigData, isLoading };
};

export default connect(mapStateToProps, { posAllProduct, posFetchProduct })(
    Product
);
