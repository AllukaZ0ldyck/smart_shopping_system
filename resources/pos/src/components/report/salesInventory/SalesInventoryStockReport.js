import React, { useEffect, useState, useCallback } from "react";
import { connect } from "react-redux";
import { useIntl } from "react-intl";
import {
    Alert,
    Button,
    Card,
    Col,
    Form,
    Row,
    Tab,
    Tabs,
    Table,
} from "react-bootstrap-v5";
import MasterLayout from "../../MasterLayout";
import TabTitle from "../../../shared/tab-title/TabTitle";
import {
    currencySymbolHandling,
    getFormattedMessage,
    placeholderText,
} from "../../../shared/sharedMethod";
import ReactSelect from "../../../shared/select/reactSelect";
import { fetchAllWarehouses } from "../../../store/action/warehouseAction";
import apiConfig from "../../../config/apiConfig";
import { apiBaseURL } from "../../../constants";
import TopProgressBar from "../../../shared/components/loaders/TopProgressBar";

const buildQuery = (params) => {
    const q = new URLSearchParams();
    Object.entries(params).forEach(([k, v]) => {
        if (v !== null && v !== undefined && v !== "") {
            q.set(k, String(v));
        }
    });
    return q.toString();
};

const SalesInventoryStockReport = (props) => {
    const intl = useIntl();
    const { warehouses, fetchAllWarehouses, frontSetting, allConfigData } =
        props;

    const t = (id, defaultMessage) =>
        intl.formatMessage({ id, defaultMessage });

    const [activeTab, setActiveTab] = useState("report");

    const [period, setPeriod] = useState("daily");
    const [anchorDate, setAnchorDate] = useState(
        new Date().toISOString().slice(0, 10)
    );
    const [warehouseValue, setWarehouseValue] = useState({
        label: intl.formatMessage({
            id: "report-all.warehouse.label",
            defaultMessage: "All",
        }),
        value: "all",
    });

    const [reportPayload, setReportPayload] = useState(null);
    const [reportLoading, setReportLoading] = useState(false);

    const [lowStockRows, setLowStockRows] = useState([]);
    const [lowStockLoading, setLowStockLoading] = useState(false);

    const [searchText, setSearchText] = useState("");
    const [searchRows, setSearchRows] = useState([]);
    const [searchLoading, setSearchLoading] = useState(false);
    const [searchAttempted, setSearchAttempted] = useState(false);

    const currencySymbol =
        frontSetting?.value?.currency_symbol || "";

    const warehouseArray = warehouses || [];
    const warehouseSelectData = [
        {
            attributes: {
                name: t("report-all.warehouse.label", "All"),
            },
            id: "all",
        },
    ].concat(warehouseArray);

    useEffect(() => {
        fetchAllWarehouses();
    }, [fetchAllWarehouses]);

    const warehouseIdParam =
        warehouseValue.value === "all" || warehouseValue.value == null
            ? null
            : warehouseValue.value;

    const loadReport = useCallback(async () => {
        setReportLoading(true);
        try {
            const qs = buildQuery({
                period,
                anchor_date: anchorDate,
                warehouse_id: warehouseIdParam,
            });
            const res = await apiConfig.get(
                `${apiBaseURL.SALES_INVENTORY_REPORT}?${qs}`
            );
            setReportPayload(res.data.data);
        } catch {
            setReportPayload(null);
        } finally {
            setReportLoading(false);
        }
    }, [period, anchorDate, warehouseIdParam]);

    useEffect(() => {
        if (activeTab === "report") {
            loadReport();
        }
    }, [activeTab, loadReport]);

    const downloadExcel = async () => {
        const qs = buildQuery({
            period,
            anchor_date: anchorDate,
            warehouse_id: warehouseIdParam,
        });
        const res = await apiConfig.get(
            `${apiBaseURL.SALES_INVENTORY_REPORT_EXCEL}?${qs}`
        );
        const url = res.data?.data?.sales_inventory_excel_url;
        if (url) {
            window.open(url, "_blank");
        }
    };

    const downloadPdf = async () => {
        const qs = buildQuery({
            period,
            anchor_date: anchorDate,
            warehouse_id: warehouseIdParam,
        });
        const res = await apiConfig.get(
            `${apiBaseURL.SALES_INVENTORY_REPORT_PDF}?${qs}`
        );
        const url = res.data?.data?.sales_inventory_pdf_url;
        if (url) {
            window.open(url, "_blank");
        }
    };

    const loadLowStock = useCallback(async () => {
        setLowStockLoading(true);
        try {
            const base = apiBaseURL.PRODUCT_STOCK_REPORT;
            const path =
                warehouseIdParam == null
                    ? `${base}?pageSize=100`
                    : `${base}/${warehouseIdParam}?pageSize=100`;
            const res = await apiConfig.get(path);
            const rows = res.data?.[0]?.data ?? res.data?.data ?? [];
            setLowStockRows(Array.isArray(rows) ? rows : []);
        } catch {
            setLowStockRows([]);
        } finally {
            setLowStockLoading(false);
        }
    }, [warehouseIdParam]);

    useEffect(() => {
        if (activeTab === "stock") {
            loadLowStock();
        }
    }, [activeTab, loadLowStock]);

    const runProductSearch = async () => {
        if (!warehouseIdParam || warehouseIdParam === "all") {
            setSearchRows([]);
            return;
        }
        setSearchAttempted(true);
        setSearchLoading(true);
        try {
            const qs = buildQuery({
                warehouse_id: warehouseIdParam,
                search: searchText.trim(),
                pageSize: 50,
                page: 1,
            });
            const res = await apiConfig.get(
                `${apiBaseURL.STOCK_REPORT}?${qs}`
            );
            const rows = res.data?.data ?? [];
            setSearchRows(Array.isArray(rows) ? rows : []);
        } catch {
            setSearchRows([]);
        } finally {
            setSearchLoading(false);
        }
    };

    const salesLines = reportPayload?.sales_lines || [];
    const inventoryRows = reportPayload?.inventory || [];

    return (
        <MasterLayout>
            <TopProgressBar />
            <TabTitle
                title={placeholderText("report.sales-inventory-stock.title")}
            />
            <Tabs
                activeKey={activeTab}
                onSelect={(k) => setActiveTab(k || "report")}
                className="mb-4"
            >
                <Tab
                    eventKey="report"
                    title={t(
                        "report.sales-inventory.tab.report",
                        "Sales & inventory"
                    )}
                >
                    <Card className="mb-4">
                        <Card.Body>
                            <Row className="g-3 align-items-end">
                                <Col md={3}>
                                    <Form.Label>
                                        {getFormattedMessage(
                                            "report.sales-inventory.period.label"
                                        )}
                                    </Form.Label>
                                    <Form.Select
                                        value={period}
                                        onChange={(e) =>
                                            setPeriod(e.target.value)
                                        }
                                    >
                                        <option value="daily">
                                            {t(
                                                "report.sales-inventory.period.daily",
                                                "Daily"
                                            )}
                                        </option>
                                        <option value="weekly">
                                            {t(
                                                "report.sales-inventory.period.weekly",
                                                "Weekly"
                                            )}
                                        </option>
                                        <option value="monthly">
                                            {t(
                                                "report.sales-inventory.period.monthly",
                                                "Monthly"
                                            )}
                                        </option>
                                    </Form.Select>
                                </Col>
                                <Col md={3}>
                                    <Form.Label>
                                        {getFormattedMessage(
                                            "report.sales-inventory.anchor.label"
                                        )}
                                    </Form.Label>
                                    <Form.Control
                                        type="date"
                                        value={anchorDate}
                                        onChange={(e) =>
                                            setAnchorDate(e.target.value)
                                        }
                                    />
                                </Col>
                                <Col md={4}>
                                    <Form.Label>
                                        {getFormattedMessage("warehouse.title")}
                                    </Form.Label>
                                    <ReactSelect
                                        data={warehouseSelectData}
                                        onChange={(obj) => setWarehouseValue(obj)}
                                        title={getFormattedMessage(
                                            "warehouse.title"
                                        )}
                                        errors=""
                                        value={warehouseValue}
                                        placeholder={placeholderText(
                                            "purchase.select.warehouse.placeholder.label"
                                        )}
                                    />
                                </Col>
                                <Col md={2} className="d-flex gap-2 flex-wrap">
                                    <Button
                                        variant="primary"
                                        type="button"
                                        disabled={reportLoading}
                                        onClick={loadReport}
                                    >
                                        {getFormattedMessage(
                                            "report.sales-inventory.refresh"
                                        )}
                                    </Button>
                                </Col>
                            </Row>
                            <div className="d-flex flex-wrap gap-2 mt-3">
                                <Button
                                    variant="outline-success"
                                    type="button"
                                    onClick={downloadExcel}
                                >
                                    {getFormattedMessage(
                                        "report.sales-inventory.download-excel"
                                    )}
                                </Button>
                                <Button
                                    variant="outline-danger"
                                    type="button"
                                    onClick={downloadPdf}
                                >
                                    {getFormattedMessage(
                                        "report.sales-inventory.download-pdf"
                                    )}
                                </Button>
                            </div>
                            {reportPayload && (
                                <p className="text-muted small mt-2 mb-0">
                                    {reportPayload.start_date} —{" "}
                                    {reportPayload.end_date}
                                </p>
                            )}
                        </Card.Body>
                    </Card>

                    <h5 className="mb-2">
                        {getFormattedMessage(
                            "report.sales-inventory.sales-section"
                        )}
                    </h5>
                    <Table responsive bordered hover size="sm" className="mb-4">
                        <thead>
                            <tr>
                                <th>
                                    {getFormattedMessage(
                                        "globally.react-table.column.created-date.label"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage("customer.title")}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "globally.detail.product"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage("pos-qty.title")}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "report.sales-inventory.unit-price"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "report.sales-inventory.revenue"
                                    )}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {reportLoading && (
                                <tr>
                                    <td colSpan={6} className="text-center">
                                        …
                                    </td>
                                </tr>
                            )}
                            {!reportLoading &&
                                salesLines.map((row, idx) => (
                                    <tr key={idx}>
                                        <td>{row.date}</td>
                                        <td>{row.customer_name}</td>
                                        <td>{row.product_name}</td>
                                        <td>{row.quantity}</td>
                                        <td>
                                            {currencySymbolHandling(
                                                allConfigData,
                                                currencySymbol,
                                                row.unit_price
                                            )}
                                        </td>
                                        <td>
                                            {currencySymbolHandling(
                                                allConfigData,
                                                currencySymbol,
                                                row.total_revenue
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            {!reportLoading && salesLines.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="text-center">
                                        {getFormattedMessage(
                                            "sale.product.table.no-data.label"
                                        )}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </Table>

                    <h5 className="mb-2">
                        {getFormattedMessage(
                            "report.sales-inventory.inventory-section"
                        )}
                    </h5>
                    <Table responsive bordered hover size="sm">
                        <thead>
                            <tr>
                                <th>
                                    {getFormattedMessage(
                                        "globally.react-table.column.code.label"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "globally.detail.product"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage("warehouse.title")}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "report.sales-inventory.current-stock"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "report.sales-inventory.unit-cost"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "report.sales-inventory.total-value"
                                    )}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {reportLoading && (
                                <tr>
                                    <td colSpan={6} className="text-center">
                                        …
                                    </td>
                                </tr>
                            )}
                            {!reportLoading &&
                                inventoryRows.map((row, idx) => (
                                    <tr key={idx}>
                                        <td>{row.code}</td>
                                        <td>{row.product}</td>
                                        <td>{row.warehouse}</td>
                                        <td>{row.current_stock}</td>
                                        <td>
                                            {currencySymbolHandling(
                                                allConfigData,
                                                currencySymbol,
                                                row.unit_cost
                                            )}
                                        </td>
                                        <td>
                                            {currencySymbolHandling(
                                                allConfigData,
                                                currencySymbol,
                                                row.total_value
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            {!reportLoading && inventoryRows.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="text-center">
                                        {getFormattedMessage(
                                            "sale.product.table.no-data.label"
                                        )}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </Table>
                </Tab>
                <Tab
                    eventKey="stock"
                    title={t(
                        "report.sales-inventory.tab.stock",
                        "Low stock & search"
                    )}
                >
                    <Card className="mb-3">
                        <Card.Body>
                            <Row className="g-3 align-items-end">
                                <Col md={6}>
                                    <Form.Label>
                                        {getFormattedMessage("warehouse.title")}
                                    </Form.Label>
                                    <ReactSelect
                                        data={warehouseSelectData}
                                        onChange={(obj) => {
                                            setWarehouseValue(obj);
                                            setSearchRows([]);
                                        }}
                                        title={getFormattedMessage(
                                            "warehouse.title"
                                        )}
                                        errors=""
                                        value={warehouseValue}
                                        placeholder={placeholderText(
                                            "purchase.select.warehouse.placeholder.label"
                                        )}
                                    />
                                </Col>
                                <Col md={6}>
                                    <Button
                                        variant="primary"
                                        type="button"
                                        onClick={loadLowStock}
                                        disabled={lowStockLoading}
                                    >
                                        {getFormattedMessage(
                                            "report.sales-inventory.refresh-low-stock"
                                        )}
                                    </Button>
                                </Col>
                            </Row>
                        </Card.Body>
                    </Card>

                    <h5 className="mb-2">
                        {getFormattedMessage(
                            "report.sales-inventory.low-stock-title"
                        )}
                    </h5>
                    <Table responsive bordered hover size="sm" className="mb-4">
                        <thead>
                            <tr>
                                <th>
                                    {getFormattedMessage(
                                        "dashboard.stockAlert.code.label"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "dashboard.stockAlert.product.label"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "dashboard.stockAlert.warehouse.label"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "dashboard.stockAlert.quantity.label"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "dashboard.stockAlert.alertQuantity.label"
                                    )}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {lowStockLoading && (
                                <tr>
                                    <td colSpan={5} className="text-center">
                                        …
                                    </td>
                                </tr>
                            )}
                            {!lowStockLoading &&
                                lowStockRows.map((p, idx) => (
                                    <tr key={idx}>
                                        <td>{p.code}</td>
                                        <td>{p.name}</td>
                                        <td>{p.stock?.warehouse?.name}</td>
                                        <td>
                                            {p.stock?.quantity}{" "}
                                            <span className="text-muted small">
                                                {p.stock?.product_unit_name}
                                            </span>
                                        </td>
                                        <td>{p.stock_alert ?? 0}</td>
                                    </tr>
                                ))}
                            {!lowStockLoading && lowStockRows.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="text-center">
                                        {getFormattedMessage(
                                            "report.sales-inventory.no-low-stock"
                                        )}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </Table>

                    <h5 className="mb-2">
                        {getFormattedMessage(
                            "report.sales-inventory.search-title"
                        )}
                    </h5>
                    <Row className="g-2 mb-3">
                        <Col md={8}>
                            <Form.Control
                                type="search"
                                placeholder={placeholderText(
                                    "report.sales-inventory.search-placeholder"
                                )}
                                value={searchText}
                                onChange={(e) => setSearchText(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === "Enter") {
                                        e.preventDefault();
                                        runProductSearch();
                                    }
                                }}
                            />
                        </Col>
                        <Col md={4}>
                            <Button
                                variant="secondary"
                                className="w-100"
                                type="button"
                                disabled={searchLoading || !warehouseIdParam}
                                onClick={runProductSearch}
                            >
                                {getFormattedMessage(
                                    "report.sales-inventory.search-btn"
                                )}
                            </Button>
                        </Col>
                    </Row>
                    {!warehouseIdParam ? (
                        <Alert variant="info">
                            {getFormattedMessage(
                                "report.sales-inventory.search-need-warehouse"
                            )}
                        </Alert>
                    ) : null}
                    <Table responsive bordered hover size="sm">
                        <thead>
                            <tr>
                                <th>
                                    {getFormattedMessage(
                                        "globally.react-table.column.code.label"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "globally.detail.product"
                                    )}
                                </th>
                                <th>
                                    {getFormattedMessage(
                                        "report.sales-inventory.current-stock"
                                    )}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {searchLoading && (
                                <tr>
                                    <td colSpan={3} className="text-center">
                                        …
                                    </td>
                                </tr>
                            )}
                            {!searchLoading &&
                                searchRows.map((row) => {
                                    const a = row.attributes || row;
                                    const prod = a.product || {};
                                    return (
                                        <tr key={row.id || prod.code}>
                                            <td>{prod.code}</td>
                                            <td>{prod.name}</td>
                                            <td>{a.quantity}</td>
                                        </tr>
                                    );
                                })}
                            {!searchLoading &&
                                searchAttempted &&
                                searchRows.length === 0 && (
                                <tr>
                                    <td colSpan={3} className="text-center">
                                        {getFormattedMessage(
                                            "report.sales-inventory.search-empty"
                                        )}
                                    </td>
                                </tr>
                            )}
                            {!searchLoading && !searchAttempted && (
                                <tr>
                                    <td
                                        colSpan={3}
                                        className="text-center text-muted"
                                    >
                                        {getFormattedMessage(
                                            "report.sales-inventory.search-hint"
                                        )}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </Table>
                </Tab>
            </Tabs>
        </MasterLayout>
    );
};

const mapStateToProps = (state) => ({
    warehouses: state.warehouses,
    frontSetting: state.frontSetting,
    allConfigData: state.allConfigData,
});

export default connect(mapStateToProps, { fetchAllWarehouses })(
    SalesInventoryStockReport
);
