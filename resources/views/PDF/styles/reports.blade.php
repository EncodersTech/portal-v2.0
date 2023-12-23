<style>
    html {
    }

    body {
        padding: 0.25rem;
    }

    .m-0 { margin: 0 !important; }
    .mb-0, .mx-0 { margin-bottom: 0 !important; }
    .mb-1, .mx-1 { margin-bottom: 0.25rem !important; }
    .mb-2, .mx-2 { margin-bottom: 0.5rem !important; }
    .mb-3, .mx-3 { margin-bottom: 1rem !important; }

    .mt-0, .mx-0 { margin-top: 0 !important; }
    .mt-1, .mx-1 { margin-top: 0.25rem !important; }
    .mt-2, .mx-2 { margin-top: 0.5rem !important; }
    .mt-3, .mx-3 { margin-top: 1rem !important; }

    .ml-0, .my-0 { margin-left: 0 !important; }
    .ml-1, .my-1 { margin-left: 0.25rem !important; }
    .ml-2, .my-2 { margin-left: 0.5rem !important; }
    .ml-3, .my-3 { margin-left: 1rem !important; }

    .mr-0, .my-0 { margin-right: 0 !important; }
    .mr-1, .my-1 { margin-right: 0.25rem !important; }
    .mr-2, .my-2 { margin-right: 0.5rem !important; }
    .mr-3, .my-3 { margin-right: 1rem !important; }

    .p-0 { padding: 0 !important; }
    .pb-0, .px-0 { padding-bottom: 0 !important; }
    .pb-1, .px-1 { padding-bottom: 0.25rem !important; }
    .pb-2, .px-2 { padding-bottom: 0.5rem !important; }
    .pb-3, .px-3 { padding-bottom: 1rem !important; }

    .pt-0, .px-0 { padding-top: 0 !important; }
    .pt-1, .px-1 { padding-top: 0.25rem !important; }
    .pt-2, .px-2 { padding-top: 0.5rem !important; }
    .pt-3, .px-3 { padding-top: 1rem !important; }

    .pl-0, .py-0 { padding-left: 0 !important; }
    .pl-1, .py-1 { padding-left: 0.25rem !important; }
    .pl-2, .py-2 { padding-left: 0.5rem !important; }
    .pl-3, .py-3 { padding-left: 1rem !important; }

    .pr-0, .py-0 { padding-right: 0 !important; }
    .pr-1, .py-1 { padding-right: 0.25rem !important; }
    .pr-2, .py-2 { padding-right: 0.5rem !important; }
    .pr-3, .py-3 { padding-right: 1rem !important; }

    .text-right { text-align: right; }

    .header {
        width: 100%;
        margin-bottom: 3rem;
    }

    .header-text {
        display: inline-block;
        width: 78%;
        vertical-align: top;
    }

    .logo-container {
        display: inline-block;
        width: 20%;
        text-align: right;
        vertical-align: top;
    }

    .logo { width: 100%; }

    .table-0 .col-1 { width: 186px; }

    .table-0 .col-2 { width: 186px; }

    .table-0 .col-3 { width: 67px; }

    .table-0 .col-23 { width: 248px; }

    .table-0 .col-4 { width: 230px; }

    .table-0 .col-5 { width: 124px; }

    .table-0 .col-6 { width: 173px; }

    .table-0 .col-7 { width: 62px; }

    .table-0 .col-8 { width: 100px; }

    .table-0 .col-9 { width: 185px; }

    .table-0 .col-10 { width: 300px; }

    .table-0 {
        border-collapse: collapse;
        width: 100%;
        color: #212529;
        border-bottom: 2px solid #dee2e6;
        width: 1240px;
        table-layout: fixed;
    }

    .table-0 > thead > tr > th {
        color: #F8F9FA;
        background-color: #212529;
        padding: 0.25rem;
        vertical-align: top;
        border-bottom: 2px solid #dee2e6;
        border-top: 2px solid #dee2e6;
        border-left: 1px solid #dee2e6;
    }

    .table-0 > tbody > tr > th:last-child {
        border-right: 1px solid #dee2e6;
    }

    .table-0 > tbody > tr > td {
        padding: 0.25rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
        border-left: 1px solid #dee2e6;
    }

    .table-0 > tbody > tr > td:last-child {
        border-right: 1px solid #dee2e6;
    }

    .table-1 {
        border-collapse: collapse;
        width: 100%;
        color: #212529;
        border-style: hidden;
    }

    .table-1 > tbody > tr > td {
        padding: 0.25rem;
        vertical-align: top;
        border-bottom: 1px solid #dee2e6;
        border-left: 1px solid #dee2e6;
    }

    .table-1 > tbody > tr > td:last-child {
        border-right: 1px solid #dee2e6;
    }

    .table-1 > tbody > tr > td.total {
        background-color: #FFC107;
        font-weight: bold;
        border-bottom: 1px solid #dee2e6;
    }

    .table-2 {
        border-collapse: collapse;
        width: 100%;
        color: #212529;
        border-style: hidden;
    }

    .table-2 > tbody > tr > td {
        padding: 0.25rem;
        vertical-align: top;
        border-bottom: 1px solid #dee2e6;
        border-left: 1px solid #dee2e6;
    }

    .table-2 > tbody > tr > td:last-child {
        border-right: 1px solid #dee2e6;
    }

    .text-danger {
        color: #DC3545;
    }

    .summary-header {
        margin-bottom: 1rem !important;
    }
    .summary-header .logo-container h6 {
        margin-top: 20px;
    }
    .summary-header .logo-container h4 {
        margin-top: 40px;
    }
    .table-summary {
        width: 100%;
        font-size: 12px;
    }
    .table-summary > thead > tr > th {
        font-size: 11px;
    }
    .table-summary .meet-summary-header-1 { padding-top: 14px; }
    .table-summary .col-sum-1 { width: 110px; }
    .table-summary .col-sum-2 { text-align: center; }
    .table-summary .col-sum-3 { text-align: right; }
    .meet-header-text{margin-top: 64px;text-align: center;margin-bottom: 20px;}
	.meet-subheader-text{text-align: center;}
	.extra-margin{margin-left:10px}
	.table-header{margin:15px;text-align:center}
	.full-width{width:100%}
    .float-child {
        width: 50%;
        float: left;
        padding: 20px;
    }
    .float-parent {
        width: 100%;
    }
</style>
