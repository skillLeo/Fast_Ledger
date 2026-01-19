<div class="employee-tabs mb-4">
    <div class="tab-item active" data-tab="personal">Personal</div>
    <div class="tab-item" data-tab="employment">Employment Info</div>
    <div class="tab-item" data-tab="nic">NIC</div>
    <div class="tab-item" data-tab="hmrc">HMRC Starter Declaration</div>
    <div class="tab-item" data-tab="contacts">Contacts & History</div>
    <div class="tab-item" data-tab="terms">Terms</div>
    <div class="tab-item" data-tab="payment">Payment & Banking</div>
</div>

<style>
.employee-tabs {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0;
    flex-wrap: wrap;
}

.employee-tabs .tab-item {
    padding: 10px 20px;
    cursor: pointer;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-bottom: none;
    border-radius: 6px 6px 0 0;
    transition: all 0.3s;
    font-weight: 500;
    color: #6c757d;
}

.employee-tabs .tab-item:hover {
    background: #e9ecef;
}

.employee-tabs .tab-item.active {
    background: white;
    color: #13667d;
    border-color: #13667d;
    border-bottom: 2px solid white;
    margin-bottom: -2px;
}

.tab-content-wrapper {
    margin-top: 20px;
}

.tab-content-pane {
    display: none;
}

.tab-content-pane.active {
    display: block;
}
</style>