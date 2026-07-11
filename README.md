# 🧙 Jean-Piarre Foucault – *Will It Make You a Millionaire?*
*...probably not, but you’ll have a blast trying.*

## About the Project 🎩
Welcome to **Jean-Piarre Foucault** – the ultimate tool for predicting French lottery draws. Inspired by the legendary Jean-Pierre Foucault (yes, *that* one from *Who Wants to Be a Millionaire*) and powered by artificial intelligence, this project is here to make you question randomness itself.

This project combines **Docker**, **Python** (for the hidden logic and deep learning magic), and **Drupal 11** (for the web and database side, because, let’s face it, I’m pretty good at it).

## The Mystery of Python Magic 🧪✨
Do you *really* believe in randomness? Think again. This project’s secret sauce is a hefty dose of Python that digs into patterns the average eye can’t see. And here’s something to ponder:

- **49 identical balls** (of which we know the weight and size) spinning in the same jar, at the same speed, in the same room, handled by the same hands in the same order – does that really sound random?
- Some balls seem to **pop up more often** than others. A coincidence? Or a pattern waiting to be cracked?
- And what about those balls that love to come out **together** like old friends?

**Jean-Piarre Foucault** was built with these “coincidences” in mind. With our deep learning algorithms working in the background, it’s less about luck and more about *predictive insight*. Python doesn't believe in chance. Neither should you.

## Installation Guide – Only Three Easy Steps (out of Four) 🚀
1. **Install [DDEV](https://ddev.readthedocs.io/en/stable/users/install/)** – if you can handle *that*, the rest is cake.
2. **Clone the Repo** – like a pro.
3. Run `ddev init` – and watch as the magic unfolds!
4. Realize there’s a hidden **Step 4**: *Implementing your own prediction algorithm* (sorry, mine’s not included – I’ll make it public once I’m a millionaire 😆).

Voilà! You now have Jean-Piarre Foucault running locally… minus that small “predict the future” part. 😄

## Quality Code? You Bet. 🔍
Run `ddev quality` to unleash the *“very hard”* quality check tools. If your code survives this, it's ready for anything.

You can also pass a comma-separated list of tasks as an argument — for example, `ddev quality phpcs,phpmd` will run only the phpcs and phpmd tasks.

## Features 🖇️
- **Real FDJ Data** (shhh...) 📊: Yep, we’re grabbing real lotto draw data via an FDJ API call. Let’s keep this one between us.
- **Enhanced FDJ Stats Views** 📈: Enjoy views that are almost like FDJ’s… but better.
- **Random Number Predictions** 🎲: Because when it comes to the lottery, randomness is really all you need.

## Useful commands 📎
| Description             | Command                                       |
|-------------------------|-----------------------------------------------|
| Start project           | `ddev start`                                  |
| Open project in browser | `ddev launch`                                 |
| Stop project            | `ddev stop`                                   |
| Update project          | `ddev update`                                 |
| Init project            | `ddev init`                                   |
| Check quality           | `ddev quality`                                |
| Force update data       | `ddev drush crun import_dynamic_data --force` |
| Mock predictions        | `ddev drush mockpred both`                    |

## Disclaimer
This is a **fun project** – no financial advice here. And as for winning, well, *good luck*. 🎰

> [!IMPORTANT]
>**Jean-Piarre Foucault** is a work in progress, but what’s here is fully functional and already delivers *almost-beautiful* predictions. The journey to perfecting lottery insights is just beginning, and this project will keep evolving – expect continuous updates and improvements throughout 2026-2027. Stay tuned, because the best is yet to come! 🎉
