# StudyHubPoint Current Affairs

Hindi-first, exam-oriented daily current-affairs MCQs for SSC, UPSC, State PCS, Banking, Railway, CTET, DSSSB, KVS, NVS, UP Police and other government examinations.

## Publishing schedule

- **Monday–Saturday:** create one daily current-affairs JSON file.
- **Sunday:** create one static-topic Markdown file instead of the daily JSON file.

### File naming

Daily JSON files use:

```text
DD_Month_YYYY_current_affairs_hindi.json
```

Example: `09_july_2026_current_affairs_hindi.json`

Sunday static topics use a descriptive date-and-topic slug, for example:

```text
data/static/06_september_2026_indian_constitution.md
```

## Content rules

Accuracy is more important than speed. Every fact must be verified before publication; never guess or invent dates, venues, appointments, figures, reports or quotations. Prefer Tier-1 official sources such as PIB, ministries, RBI, SEBI, ISRO, DRDO, the Election Commission, the Supreme Court, NITI Aayog, ICC, FIFA, IOC, UN, WHO, World Bank and IMF. Reuters, AP, The Hindu and The Indian Express are Tier-2 sources. Tier-3 sources are for discovery only.

Each question must be concise, Hindi-first, and ask for one specific fact. Use a 70% moderate, 20% easy and 10% tough difficulty mix. Avoid repetition of the same story within seven days, unless there is a major follow-up.

## Daily JSON requirements

Every MCQ must include the production fields defined in [`INSTRUCTIONS.md`](INSTRUCTIONS.md), including:

- `question_number`, `date`, `category`, `question_text`, `options`, `correct_option_id`
- `explanation` (maximum 2 items)
- `exam_focus_facts` and `related_facts` (maximum 4 items each)
- `source`, `source_type`, `confidence`, `importance`, and `news_age`
- `highlights` (40–65 exact phrases copied from that question's content)
- `font_size_scale`, `explanation_image_url`, and `explanation_image_label`

Every highlight must be a literal, case-sensitive substring of the question, options, explanation, exam-focus facts, or related facts. Do not use generic SEO tags. JSON must be validated before delivery.

## Sunday Markdown requirements

Sunday files should contain a static topic with an overview, key points, and a quick-revision section. Static content must be clearly separated from daily current affairs.

## Workflow

1. Collect headlines from official and reliable sources.
2. Select high-value exam topics and remove topics used in the previous seven days.
3. Cross-verify every fact.
4. Draft concise Hindi-first questions and pure-Hindi options unless bilingual wording is necessary.
5. Add explanations, revision facts, source metadata and production fields.
6. Generate content-linked highlights and verify every highlight programmatically.
7. Validate the JSON and save it using the required filename.

See [`INSTRUCTIONS.md`](INSTRUCTIONS.md) for the complete v1.1 / v2.0 production specification.
