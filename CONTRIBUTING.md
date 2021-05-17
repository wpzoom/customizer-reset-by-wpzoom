# Contributing

1. Fork this repository to your own GitHub account.

2. Clone the repository to your `wp-content/plugins/` directory.

`git clone git@github.com:<your_github_profile>/customizer-reset.git`

3. Navigate into the directory.

`cd customizer-reset`

4. Create a feature branch to record your changes.

`git checkout -b <branch_name>`

5. Install Composer dependencies.

`composer install`

6. Check your pull request with the following commands.

`composer run phpcbf`

`composer run phpcs`

`composer run phpstan`
