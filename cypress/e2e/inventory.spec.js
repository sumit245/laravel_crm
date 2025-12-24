describe('Unified Inventory Datatable', () => {
    it('renders initial DOM rows and uses deferLoading, then triggers AJAX on pagination', () => {
        // Intercept the server-side endpoint
        cy.intercept('GET', '/__dev/inventory-data*').as('inventoryData');

        // Visit the dev test page
        cy.visit('/__dev/unified-inventory-test');

        // Check initial DOM rows are rendered
        cy.get('#unifiedInventoryTable tbody tr').should('have.length.at.least', 1);
        cy.contains('#unifiedInventoryTable', 'SERI1').should('exist');

        // Confirm DataTables init has deferLoading set
        cy.window().then((win) => {
            const dt = win.jQuery && win.jQuery('#unifiedInventoryTable').DataTable && win.jQuery('#unifiedInventoryTable').DataTable();
            expect(dt, 'DataTable instance present').to.exist;
            const defer = dt.settings && dt.settings()[0] && dt.settings()[0].oInit && dt.settings()[0].oInit.deferLoading;
            expect(defer, 'deferLoading set').to.equal(100);
        });

        // Ensure no initial AJAX happened (DataTables should not call the endpoint immediately)
        // Wait briefly to ensure any accidental call would be caught
        cy.wait(500);
        cy.get('@inventoryData.all').should('have.length', 0);

        // Click next page to trigger server-side AJAX
        cy.get('#unifiedInventoryTable_paginate .paginate_button.next').click();

        // Now the AJAX call should occur
        cy.wait('@inventoryData').its('response.statusCode').should('eq', 200);
    });
});
