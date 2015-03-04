Feature: Authentication
  In order to use the application
  As an anonymous user
  I need to be able to be authenticated as a registered user

  Scenario: Authentication
    Given I am on the homepage
    When I fill in "login_email" with "saunois.martial@gmail.com"
    And I fill in "login_password" with "For2@cke"
    And I press "connect_btn"
    Then I should be logged in
