# Principles of craftmanship for building useful software

## KISS
We keep the technical stack simple. For example, we use MySQL. If we can, we keep using MySQL for storing things, even if using other types of databases might have marginally better performance or features. Each new type of infrastructure requires maintenance and complexity down the line.

## Avoid lock-in
From time to time, we use external services. For example, we use some AWS cloud services. But we need to be indepentent enough of them to be able to shift to another environment in 72 hours. We run the infrastructure, they don't run us.

## We are the framework
We may use frameworks for our development, but we structure the code according to our paradigms. We want to spend time thinking about the most suitable architecture for what our software needs to do, not learning the architecture of frameworks.

## We ship in small increments
Shipping to production can be scary, because we might introduce bugs the affect other people. If this happens, it's easier to deal with when the set of changes we've done is small and fresh in our minds, than to store up a huge changeset over weeks not knowing what effects they may have when we finally ship.

## We test at the right places
We use automatic tests in front of the gates of our interfaces and do just enough to see if what comes out of the black box passes or fails doing what they need to do. We avoid littering our code with testing implementation details which couples our code to the tests and makes it harder to improve, not easier.

## We refactor
We use 70% of our time adding new things and 30% of our time taking out things, or making the old simpler.

## Legacy is good
When someone wrote software that still works many years later, well done. Sometimes keeping that doing it's job contained in it's own box is the most practical thing to do while writing new software around it.

## Technical debt is one way to invest
Taking out a loan to pay out in the future can be an appropriate way to move forward in a world of scarcity, even if there are interests. We are creating a tool, not a work of Art.

## We don't act from FOMO
Swtiching to the latest languages, tools and frameworks because we want to feel up to date or belong to a tribe is beneath us. We strive to learn new things all the time about how to build better software that is useful to people. Some of the time this means learning new languages and frameworks, but most of the time it has nothing to do with languages and frameworks.
