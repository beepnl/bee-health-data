# Bee Health Data Portal

EU-wide bee health data portal. This portal is used to share data between the different parties within the B-Good consortium.
It is based on the PHP Laravel framework in combination with a Postgres database and AWS S3 storage.

## UX

A UX design is available. The design has been made in Adobe XD which you can [download here](https://www.adobe.com/products/xd.html). For access to the ux design please request access to Marten.

## Class diagram

A class diagram has been distilled from the requirements and UX design. This class diagram is a tool for discussion and should not be seen as an authoritative document.
It is important that the document is updated as our understanding of the domain evolves.

The class diagram has been made with [Star UML](http://staruml.io). You can find the class diagram [here](/docs/class-diagram.mdj)

## Development flow

Please use git flow as a branching model. Create feature branches and name them in the following format: `feature/<github_issue_number>-<some_descriptive_keywords>`.

Once a feature is completed, please open a pull request that merges your feature branch into the `development` branch and flag @georgevanvliet for a code review.

After each sprint we can make a release branch, test it, and then merge into master. Master will then be backmerged into development.

## Run project on docker

```
cp .env.example .env
```

```
docker-compose build app
```

```
docker-compose up -d
```
