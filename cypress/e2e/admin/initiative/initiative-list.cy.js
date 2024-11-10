describe('Painel de Controle - Página de listar Iniciativas', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024');
        cy.visit('/painel/iniciativas');
    });

    it('Garante que a página de Iniciativas existe', () => {
        cy.get('h2').contains('Minhas Iniciativas').should('be.visible');
    });

    it('Garante que as iniciativas estejam visíveis ', () => {
        cy.get(':nth-child(7) > :nth-child(1) > a').contains('AxeZumbi').should('be.visible');
        cy.get(':nth-child(7) > :nth-child(2)').contains('17/07/2024 15:12:00').should('be.visible');
    });

    it('Garante que seja possível excluir uma iniciativa', () => {
        cy.get('[data-cy=remove-7]').contains('Excluir').click();
        cy.get('#modalRemoveConfirm [data-modal-button=confirm-link]')
            .should('be.visible')
            .click();

        cy.get('[data-cy=table-initiative-list] tr > :nth-child(1) > a')
            .contains('AxeZumbi')
            .should('not.exist');
    });
})