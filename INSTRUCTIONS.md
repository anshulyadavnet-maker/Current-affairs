# StudyHubPoint Current Affairs MCQ Generator — Instructions

> **Version:** 1.1 (updated 06 Aug 2026; v2.0 production fields and content-linked highlights)
> **Language:** Hindi-first, with English terms in brackets only where necessary
> **Output:** JSON for Monday–Saturday; Markdown static topic on Sunday

## 1. Purpose and core principle

Create fact-checked, exam-oriented daily current-affairs MCQs for SSC, UPSC, State PCS, Banking, Railway, CTET, DSSSB, KVS, NVS, UP Police and all government examinations.

**Accuracy > speed. Never guess.** Do not invent dates, venues, appointments, reports, figures or quotations. If a fact cannot be verified, write `⚠ Verification Pending – Please verify before publishing.` and do not publish that MCQ.

## 2. Source priority

Prefer these sources in order:

1. **Tier-1 official:** PIB, President of India, PMO, ministries, RBI, SEBI, ISRO, DRDO, Election Commission, Supreme Court, NITI Aayog, ICC, FIFA, IOC, UN, WHO, World Bank and IMF.
2. **Tier-2 reliable:** Reuters, AP News, The Hindu and The Indian Express.
3. **Tier-3 discovery only:** Hindustan Times, Times of India, Business Standard and Economic Times.

Coaching websites, AI summaries, social media and current-affairs portals must never be used as verification sources. Always provide a direct source or named release/notification.

## 3. Verification and repetition

Verify the person, designation, date, venue, country/state, ministry, organisation, number, theme, award, report and whether the event is genuinely new. Never confuse a previous edition with the latest edition.

Do not repeat a story within seven days unless there is a major follow-up. Normally create only one MCQ from one news story. If a topic was used yesterday, replace it with a fresh verified topic.

## 4. Exam value and question style

Prefer appointments, cabinet decisions, bills/acts, schemes, reports/indices, science and technology, ISRO/defence, international relations, sports records, GI tags, awards, books, obituaries, economy, environment, UN/WHO/IMF/World Bank, RBI/SEBI, the Election Commission and the Supreme Court.

Avoid full-form-only questions, static definitions, background articles, old schemes without a fresh update, and old information presented as current affairs.

Questions must be concise Hindi-first one-liners asking for exactly one fact. Prefer forms such as “Which state…?”, “Which ministry…?”, “Who was appointed…?”, “Which country…?”, “What is the objective…?”, “How many…?” rather than generic “What is…?”. Keep full forms in the explanation, not the question. Options should be pure Hindi unless bilingual wording is specifically requested.

Target difficulty: **70% moderate, 20% easy, 10% tough**.

## 5. Required JSON object

```json
{
  "question_number": 1,
  "date": "26 August 2026",
  "category": "राष्ट्रीय घटनाक्रम",
  "question_text": "प्रश्न का संक्षिप्त हिन्दी पाठ?",
  "options": ["विकल्प एक", "विकल्प दो", "विकल्प तीन", "विकल्प चार"],
  "correct_option_id": 2,
  "explanation": ["सरल हिन्दी में दो छोटे वाक्य।"],
  "exam_focus_facts": ["महत्वपूर्ण पुनरावृत्ति तथ्य 1", "महत्वपूर्ण पुनरावृत्ति तथ्य 2", "महत्वपूर्ण पुनरावृत्ति तथ्य 3"],
  "related_facts": ["सीधा संबंधित स्थिर तथ्य 1", "सीधा संबंधित स्थिर तथ्य 2", "सीधा संबंधित स्थिर तथ्य 3"],
  "source": "PIB Press Release",
  "source_type": "Official",
  "confidence": "HIGH",
  "importance": 5,
  "news_age": "0 Day",
  "highlights": ["प्रश्न के पाठ से लिया गया सटीक वाक्यांश"],
  "font_size_scale": 1.15,
  "explanation_image_url": "images/1.png",
  "explanation_image_label": ""
}
```

### Field limits

| Field | Requirement |
|---|---|
| `correct_option_id` | Zero-based integer: `0`, `1`, `2` or `3` |
| `explanation` | Array with at most 2 items; 2–3 short, simple sentences total |
| `exam_focus_facts` | 3–4 important one-liners; maximum 4 |
| `related_facts` | 3–4 directly related static-GK one-liners; maximum 4 |
| `source` | Short named source, never only “Official Source” |
| `source_type` | `Official`, `Official + Tier-2`, `Tier-2` or `Tier-3` |
| `confidence` | `HIGH`, `MEDIUM` or `LOW`; do not generate an unverified LOW-confidence MCQ |
| `importance` | Integer from 1 to 5 |
| `news_age` | `0 Day`, `1 Day`, etc. |
| `font_size_scale` | `1.15` normally; `1.08` for text-dense questions |
| `explanation_image_url` | `images/N.png`, where N is the question number |
| `explanation_image_label` | Always `""` |

## 6. Strict highlights rule

`highlights` must contain **40–65 meaningful exact phrases** copied from the same question's `question_text`, `options`, `explanation`, `exam_focus_facts` or `related_facts`. Include proper nouns, names, numbers, dates, places, scheme/organisation names and key terms where they literally occur.

Do not add generic SEO tags such as `SSC current affairs`, `StudyHubPoint` or `daily current affairs hindi` unless that exact text appears in the question content. Do not paraphrase. English matching is case-sensitive and Hindi spelling/matras must be identical.

Before saving, programmatically assert that every highlight is a substring of the combined question content. Fix every missing tag before delivery.

## 7. Quality checklist

Before delivery, verify:

- no topic or fact is repeated from the previous seven days;
- the date, person, designation, venue, country, edition, number and source are correct;
- the question is Hindi-first, concise and asks for one fact;
- the JSON is valid;
- explanation has at most 2 items;
- exam-focus and related facts have at most 4 items each;
- all 40–65 highlights are exact content-linked substrings;
- all production fields are present and correctly formatted.

## 8. File naming and workflow

Monday–Saturday daily files must use:

```text
DD_Month_YYYY_current_affairs_hindi.json
```

Example: `09_july_2026_current_affairs_hindi.json`.

On Sunday, create a static topic Markdown file instead of the daily JSON, using a descriptive date-and-topic filename, for example `06_september_2026_indian_constitution.md`.

Workflow: collect official/reliable headlines, filter for exam value, cross-verify, remove recent repeats, draft questions, add explanations and production fields, generate and validate highlights, validate JSON, then save and deliver.

## 9. Sunday static-topic template

```markdown
# Static Topic: Topic name

- **Date:** DD Month YYYY
- **Type:** static-topic

## Overview

Explain the topic clearly.

## Key points

- Point one
- Point two

## Quick revision

Add important facts, dates, definitions, or questions for revision.
```
