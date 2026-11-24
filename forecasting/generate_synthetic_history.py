"""Generate a reproducible private CSV package for the Slice 11 importer."""

import argparse
import csv
import hashlib
import io
import json
import random
from collections import Counter
from datetime import date, timedelta
from pathlib import Path

START = date(2026, 7, 1)
END = date(2026, 10, 31)
VERSION = "1"
PRICES = {
    "Potong rambut": 18_000,
    "Keramas": 5_000,
    "Semir hitam": 25_000,
    "Bleaching": 65_000,
    "Highlight": 65_000,
    "Hair Tonic": 15_000,
}
CUSTOMERS = (
    "Adi", "Ardi", "Bima", "Dani", "Dimas", "Fajar", "Galih", "Ilham",
    "Iqbal", "Joko", "Nanda", "Raka", "Rizki", "Wahyu", "Yusuf",
)


def csv_bytes(header, rows):
    output = io.StringIO(newline="")
    writer = csv.writer(output, lineterminator="\n")
    writer.writerow(header)
    writer.writerows(rows)
    return output.getvalue().encode("utf-8")


def transaction_items(rng):
    if rng.random() < 0.10:
        return [("product", "Hair Tonic", 1)]
    items = [("service", "Potong rambut", 1)]
    if rng.random() < 0.13:
        items.append(("service", "Keramas", 1))
    if rng.random() < 0.025:
        items.append(("service", rng.choice(("Semir hitam", "Bleaching", "Highlight")), 1))
    if rng.random() < 0.04:
        items.append(("product", "Hair Tonic", 1))
    return items


def generate(seed):
    rng = random.Random(seed)
    transactions, items = [], []
    revenues, counts = Counter(), Counter()
    for offset in range((END - START).days + 1):
        day = START + timedelta(days=offset)
        month = day.replace(day=1).isoformat()
        heavier = 0.70 if day.weekday() >= 5 else 0.45
        count = 14 + (rng.random() < heavier)
        counts[month] += count
        for index in range(count):
            tx_id = f"SYN-{day:%Y%m%d}-{index + 1:02d}"
            minutes = 540 + index * 45 + rng.randrange(15)
            time = f"{minutes // 60:02d}:{minutes % 60:02d}:00"
            barber_name = ("Bayu", "Ari")[(index + offset) % 2]
            transactions.append([tx_id, f"{day.isoformat()} {time}", barber_name, rng.choice(CUSTOMERS)])
            for number, (kind, label, quantity) in enumerate(transaction_items(rng), start=1):
                price = PRICES[label]
                amount = quantity * price
                items.append((tx_id, number, kind, label, quantity, price, amount))
                revenues[month] += amount

    missing = len(transactions) * 60 // 100
    for index in rng.sample(range(len(transactions)), missing):
        transactions[index][3] = ""

    files = {
        "transactions.csv": csv_bytes(
            ("source_transaction_id", "recorded_at_wib", "barber_name", "customer_name"),
            transactions,
        ),
        "transaction_items.csv": csv_bytes(
            ("source_transaction_id", "item_number", "item_type", "item_name", "quantity", "unit_price", "amount"),
            items,
        ),
        "monthly_revenues.csv": csv_bytes(
            ("month_start", "revenue", "transaction_count", "complete"),
            ((month, revenues[month], counts[month], 1) for month in sorted(counts)),
        ),
    }
    manifest = {
        "schema_version": 1,
        "dataset_version": f"synthetic-2026-07-to-10-seed-{seed}-v{VERSION}",
        "generator_version": VERSION,
        "data_kind": "synthetic",
        "timezone": "Asia/Jakarta",
        "currency": "IDR",
        "coverage": {"start": START.isoformat(), "end": END.isoformat()},
        "seed": seed,
        "assumptions": {
            "open_days": "every_day",
            "opening_hours_wib": "09:00-21:00",
            "daily_transactions": [14, 15],
            "customer_name_missing_target_percent": 60,
            "customer_names_missing": missing,
            "barbers": ["Bayu", "Ari"],
            "price_reference": "Manual recap photos, prices used as synthetic snapshots",
            "prices_idr": PRICES,
            "independent_probabilities": {
                "day_has_15_weekday": 0.45,
                "day_has_15_weekend": 0.70,
                "product_only": 0.10,
                "with_haircut_add_wash": 0.13,
                "with_haircut_add_color": 0.025,
                "with_haircut_add_product": 0.04,
            },
        },
        "files": {name: hashlib.sha256(content).hexdigest() for name, content in files.items()},
    }
    files["manifest.json"] = (json.dumps(manifest, ensure_ascii=False, indent=2) + "\n").encode("utf-8")
    return files, len(transactions), len(items), missing


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("output", type=Path, help="Local package directory under storage/app/private/imports")
    parser.add_argument("--seed", type=int, default=20261124)
    args = parser.parse_args()
    if not 0 <= args.seed <= 2**32 - 1:
        parser.error("--seed must be between 0 and 4294967295")
    files, transactions, items, missing = generate(args.seed)
    for name, content in files.items():
        existing = args.output / name
        if existing.exists() and existing.read_bytes() != content:
            parser.error(f"{existing} contains different data. Choose a new directory.")
    args.output.mkdir(parents=True, exist_ok=True)
    for name, content in files.items():
        path = args.output / name
        if not path.exists():
            path.write_bytes(content)
    print(f"{transactions} transactions, {items} items, {missing} blank customer names")


if __name__ == "__main__":
    main()
