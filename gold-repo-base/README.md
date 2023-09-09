# UNICEF Platform

The Drupal 8 application repository for UNICEF.org's platform build.

This project is based on [Acquia BLT (Build and Launch Tool)](https://github.com/acquia/blt), an open-source project template and tool that enables building, testing, and deploying Drupal installations following Acquia's best practices. It uses [Drupal VM](https://www.drupalvm.com/) for local development.

## Getting Started

To set up your local environment and begin developing for this project, refer to the [BLT onboarding documentation](https://docs.acquia.com/blt/developer/onboarding/).

## Resources

* [Current live, reference site](http://www.unicef.org/)
* [Sharepoint site](https://unicef.sharepoint.com/teams/ITSS/UNICEForg/SitePages/Home.aspx)
* [Visual Studio Online](https://unicef.visualstudio.com/unicef.org)
* [GitHub](https://github.com/unicef/global-web-redesign)
* [TravisCI](https://travis-ci.com/unicef/global-web-redesign)

## Environments

* [Local site](http://local.unicefplatform.com/) ([Drupal VM dashboard](http://dashboard.local.unicefplatform.com/))

#### Shield (Basic auth)

Cloud environments are protected by a [PHP authentication shield](https://www.drupal.org/project/shield).

## Automated Tests

Automated tests and related configuration are contained in the [`tests`](https://github.com/unicef/global-web-redesign/blob/develop/tests) directory, Behat feature specifications in [`tests/behat/features`](https://github.com/unicef/global-web-redesign/blob/develop/tests/behat/features).

## FAQ

### Should we use commands in the VM or on the host machine?

BLT commands should be run from the host machine. BLT uses Drush aliases to automatically target commands to the guest VM when necessary.

Frontend commands such as `gulp watch` can be run either on the host machine (if you have the requisite build tools installed) or on the guest VM (which can be accessed via `vagrant ssh`).

### How do I debug Twig templates?

##### Short Answer

Copy `sites/example.development.services.yml` to `sites/development.services.yml`.

Use commands like `{{ kint(node) }}` to print information about a node.

##### Discover Twig Variables

You can reference the Classy theme such as `/docroot/core/themes/classy/templates/content/node.html.twig` which contains many comments about  variables you may want to use.

You can also use `{{ kint() }}` or `dump()` to print all available variables although this requires a significant amount of memory.

### How is configuration managed?

Configuration is stored in yml files within `/config` folder using Drupal's native config import/export functionality.

## Development Workflow

Code should be contributed following a gitflow workflow. The canonical project Github repo should be forked and all development should be performed against the develop branch in individual feature branches for each VSTS issue. A more detailed overview of this process including resolving common issues is available in the [BLT documentation](http://blt.readthedocs.io/en/8.x/readme/dev-workflow/#development-workflow).

Please follow this [development](https://unicef.sharepoint.com/:p:/r/teams/ITSS/UNICEForg/_layouts/15/Doc.aspx?sourcedoc=%7BD800EACB-C94F-4646-8CD2-115D5543B747%7D&action=edit&source=https%3A%2F%2Funicef%2Esharepoint%2Ecom%2Fteams%2FITSS%2FUNICEForg%2Funicef%2Eorg%2520Drupal%2520CMS%2FForms%2FLevel%25201%2520View%2Easpx) and [deployment](https://unicef.sharepoint.com/:w:/r/teams/ITSS/UNICEForg/_layouts/15/Doc.aspx?sourcedoc=%7BE3D4994D-2B36-4511-B612-7DC407DE70F3%7D&action=edit&source=https%3A%2F%2Funicef%2Esharepoint%2Ecom%2Fteams%2FITSS%2FUNICEForg%2Funicef%2Eorg%2520Drupal%2520CMS%2FForms%2FLevel%25201%2520View%2Easpx) documentation to use our project standards.

## Code Review

All code will be reviewed via pull request and feedback will be provided in Github.

Assuming that the TravisCI build has passed including automated linting and validation tasks, code will then be reviewed once it is in the "Ready for review" state in VSTS and assigned to a technical leader.
