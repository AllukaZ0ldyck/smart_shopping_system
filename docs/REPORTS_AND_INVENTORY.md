# Sales and inventory reports

The admin UI (web app) already includes **sales** and **inventory (stock)** reporting. Access requires an authenticated user with the right **permissions** (e.g. `manage_reports`, `manage_sale`, `manage_pos_screen` depending on the feature).

## Where to find reports in the UI

After login, open the sidebar **Reports** section. Typical entries include:

- **Warehouse / stock overview** — stock levels by warehouse context.
- **Sale report** — sales over a date range.
- **Stock report** — current stock listing (pagination, search, warehouse filter).
- **Product quantity report** — quantity-focused inventory view.
- **Today’s sales** — same-day summary (API: `today-sales-overall-report`).
- **Top selling products**, **profit & loss**, **supplier / customer** reports — as enabled for your role.

Exact menu labels depend on enabled permissions and language pack.

## Main API endpoints (authenticated)

Base path: `/api/` with `Authorization: Bearer <token>` (or session, per your auth mode).

**Inventory / stock**

| Method | Route | Controller | Notes |
|--------|--------|------------|--------|
| GET | `stock-report` | `ManageStockAPIController@stockReport` | Paginated stock; `warehouse_id`, `search` |
| GET | `stock-report-excel` | `ReportAPIController@stockReportExcel` | Excel export |
| GET | `get-product-sale-report-excel` | `ReportAPIController@getProductSaleReportExport` | Product × sales export |
| GET | `get-purchase-product-report-excel` | `ReportAPIController@getPurchaseProductReportExport` | Purchase product export |

**Sales**

| Method | Route | Notes |
|--------|--------|--------|
| GET | `warehouse-report` | Warehouse-level report data |
| GET | `sales-report-excel` | Excel |
| GET | `total-sale-report-excel` | Totals export |
| GET | `today-sales-overall-report` | Today aggregate |
| GET | `get-sale-product-report` | Sale line / product report |
| GET | `top-selling-product-report` | JSON |
| GET | `top-selling-product-report-excel` | Excel |

Permissions are enforced in `routes/api.php` (middleware groups such as `permission:manage_reports`).

## Dashboard stock alerts

The **dashboard** can surface **low-stock / alert** style information (widgets use stock alert fields). Low stock at the row level is also driven by `manage_stocks` and product `stock_alert` in the domain model.

## POS notifications

On the **POS product grid**, when **Show out of stock products** is enabled in settings:

- **Out of stock** items show an **Out of stock** badge and an **error** toast if tapped.
- **Low stock** (quantity at or below **stock alert**) shows a **warning** toast when the item is **first added** to the cart.

## Export workflow

1. Log in with a user that has report permissions.
2. Open the desired report screen.
3. Use **Excel** / export buttons where available, or call the corresponding `*-excel` API with the same query parameters as the UI.

For automation, mirror the query string the UI sends (dates, `warehouse_id`, filters) against the documented routes above.
