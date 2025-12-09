describe("Mails", () => {
  it("Test Mailpit API works", () => {
    cy.mailpitGetAllMails().then((result) => {
      console.log("Results: ", result);
      expect(result).to.have.property("messages");
      expect(result.messages).to.have.length(0);
      expect(result.messages).to.be.an("array");
      expect(result).to.have.property("tags");
      expect(result).to.have.property("messages_count", 0);
      expect(result).to.have.property("start");
      expect(result).to.have.property("total", 0);
      expect(result).to.have.property("count", 0);
      expect(result).to.have.property("unread");
    });
  });
});
