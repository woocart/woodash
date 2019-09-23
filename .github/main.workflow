workflow "on pull request merge, delete the branch" {
  on = "pull_request"
  resolves = ["branch cleanup"]
}

action "branch cleanup" {
  uses = "jessfraz/branch-cleanup-action@master"
  secrets = ["GITHUB_TOKEN"]
}

action "Build package" {
  uses = "./.github/php"
  args = "make build"
}

action "Upload to release" {
  uses = "JasonEtco/upload-to-release@master"
  args = "build/woodash.zip application/zip"
  secrets = ["GITHUB_TOKEN"]
  needs = ["Build package"]
}

workflow "WooDash" {
  on = "push"
  resolves = [
    "Lint",
    "Test",
    "Cover",
  ]
}

action "On master or PR" {
  uses = "actions/bin/filter@master"
  args = "branch master|ref refs/pulls/*|ref refs/heads/*"
}

action "Install Dependencies" {
  uses = "./.github/php"
  args = "make ensure"
  needs = ["On master or PR"]
}

action "Lint" {
  uses = "./.github/php"
  args = "make fmt"
  needs = ["Install Dependencies"]
}

action "Test" {
  uses = "./.github/php"
  args = "make test"
  needs = ["Install Dependencies"]
}

action "Cover" {
  uses = "./.github/php"
  args = "make cover"
  needs = ["Test"]
}
